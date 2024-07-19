<?php

require_once 'tests/units/Base.php';

use Kanboard\Core\Plugin\Loader;
use Kanboard\Plugin\Wiki\Model\Wiki;
use Kanboard\Model\ProjectModel;
use Kanboard\Core\User\UserSession;
use Kanboard\Core\Security\AuthenticationManager;
use Kanboard\Auth\DatabaseAuth;

class WikiPageTest extends Base
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

        $wikimodel = new Wiki($this->container);
        // create wiki pages
        $this->assertEquals($wikimodel->createpage($project['id'], "Security", "Some content", '2015-01-01'), 1, 'Failed to a create wiki page on project');
        $this->assertEquals($wikimodel->createpage($project['id'], "Conventions", 'More content'), 2, 'Failed to an additional create wiki page on project');

        // grab editions for first wiki page
        $editions = $wikimodel->getEditions(1);
        $this->assertEmpty($editions);

        $values = [
            'title' => "Security",
            'content' => "Some content",
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

        $wikimodel = new Wiki($this->container);

        ]
        // create wiki pages
        $this->assertEquals($wikimodel->createpage($project['id'], "Home", "", '2015-01-01'), 1, 'Failed to a create wiki page home on project');
        $this->assertEquals($wikimodel->createpage($project['id'], "Page 2", ""), 2, 'Failed to a create wiki page 2 on project');
        $this->assertEquals($wikimodel->createpage($project['id'], "Page 3", ""), 3, 'Failed to a create wiki page 3 on project');
        $this->assertEquals($wikimodel->createpage($project['id'], "Page 4", ""), 4, 'Failed to a create wiki page 4 on project');
        $this->assertEquals($wikimodel->createpage($project['id'], "Page 5", ""), 5, 'Failed to a create wiki page 5 on project');

        // reorder
        $wikimodel->reorderPages($project['id'], 5, 3);

        $expectedColumnOrders = [1,2,4,5,3]

        $wikiPages = $wikimodel->getWikipages($project['id']);
        $this->assertEquals(count($expectedColumnOrders), count($wikiPages), 'expected column order count doesn\'t match pages');

        for ($i=0; $i < count($expectedColumnOrders); $i++) { 
            $this->assertEquals($wikiPages[$]['ordercolumn'], $expectedColumnOrders[$i], 'Failed to reorder '. $i);
        }
    }
}
