<?php

require_once 'tests/units/Base.php';
require_once __DIR__ . '/../AssertMultipleTrait.php';

use KanboardTests\units\Base;
use Kanboard\Core\Plugin\Loader;
use Kanboard\Model\ProjectModel;
use Kanboard\Plugin\Wiki\Model\WikiEditionStore;
use Kanboard\Plugin\Wiki\Model\WikiModel;
use Kanboard\Plugin\Wiki\Model\WikiPageModel;

class WikiEditionStoreTest extends Base
{
    use AssertMultipleTrait;

    private $pages;
    private $store;

    protected function setUp(): void
    {
        parent::setUp();

        $plugin = new Loader($this->container);
        $plugin->scan();

        $_SESSION['user'] = array('id' => 1, 'username' => 'admin', 'role' => 'app-admin');
        $this->container['configModel']->save(array('persistEditions' => '1'));

        $projectModel = new ProjectModel($this->container);
        $this->assertEquals(1, $projectModel->create(array('name' => 'Editions')));
        $this->pages = new WikiPageModel($this->container);
        $this->store = new WikiEditionStore($this->container);
    }

    private function page(string $content): array
    {
        return $this->pages->createPage(1, array('title' => 'Page', 'content' => $content), 1);
    }

    private function update(array $page, string $content): array
    {
        return $this->pages->updatePage($page['id'], $page['revision'], array('content' => $content), 1);
    }

    private function raw(int $pageId): array
    {
        $rows = $this->container['db']->table(WikiEditionStore::TABLE)->eq('wikipage_id', $pageId)->asc('edition')->findAll();

        return array_column($rows, 'content', 'edition');
    }

    private function text(int $i): string
    {
        return "# Notes\n\nThis page keeps the team's notes about releases and reviews.\nline two changed {$i} times\n"
            ."It is long enough that storing a change is smaller than storing the page again.";
    }

    public function testEditionsAreStoredAsDeltasAndRebuilt()
    {
        $page = $this->page($this->text(0));
        for ($i = 1; $i <= 3; $i++) {
            $page = $this->update($page, $this->text($i));
        }
        $raw = $this->raw($page['id']);
        $all = $this->store->all($page['id']);

        $this->assertMultiple(
            fn() => $this->assertSame($this->text(0), $raw[1], 'the first edition is a full copy'),
            fn() => $this->assertStringStartsWith(WikiEditionStore::MARKER.'3:', $raw[4]),
            fn() => $this->assertSame($this->text(2), $this->store->get($page['id'], 3)['content']),
            fn() => $this->assertSame(array(4, 3, 2, 1), array_column($all, 'edition')),
            fn() => $this->assertSame(1, $all[0]['added']),
            fn() => $this->assertSame(1, $all[0]['removed']),
            fn() => $this->assertSame(3, $all[0]['base']),
            fn() => $this->assertNull($all[3]['base']),
            fn() => $this->assertTrue(ctype_digit((string) $all[0]['date_creation']), 'editions are stamped with a timestamp'),
        );
    }

    public function testKeyframeEveryInterval()
    {
        $page = $this->page($this->text(0));
        for ($i = 1; $i <= WikiEditionStore::KEYFRAME_INTERVAL + 2; $i++) {
            $page = $this->update($page, $this->text($i));
        }
        $raw = $this->raw($page['id']);
        $keyframe = WikiEditionStore::KEYFRAME_INTERVAL + 1;

        $this->assertMultiple(
            fn() => $this->assertSame($this->text($keyframe - 1), $raw[$keyframe]),
            fn() => $this->assertStringStartsWith(WikiEditionStore::MARKER, $raw[$keyframe - 1]),
            fn() => $this->assertStringStartsWith(WikiEditionStore::MARKER.$keyframe.':', $raw[$keyframe + 1]),
            fn() => $this->assertSame($this->text(WikiEditionStore::KEYFRAME_INTERVAL + 2), $this->store->latest($page['id'])['content']),
        );
    }

    public function testPurgeKeepsTheChain()
    {
        $page = $this->page($this->text(0));
        for ($i = 1; $i <= 4; $i++) {
            $page = $this->update($page, $this->text($i));
        }
        $purged = array(
            $this->store->purge($page['id'], 3),
            $this->store->purge($page['id'], 1),
            $this->store->purge($page['id'], 3),
        );

        $all = $this->store->all($page['id']);
        $this->assertMultiple(
            fn() => $this->assertSame(array(true, true, false), $purged, 'purging an edition twice fails the second time'),
            fn() => $this->assertSame(array(5, 4, 2), array_column($all, 'edition')),
            fn() => $this->assertSame(array($this->text(4), $this->text(3), $this->text(1)), array_column($all, 'content')),
            fn() => $this->assertSame(2, $all[1]['base'], 'edition 4 now compares with edition 2'),
        );
    }

