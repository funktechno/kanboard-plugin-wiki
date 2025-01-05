<?php


require_once 'tests/units/Base.php';

use Kanboard\Core\Plugin\Loader;
use Kanboard\Plugin\Wiki\Model\WikiModel;
use Kanboard\Model\ProjectModel;
use Kanboard\Core\Controller\PageNotFoundException;
use Kanboard\Core\Controller\AccessForbiddenException;
use Kanboard\Core\User\UserSession;
use Kanboard\Core\Security\AuthenticationManager;
use Kanboard\Auth\DatabaseAuth;

class WikiModelTest extends Base
{
    /**
     * @var Plugin
     */
    protected $plugin;
    private $wikiModel;
    private $db;

    protected function setUp(): void
    {
        parent::setUp();

        $plugin = new Loader($this->container);
        $plugin->scan();
        
        $authManager = new AuthenticationManager($this->container);
        $authManager->register(new DatabaseAuth($this->container));

        $_SESSION['user'] = array('id' => 1, 'username' => 'test', 'role' => 'app-admin');

        // $this->db = $this->createMock(Base::class);
        $this->wikiModel = new WikiModel($this->container);
        
        // $this->wikiModel = new WikiModel($this->container);
    }

    public function testCreation()
    {
        $projectModel = new ProjectModel($this->container);

        $this->assertEquals($projectModel->create(array('name' => 'UnitTest')), 1, 'Failed to create project');

        $project = $projectModel->getById(1);

        // $this->wikiModel = new WikiModel($this->container);
        // create wiki pages

        $titleValue = "Security";
        $contentValue = "Some content";
        $this->assertEquals(1, $this->wikiModel->createpage($project['id'], $titleValue, $contentValue, '2015-01-01'), 'Failed to a create wiki page on project');
        $this->assertEquals(2, $this->wikiModel->createpage($project['id'], "Conventions", 'More content'), 'Failed to an additional create wiki page on project');

        // grab editions for first wiki page
        $editions = $this->wikiModel->getEditions(1);
        $this->assertEmpty($editions);

        $values = [
            'title' => $titleValue,
            'content' => $contentValue,
        ];

        // create wiki page edition
        $this->assertTrue($this->container['userSession']->isLogged(), 'Failed to login');

        $this->userSession = new UserSession($this->container);
        // result is not a consistent 1. is this true or id for new edition?
        $createEditionResult = $this->wikiModel->createEdition($values, 1, 1);
        // $this->assertEquals($this->wikiModel->createEdition($values, 1, 1), 1, 'Failed to create wiki edition');

        $editions = $this->wikiModel->getEditions(1);
        $this->assertNotEmpty($editions, 'Failed to get wiki editions');

        $this->assertEquals('Security', $editions[0]['title']);
        $this->assertEquals('Some content', $editions[0]['content']);
    }

    public function testReOrder(){

        $projectModel = new ProjectModel($this->container);

        $this->assertEquals($projectModel->create(array('name' => 'reorder')), 1, 'Failed to create project');

        $project = $projectModel->getById(1);

        // $this->wikiModel = new WikiModel($this->container);

        // create wiki pages
        $this->assertEquals(1, $this->wikiModel->createpage($project['id'], "Home", "", '2015-01-01'), 1, 'Failed to a create wiki page home on project');
        for ($i=2; $i <= 5; $i++) { 
            $this->assertEquals($i, $this->wikiModel->createpage($project['id'], "Page ". $i, ""), 'Failed to a create wiki page '. $i . ' on project');
        }

        // reorder
        $this->wikiModel->reorderPages($project['id'], 5, 3);
        // expected by id
        $expectedColumnOrders = [1,2,4,5,3];

        $wikiPages = $this->wikiModel->getWikipages($project['id']);
        $this->assertEquals(count($expectedColumnOrders), count($wikiPages), 'expected column order count doesn\'t match pages');

        for ($i=0; $i < count($expectedColumnOrders); $i++) { 
            $this->assertEquals($expectedColumnOrders[$wikiPages[$i]['id']-1], $wikiPages[$i]['ordercolumn'], 'Failed to reorder page id:'. $wikiPages[$i]['id']);
        }
    }

