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
}