    public function testEditsWhileSavingWasOffBecomeTheirOwnEdition()
    {
        $page = $this->page('one');
        $this->container['configModel']->save(array('persistEditions' => '0'));
        $this->container['memoryCache']->flush();
        $page = $this->update($page, 'two');
        $this->container['configModel']->save(array('persistEditions' => '1'));
        $this->container['memoryCache']->flush();
        $page = $this->update($page, 'three');

        $all = $this->store->all($page['id']);
        $this->assertMultiple(
            fn() => $this->assertSame(array(3, 2, 1), array_column($all, 'edition')),
            fn() => $this->assertSame(array('three', 'two', 'one'), array_column($all, 'content')),
            fn() => $this->assertTrue($all[1]['unrecorded']),
            fn() => $this->assertFalse($all[0]['unrecorded']),
        );
    }

    public function testLegacyFullRowsAndMarkerLikeContent()
    {
        $page = $this->page('legacy one');
        $this->container['db']->table(WikiEditionStore::TABLE)->eq('wikipage_id', $page['id'])->update(array('date_creation' => '2020-01-01'));
        $this->container['db']->table(WikiEditionStore::TABLE)->insert(array(
            'edition' => 2, 'title' => 'Page', 'content' => 'legacy two', 'creator_id' => 1, 'date_creation' => '2020-01-02', 'wikipage_id' => $page['id'],
        ));
        $this->container['db']->table(WikiPageModel::PAGE_TABLE)->eq('id', $page['id'])->update(array('editions' => 2, 'current_edition' => 2, 'content' => 'legacy two'));
        $page = $this->pages->getPageDetail($page['id']);
        $tricky = WikiEditionStore::MARKER."0:\n[]";
        $page = $this->update($page, $tricky);

        $all = $this->store->all($page['id']);
        $this->assertMultiple(
            fn() => $this->assertSame(array($tricky, 'legacy two', 'legacy one'), array_column($all, 'content')),
            fn() => $this->assertSame(1, $all[1]['added'], 'stats are computed for full rows too'),
        );
    }

    public function testCompactAndExpandKeepEveryEdition()
    {
        $page = $this->page($this->text(0));
        for ($i = 1; $i <= 24; $i++) {
            $page = $this->update($page, $this->text($i));
        }
        $expected = array_column($this->store->all($page['id']), 'content', 'edition');

        $expanded = $this->store->rewriteAll(false);
        $full = $this->raw($page['id']);
        $compacted = $this->store->rewriteAll(true);
        $raw = $this->raw($page['id']);
        $again = $this->store->rewriteAll(true);

        $this->assertMultiple(
            fn() => $this->assertSame(array('pages' => 1, 'rows' => 23, 'failed' => array()), $expanded, 'edition 1 and the keyframe were already full'),
            fn() => $this->assertSame($expected, array_reverse($full, true)),
            fn() => $this->assertSame(23, $compacted['rows']),
            fn() => $this->assertSame(0, $again['rows'], 'compacting twice changes nothing'),
            fn() => $this->assertSame($this->text(0), $raw[1]),
            fn() => $this->assertSame($this->text(20), $raw[21], 'keyframe every interval'),
            fn() => $this->assertStringStartsWith(WikiEditionStore::MARKER.'24:', $raw[25]),
            fn() => $this->assertSame($expected, array_column($this->store->all($page['id']), 'content', 'edition')),
        );
    }

    public function testWebRestoreRecordsANewEdition()
    {
        $page = $this->page('First');
        $this->update($page, 'Second');
        $wikiModel = new WikiModel($this->container);

        $this->assertTrue($wikiModel->restoreEdition($page['id'], 1));
        $restored = $wikiModel->getWikipage($page['id']);
        $this->assertMultiple(
            fn() => $this->assertSame('First', $restored['content']),
            fn() => $this->assertEquals(3, $restored['editions']),
            fn() => $this->assertEquals(3, $restored['current_edition']),
            fn() => $this->assertSame(array(3, 2, 1), array_column($wikiModel->getEditions($page['id']), 'edition')),
        );
    }
}
