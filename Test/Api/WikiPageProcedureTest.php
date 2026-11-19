<?php

require_once 'tests/units/Base.php';
require_once __DIR__ . '/../AssertMultipleTrait.php';

use KanboardTests\units\Base;
use Kanboard\Auth\DatabaseAuth;
use Kanboard\Core\Plugin\Loader;
use Kanboard\Core\Security\AuthenticationManager;
use Kanboard\Model\ProjectModel;
use Kanboard\Plugin\Wiki\Api\Procedure\WikiPageProcedure;

class WikiPageProcedureTest extends Base
{
    use AssertMultipleTrait;

    private $procedure;
    private $projectModel;

    protected function setUp(): void
    {
        parent::setUp();

        $plugin = new Loader($this->container);
        $plugin->scan();

        $authManager = new AuthenticationManager($this->container);
        $authManager->register(new DatabaseAuth($this->container));

        $_SESSION['user'] = array('id' => 1, 'username' => 'admin', 'role' => 'app-admin');

        $this->procedure = new WikiPageProcedure($this->container);
        $this->projectModel = new ProjectModel($this->container);
    }

    public function testCreateReadUpdateAndConflictResponses()
    {
        $this->assertEquals(1, $this->projectModel->create(array('name' => 'API Project')));

        $created = $this->procedure->createWikiPage(1, array(
            'title' => 'Runbook',
            'content' => 'Initial',
        ));

        $this->assertMultiple(
            fn() => $this->assertTrue($created['ok']),
            fn() => $this->assertEquals('Runbook', $created['data']['title']),
            fn() => $this->assertEquals(1, $created['data']['revision']),
        );

        $read = $this->procedure->getWikiPage($created['data']['id']);
        $this->assertMultiple(
            fn() => $this->assertTrue($read['ok']),
            fn() => $this->assertEquals('Initial', $read['data']['content']),
        );

        $updated = $this->procedure->updateWikiPage($created['data']['id'], 1, array(
            'content' => 'Updated',
        ));
        $this->assertMultiple(
            fn() => $this->assertTrue($updated['ok']),
            fn() => $this->assertEquals(2, $updated['data']['revision']),
        );

        $conflict = $this->procedure->updateWikiPage($created['data']['id'], 1, array(
            'content' => 'Stale',
        ));
        $this->assertMultiple(
            fn() => $this->assertFalse($conflict['ok']),
            fn() => $this->assertEquals('revision_conflict', $conflict['error']['code']),
            fn() => $this->assertEquals(2, $conflict['error']['details']['currentRevision']),
        );
    }

    public function testListSearchAndRevisionResponses()
    {
        $this->assertEquals(1, $this->projectModel->create(array('name' => 'Search Project')));
        $created = $this->procedure->createWikiPage(1, array(
            'title' => 'Deployment Notes',
            'content' => 'Blue green deployment',
        ));
        $this->assertTrue($created['ok']);

        $listed = $this->procedure->getWikiPages(1);
        $search = $this->procedure->searchWikiPages(1, 'green');
        $revisions = $this->procedure->getWikiPageRevisions($created['data']['id']);
        $this->assertMultiple(
            fn() => $this->assertTrue($listed['ok']),
            fn() => $this->assertEquals(1, $listed['data']['pagination']['total']),
            fn() => $this->assertTrue($search['ok']),
            fn() => $this->assertEquals('Deployment Notes', $search['data']['pages'][0]['title']),
            fn() => $this->assertTrue($revisions['ok']),
            fn() => $this->assertEquals(1, $revisions['data']['pagination']['total']),
        );
    }

    public function testJsonRpcRegistrationExposesOnlyPublicWikiProcedures()
    {
        $this->assertEquals(1, $this->projectModel->create(array('name' => 'Dispatch Project')));

        $this->container['api'] = new \JsonRPC\Server('{}');
        $plugin = new \Kanboard\Plugin\Wiki\Plugin($this->container);
        $plugin->initialize();
        $handler = $this->container['api']->getProcedureHandler();
        $listed = $handler->executeProcedure('getWikiPages', array('project_id' => 1));

        $this->assertTrue($listed['ok']);

        $this->expectException('BadFunctionCallException');
        $handler->executeProcedure('changeArchiveState', array(
            'pageId' => 1,
            'expectedRevision' => 1,
            'archived' => true,
            'method' => 'getWikiPage',
        ));
    }
}
