<?php

require_once 'tests/units/Base.php';

use Kanboard\Auth\DatabaseAuth;
use Kanboard\Core\Http\Request;
use Kanboard\Core\Plugin\Loader;
use Kanboard\Core\Security\AuthenticationManager;
use Kanboard\Core\Security\Role;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\UserModel;
use Kanboard\Plugin\Wiki\Controller\WikiFileController;
use Kanboard\Plugin\Wiki\Model\WikiModel;
use KanboardTests\units\Base;

class WikiFileControllerTest extends Base
{
    private $projectModel;
    private $userModel;
    private $wikiModel;

    protected function setUp(): void
    {
        parent::setUp();

        $plugin = new Loader($this->container);
        $plugin->scan();

        $authManager = new AuthenticationManager($this->container);
        $authManager->register(new DatabaseAuth($this->container));

        $this->projectModel = new ProjectModel($this->container);
        $this->userModel = new UserModel($this->container);
        $this->wikiModel = new WikiModel($this->container);
    }

    public function testAttachmentFormRequiresAccessToOwningProjectWhenProjectIdIsMissing()
    {
        $wikiId = $this->createPrivateWikiPage();
        $this->buildRequest(array('wiki_id' => $wikiId));
        $this->loginAsBobWithoutProjectAccess();

        $controller = new WikiFileController($this->container);

        $this->expectException('Kanboard\Core\Controller\AccessForbiddenException');
        $controller->create();
    }

    private function createPrivateWikiPage()
    {
        $this->assertEquals(1, $this->projectModel->create(array('name' => 'Private Project')));

        return $this->wikiModel->createpage(1, 'Private Runbook', 'Secret content');
    }

    private function loginAsBobWithoutProjectAccess()
    {
        $this->assertEquals(2, $this->userModel->create(array('username' => 'bob')));

        $_SESSION['user'] = array(
            'id' => 2,
            'role' => Role::APP_USER,
            'username' => 'bob',
        );
    }

    private function buildRequest(array $params)
    {
        $this->container['request'] = new Request($this->container, array('REQUEST_METHOD' => 'GET'), $params);
    }
}
