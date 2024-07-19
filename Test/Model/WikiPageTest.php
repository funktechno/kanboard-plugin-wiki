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
    }

    public function testCreation()
    {
        
        $projectModel = new ProjectModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'UnitTest')), 'Failed to create project');

        $project = $projectModel->getById(1);

        $wikimodel = new Wiki($this->container);
        // create wiki pages
        $this->assertEquals(1, $wikimodel->createpage($project['id'], "Security", "Some content", '2015-01-01'), 'Failed to a create wiki page on project');
        $this->assertEquals(2, $wikimodel->createpage($project['id'], "Conventions", 'More content'), 'Failed to an additional create wiki page on project');

        // grab editions for first wiki page
        $editions = $wikimodel->getEditions(1);
        $this->assertEmpty($editions);

        $values = [
            'title' => "Security",
            'content' => "Some content",
        ];

        // create wiki page edition
        // $this->userSession = new UserSession($this->container);
        
        $authManager = new AuthenticationManager($this->container);
        $authManager->register(new DatabaseAuth($this->container));

        $_SESSION['user'] = array('id' => 1, 'username' => 'test', 'role' => 'app-admin');
        $this->assertEquals(1, $wikimodel->createEdition($values, 1, 1), 'Failed to create wiki edition');

        $editions = $wikimodel->getEditions(1);
        $this->assertNotEmpty($editions, 'Failed to get wiki editions');

        $this->assertEquals('Security', $editions[0]['title']);
        $this->assertEquals('Some content', $editions[0]['content']);
    }
}
