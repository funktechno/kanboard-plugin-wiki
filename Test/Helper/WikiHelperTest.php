<?php

require_once 'tests/units/Base.php';

use Kanboard\Core\Plugin\Loader;
use Kanboard\Plugin\Wiki\Helper\WikiHelper;

class WikiHelperTest extends Base
{
    /**
     * @var Plugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $plugin = new Loader($this->container);
        $plugin->scan();
    }

    public function testRenderChildren(){
        $helper = new WikiHelper($this->container);
        $children = array(
            0=> array(
                'id'=> '5',
                'title'=> 'Page 1a',
                'ordercolumn' => '0',
                'children'=> array()
            )
        );

        $project = array(
            'id'=> '1',
        );

        $htmlResult = $helper->renderChildren($children, 1, $project, false);
        $this->assertStringContainsString('<ul data-parent-id="1"', $htmlResult, 'htmlResult should contain <ul data-parent-id="1"');
        $this->assertStringContainsString('<li class="wikipage" data-project-id="1" data-page-id="5"', $htmlResult, 'htmlResult should contain <li class="wikipage" data-project-id="1" data-page-id="5"');
    }
}