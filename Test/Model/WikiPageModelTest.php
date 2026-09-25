<?php

require_once 'tests/units/Base.php';

use KanboardTests\units\Base;
use Kanboard\Core\Plugin\Loader;
use Kanboard\Model\ProjectModel;
use Kanboard\Plugin\Wiki\Domain\WikiPageException;
use Kanboard\Plugin\Wiki\Model\WikiPageModel;
use Kanboard\Core\Security\AuthenticationManager;
use Kanboard\Auth\DatabaseAuth;

class WikiPageModelTest extends Base
{
    private $wikiPageModel;
    private $projectModel;

    protected function setUp(): void
    {
        parent::setUp();

        $plugin = new Loader($this->container);
        $plugin->scan();

        $authManager = new AuthenticationManager($this->container);
        $authManager->register(new DatabaseAuth($this->container));

        $_SESSION['user'] = array('id' => 1, 'username' => 'admin', 'role' => 'app-admin');

        $this->wikiPageModel = new WikiPageModel($this->container);
        $this->projectModel = new ProjectModel($this->container);
    }

    public function testCreateUpdateAndRevisionConflict()
    {
        $this->assertEquals(1, $this->projectModel->create(array('name' => 'API Project')));

        $page = $this->wikiPageModel->createPage(1, array(
            'title' => 'Home',
            'content' => 'Initial content',
        ), 1);

        $this->assertEquals(1, $page['id']);
        $this->assertEquals(1, $page['projectId']);
        $this->assertEquals('Home', $page['title']);
        $this->assertEquals('Initial content', $page['content']);
        $this->assertEquals(1, $page['revision']);
        $this->assertEquals(1, $page['currentRevision']);
        $this->assertFalse($page['archived']);

        $updated = $this->wikiPageModel->updatePage($page['id'], $page['revision'], array(
            'content' => 'Changed content',
        ), 1);

        $this->assertEquals('Changed content', $updated['content']);
        $this->assertEquals(2, $updated['revision']);
        $this->assertEquals(2, $updated['currentRevision']);

        try {
            $this->wikiPageModel->updatePage($page['id'], $page['revision'], array(
                'title' => 'Stale write',
            ), 1);
            $this->fail('Expected stale revision to be rejected.');
        } catch (WikiPageException $exception) {
            $this->assertEquals('revision_conflict', $exception->errorCode());
            $this->assertEquals(2, $exception->errorDetails()['currentRevision']);
        }
    }

    public function testRejectsHierarchyCycleAndParentWithActiveChildrenArchive()
    {
        $this->assertEquals(1, $this->projectModel->create(array('name' => 'Tree Project')));

        $parent = $this->wikiPageModel->createPage(1, array('title' => 'Parent'), 1);
        $child = $this->wikiPageModel->createPage(1, array(
            'title' => 'Child',
            'parent_id' => $parent['id'],
        ), 1);

        try {
            $this->wikiPageModel->updatePage($parent['id'], $parent['revision'], array(
                'parent_id' => $child['id'],
            ), 1);
            $this->fail('Expected cycle move to be rejected.');
        } catch (WikiPageException $exception) {
            $this->assertEquals('hierarchy_cycle', $exception->errorCode());
        }

        try {
            $this->wikiPageModel->setArchived($parent['id'], $parent['revision'], true, 1);
            $this->fail('Expected archive with active children to be rejected.');
        } catch (WikiPageException $exception) {
            $this->assertEquals('validation_failed', $exception->errorCode());
        }
    }

    public function testRestoreRevisionCreatesNewCurrentRevision()
    {
        $this->assertEquals(1, $this->projectModel->create(array('name' => 'Revision Project')));

        $page = $this->wikiPageModel->createPage(1, array(
            'title' => 'Decision',
            'content' => 'First',
        ), 1);
        $second = $this->wikiPageModel->updatePage($page['id'], 1, array('content' => 'Second'), 1);
        $restored = $this->wikiPageModel->restoreRevision($page['id'], $second['revision'], 1, 1);

        $this->assertEquals('First', $restored['content']);
        $this->assertEquals(3, $restored['revision']);
        $this->assertEquals(3, $restored['currentRevision']);

        $revisions = $this->wikiPageModel->revisions($page['id']);
        $this->assertEquals(3, $revisions['pagination']['total']);
        $this->assertEquals(3, $revisions['revisions'][0]['revision']);
    }
}