    public function testReOrderByIndex(){

        $projectModel = new ProjectModel($this->container);

        $this->assertEquals($projectModel->create(array('name' => 'reorder')), 1, 'Failed to create project');

        $project = $projectModel->getById(1);

        // $this->wikiModel = new WikiModel($this->container);

        // create wiki pages
        $this->assertEquals(1, $this->wikiModel->createpage($project['id'], "Home", "", '2015-01-01'), 1, 'Failed to a create wiki page home on project');
        for ($i=2; $i <= 5; $i++) { 
            $this->assertEquals($i, $this->wikiModel->createpage($project['id'], "Page ". $i, ""), 'Failed to a create wiki page '. $i . ' on project');
        }

        // make page 5 a child of home page
        $this->wikiModel->updatepage(array('id' => 5, 'parent_id' => 1, 'title' => 'Page 5', 'editions' => 1, 'content' => 'Some content'), 1, '2015-01-01');

        $childPage = $this->wikiModel->getWikipage(5);

        $this->assertEquals(5, $childPage['id']);
        $this->assertEquals(1, $childPage['parent_id']);
        $this->assertEquals('Page 5', $childPage['title']);
        $this->assertEquals('Some content', $childPage['content']);

        // reorder
        $this->wikiModel->reorderPagesByIndex($project['id'], 4, 2, null);
        // expected by id
        $expectedColumnOrders = [1,3,4,2];

        $wikiPages = $this->wikiModel->getWikiPagesByParentId($project['id'], null);
        $this->assertEquals(count($expectedColumnOrders), count($wikiPages), 'expected column order count doesn\'t match pages');

        for ($i=0; $i < count($expectedColumnOrders); $i++) { 
            $this->assertEquals($expectedColumnOrders[$wikiPages[$i]['id']-1], $wikiPages[$i]['ordercolumn'], 'Failed to reorder page id:'. $wikiPages[$i]['id']);
        }
    }
    /*
    public function testGetEditionsReturnsArray()
    {
        $wikiId = 1;
        $expectedResult = [
            ['id' => 1, 'title' => 'My page', 'content' => 'Content of the page', 'edition' => 1]
        ];

        // $this->db
        //     ->expects($this->once())
        //     ->method('table')
        //     ->with(WikiModel::WIKI_EDITION_TABLE)
        //     ->willReturn($this->db);

        // $this->db
        //     ->expects($this->once())
        //     ->method('eq')
        //     ->with('wikipage_id', $wikiId)
        //     ->willReturn($this->db);

        // $this->db
        //     ->expects($this->once())
        //     ->method('desc')
        //     ->with('edition')
        //     ->willReturn($this->db);

        // $this->db
        //     ->expects($this->once())
        //     ->method('findAll')
        //     ->willReturn($expectedResult);

        $result = $this->wikiModel->getEditions($wikiId);
        $this->assertEquals(json_encode($expectedResult), json_encode($result));
    }
        */
    public function testCreatePageSavesCorrectly()
    {
        // $this->db
        //     ->expects($this->once())
        //     ->method('table')
        //     ->with(WikiModel::WIKITABLE)
        //     ->willReturnSelf();

        // $this->db
        //     ->method('persist')
        //     ->willReturn(1);

        $result = $this->wikiModel->createpage(1, 'Test title', 'Test content');

        $this->assertEquals(false, $result);
    }

    public function testUpdatePageUpdatesCorrectly()
    {
        // $this->db
        //     ->expects($this->once())
        //     ->method('table')
        //     ->with(WikiModel::WIKITABLE)
        //     ->willReturnSelf();

        // $this->db
        //     ->method('eq')
        //     ->with('id', 1)
        //     ->willReturnSelf();

        // $this->db
        //     ->method('update')
        //     ->willReturn(true);

        $result = $this->wikiModel->updatepage(['title' => 'New Title', 'content' => 'New content', 'id' => 1], 1);

        $this->assertEquals(1, $result);
    }

    public function testRemovePageRemovesCorrectly()
    {
        // $this->db
        //     ->expects($this->once())
        //     ->method('table')
        //     ->with(WikiModel::WIKITABLE)
        //     ->willReturnSelf();

        // $this->db
        //     ->method('eq')
        //     ->with('id', 1)
        //     ->willReturnSelf();

        // $this->db
        //     ->method('remove')
        //     ->willReturn(true);

        $result = $this->wikiModel->removepage(1);

        $this->assertTrue($result);
    }

