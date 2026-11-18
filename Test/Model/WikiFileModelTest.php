<?php

require_once 'tests/units/Base.php';
require_once __DIR__ . '/../AssertMultipleTrait.php';
use KanboardTests\units\Base;
use Kanboard\Core\Plugin\Loader;
use Kanboard\Plugin\Wiki\Model\WikiModel;
use Kanboard\Plugin\Wiki\Model\WikiFileModel;
use Kanboard\Model\ProjectModel;

class WikiFileModelTest extends Base
{
    use AssertMultipleTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $plugin = new Loader($this->container);
        $plugin->scan();

        $_SESSION['user'] = array('id' => 1, 'username' => 'test', 'role' => 'app-admin');
    }

    public function testGetProjectId()
    {
        $projectModel = new ProjectModel($this->container);
        $wikiModel = new WikiModel($this->container);
        $wikiFileModel = new WikiFileModel($this->container);

        $this->assertMultiple(
            fn() => $this->assertEquals(1, $projectModel->create(array('name' => 'Project A'))),
            fn() => $this->assertEquals(2, $projectModel->create(array('name' => 'Project B'))),
            fn() => $this->assertEquals(1, $wikiModel->createpage(1, 'Page A', 'content')),
            fn() => $this->assertEquals(2, $wikiModel->createpage(2, 'Page B', 'content')),
        );

        $file_id = $this->container['db']->table(WikiFileModel::TABLE)->persist(array(
            'wikipage_id' => 2,
            'name' => 'test.txt',
            'path' => 'wikipages/2/test',
            'is_image' => 0,
            'date' => time(),
            'user_id' => 1,
            'size' => 4,
        ));

        $this->assertMultiple(
            fn() => $this->assertEquals(2, $wikiFileModel->getProjectId($file_id)),
            fn() => $this->assertEquals(0, $wikiFileModel->getProjectId(999)),
        );
    }

    public function testGetPublicFile()
    {
        $projectModel = new ProjectModel($this->container);
        $wikiModel = new WikiModel($this->container);
        $wikiFileModel = new WikiFileModel($this->container);
        $config = $this->container['configModel'];

        // project 1 public (pages 1 and 3, page 3 archived), project 2 public (page 2), project 3 private (page 4)
        $projectModel->create(array('name' => 'Public A'));
        $projectModel->create(array('name' => 'Public B'));
        $projectModel->create(array('name' => 'Private'));
        $projectModel->enablePublicAccess(1);
        $projectModel->enablePublicAccess(2);
        $tokenA = $projectModel->getById(1)['token'];
        $tokenB = $projectModel->getById(2)['token'];
        $wikiModel->createpage(1, 'A', 'content');
        $wikiModel->createpage(2, 'B', 'content');
        $wikiModel->createpage(1, 'Archived', 'content');
        $wikiModel->createpage(3, 'Private', 'content');
        $this->container['db']->table(WikiModel::WIKITABLE)->eq('id', 3)->update(array('is_active' => 0));

        $files = array();
        foreach (array(1, 2, 3, 4) as $wikipage_id) {
            $files[$wikipage_id] = $this->container['db']->table(WikiFileModel::TABLE)->persist(array(
                'wikipage_id' => $wikipage_id, 'name' => 'f.txt', 'path' => 'wikipages/' . $wikipage_id . '/f',
                'is_image' => 0, 'date' => time(), 'user_id' => 1, 'size' => 1,
            ));
        }

        // attachments are off by default
        $this->assertNull($wikiFileModel->getPublicFile($files[1], $tokenA));

        $config->save(array('wiki_public_attachments' => 1));
        $this->container['memoryCache']->flush();

        $this->assertMultiple(
            fn() => $this->assertEquals($files[1], $wikiFileModel->getPublicFile($files[1], $tokenA)['id']),
            fn() => $this->assertNull($wikiFileModel->getPublicFile($files[1], ''), 'no token'),
            fn() => $this->assertNull($wikiFileModel->getPublicFile($files[1], 'not-a-token'), 'unknown token'),
            fn() => $this->assertNull($wikiFileModel->getPublicFile($files[2], $tokenA), 'file of another public project'),
            fn() => $this->assertNull($wikiFileModel->getPublicFile($files[3], $tokenA), 'archived page'),
            fn() => $this->assertNull($wikiFileModel->getPublicFile($files[4], $tokenA), 'file of a private project'),
            fn() => $this->assertNull($wikiFileModel->getPublicFile(999, $tokenA), 'missing file'),
            fn() => $this->assertEquals($files[2], $wikiFileModel->getPublicFile($files[2], $tokenB)['id']),
        );

        // public access turned off for the project, or public wiki turned off globally
        $projectModel->disablePublicAccess(2);
        $this->assertNull($wikiFileModel->getPublicFile($files[2], $tokenB), 'project no longer public');

        $config->save(array('wiki_public_access' => 0));
        $this->container['memoryCache']->flush();
        $this->assertNull($wikiFileModel->getPublicFile($files[1], $tokenA), 'public wiki off');
    }
}
