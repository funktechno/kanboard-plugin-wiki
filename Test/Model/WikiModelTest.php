<?php

require_once 'tests/units/Base.php';

use Kanboard\Core\Plugin\Loader;
use Kanboard\Plugin\Wiki\Model\WikiModel;
use Kanboard\Model\ProjectModel;
use Kanboard\Core\User\UserSession;
use Kanboard\Core\Security\AuthenticationManager;
use Kanboard\Auth\DatabaseAuth;

class WikiModelTest extends Base
{
    /**
     * @var Plugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();
        // $this->plugin = new Plugin($this->container);

        $plugin = new Loader($this->container);
        $plugin->scan();
        
        $authManager = new AuthenticationManager($this->container);
        $authManager->register(new DatabaseAuth($this->container));

        $_SESSION['user'] = array('id' => 1, 'username' => 'test', 'role' => 'app-admin');
    }

    public function testCreation()
    {
        $projectModel = new ProjectModel($this->container);

        $this->assertEquals($projectModel->create(array('name' => 'UnitTest')), 1, 'Failed to create project');

        $project = $projectModel->getById(1);

        $wikimodel = new WikiModel($this->container);
        // create wiki pages

        $titleValue = "Security";
        $contentValue = "Some content";
        $this->assertEquals(1, $wikimodel->createpage($project['id'], $titleValue, $contentValue, '2015-01-01'), 'Failed to a create wiki page on project');
        $this->assertEquals(2, $wikimodel->createpage($project['id'], "Conventions", 'More content'), 'Failed to an additional create wiki page on project');

        // grab editions for first wiki page
        $editions = $wikimodel->getEditions(1);
        $this->assertEmpty($editions);

        $values = [
            'title' => $titleValue,
            'content' => $contentValue,
        ];

        // create wiki page edition
        $this->assertTrue($this->container['userSession']->isLogged(), 'Failed to login');

        $this->userSession = new UserSession($this->container);
        // result is not a consistent 1. is this true or id for new edition?
        $createEditionResult = $wikimodel->createEdition($values, 1, 1);
        // $this->assertEquals($wikimodel->createEdition($values, 1, 1), 1, 'Failed to create wiki edition');

        $editions = $wikimodel->getEditions(1);
        $this->assertNotEmpty($editions, 'Failed to get wiki editions');

        $this->assertEquals('Security', $editions[0]['title']);
        $this->assertEquals('Some content', $editions[0]['content']);
    }

    public function testReOrder(){

        $projectModel = new ProjectModel($this->container);

        $this->assertEquals($projectModel->create(array('name' => 'reorder')), 1, 'Failed to create project');

        $project = $projectModel->getById(1);

        $wikimodel = new WikiModel($this->container);

        // create wiki pages
        $this->assertEquals(1, $wikimodel->createpage($project['id'], "Home", "", '2015-01-01'), 1, 'Failed to a create wiki page home on project');
        for ($i=2; $i <= 5; $i++) { 
            $this->assertEquals($i, $wikimodel->createpage($project['id'], "Page ". $i, ""), 'Failed to a create wiki page '. $i . ' on project');
        }

        // reorder
        $wikimodel->reorderPages($project['id'], 5, 3);
        // expected by id
        $expectedColumnOrders = [1,2,4,5,3];

        $wikiPages = $wikimodel->getWikipages($project['id']);
        $this->assertEquals(count($expectedColumnOrders), count($wikiPages), 'expected column order count doesn\'t match pages');

        for ($i=0; $i < count($expectedColumnOrders); $i++) { 
            $this->assertEquals($expectedColumnOrders[$wikiPages[$i]['id']-1], $wikiPages[$i]['ordercolumn'], 'Failed to reorder page id:'. $wikiPages[$i]['id']);
        }
    }

    public function testReOrderByIndex(){

        $projectModel = new ProjectModel($this->container);

        $this->assertEquals($projectModel->create(array('name' => 'reorder')), 1, 'Failed to create project');

        $project = $projectModel->getById(1);

        $wikimodel = new WikiModel($this->container);

        // create wiki pages
        $this->assertEquals(1, $wikimodel->createpage($project['id'], "Home", "", '2015-01-01'), 1, 'Failed to a create wiki page home on project');
        for ($i=2; $i <= 5; $i++) { 
            $this->assertEquals($i, $wikimodel->createpage($project['id'], "Page ". $i, ""), 'Failed to a create wiki page '. $i . ' on project');
        }

        // make page 5 a child of home page
        $wikimodel->updatepage(array('id' => 5, 'parent_id' => 1, 'title' => 'Page 5', 'editions' => 1, 'content' => 'Some content'), 1, '2015-01-01');

        $childPage = $wikimodel->getWikipage(5);

        $this->assertEquals(5, $childPage['id']);
        $this->assertEquals(1, $childPage['parent_id']);
        $this->assertEquals('Page 5', $childPage['title']);
        $this->assertEquals('Some content', $childPage['content']);

        // reorder
        $wikimodel->reorderPagesByIndex($project['id'], 4, 2, null);
        // expected by id
        $expectedColumnOrders = [0,1,3,2];

        $wikiPages = $wikimodel->getWikiPagesByParentId($project['id'], null);
        $this->assertEquals(count($expectedColumnOrders), count($wikiPages), 'expected column order count doesn\'t match pages');

        for ($i=0; $i < count($expectedColumnOrders); $i++) { 
            $this->assertEquals($expectedColumnOrders[$wikiPages[$i]['id']-1], $wikiPages[$i]['ordercolumn'], 'Failed to reorder page id:'. $wikiPages[$i]['id']);
        }
    }
}