    public function testGetWikipageReturnsCorrectData()
    {
        // $this->db
        //     ->expects($this->once())
        //     ->method('table')
        //     ->with(WikiModel::WIKITABLE)
        //     ->willReturnSelf();

        // $this->db
        //     ->method('columns')
        //     ->willReturnSelf();

        // $this->db
        //     ->method('eq')
        //     ->with('id', 1)
        //     ->willReturnSelf();

        $expected = ['id' => 1, 'title' => 'Test'];

        // $this->db
        //     ->method('findOne')
        //     ->willReturn($expected);

        $result = $this->wikiModel->getWikipage(1);

        $this->assertSame($expected, $result);
    }

    public function testGetLatestEditionReturnsArray()
    {
        $wikiId = 1;
        $expectedResult = ['id' => 1, 'title' => 'Page Title', 'edition' => 2];

        // $this->db
        //     ->expects($this->once())
        //     ->method('table')
        //     ->with(WikiModel::WIKI_EDITION_TABLE)
        //     ->willReturn($this->db);

        // $this->db
        //     ->expects($this->once())
        //     ->method('eq')
        //     ->with('wikipage_id', $wikiId)
        //     ->willReturn($this->db);

        // $this->db
        //     ->expects($this->once())
        //     ->method('desc')
        //     ->with('edition')
        //     ->willReturn($this->db);

        // $this->db
        //     ->expects($this->once())
        //     ->method('findOne')
        //     ->willReturn($expectedResult);

        $result = $this->wikiModel->getLatestEdition($wikiId);
        $this->assertEquals($expectedResult, $result);
    }

    public function testRemovePageWithNonExistentIdReturnsFalse()
    {
        $wikiId = 999;

        // $this->db
        //     ->expects($this->once())
        //     ->method('table')
        //     ->with(WikiModel::WIKITABLE)
        //     ->willReturn($this->db);

        // $this->db
        //     ->expects($this->once())
        //     ->method('eq')
        //     ->with('id', $wikiId)
        //     ->willReturn($this->db);

        // $this->db
        //     ->expects($this->once())
        //     ->method('remove')
        //     ->willReturn(false);

        $result = $this->wikiModel->removepage($wikiId);
        $this->assertFalse($result);
    }

    public function testGetWikipageThrowsPageNotFoundException()
    {
        // $this->db
        //     ->method('findOne')
        //     ->willReturn(null);

        $this->expectException(\Kanboard\Core\ExternalTask\NotFoundException::class);

        $this->wikiModel->getWiki();
    }

    public function testGetWikiPageThrowsPageNotFoundException2()
    {
        $this->expectException(PageNotFoundException::class);
        $wikiId = 999;

        // $this->db
        //     ->expects($this->once())
        //     ->method('table')
        //     ->with(WikiModel::WIKITABLE)
        //     ->willReturn($this->db);

        // $this->db
        //     ->expects($this->once())
        //     ->method('eq')
        //     ->with('id', $wikiId)
        //     ->willReturn($this->db);

        // $this->db
        //     ->expects($this->once())
        //     ->method('findOne')
        //     ->willReturn(null);

        $this->wikiModel->getWikipage($wikiId);
    }

    public function testCreatePageWithValidData()
    {
        $project_id = 1;
        $title = 'New Page';
        $content = 'Content';
        $date = date('Y-m-d');
        $values = [
            'project_id' => $project_id,
            'title' => $title,
            'content' => $content,
            'date_creation' => $date,
            'ordercolumn' => 1
        ];

        // $this->db
        //     ->expects($this->once())
        //     ->method('table')
        //     ->with(WikiModel::WIKITABLE)
        //     ->willReturn($this->db);

        // $this->db
        //     ->expects($this->once())
        //     ->method('persist')
        //     ->with($values);

        $result = $this->wikiModel->createpage($project_id, $title, $content, $date);
        $this->assertNotNull($result);
    }

    // Additional test cases can be defined here for each method
}