<?php

require_once 'tests/units/Base.php';
require_once __DIR__ . '/../AssertMultipleTrait.php';
use KanboardTests\units\Base;
use Kanboard\Core\Plugin\Loader;
use Kanboard\Plugin\Wiki\Helper\WikiHelper;

class WikiHelperTest extends Base
{
    use AssertMultipleTrait;

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

        $htmlResult = $helper->renderChildren($children, 1, $project, 0, false);
        $this->assertMultiple(
            fn() => $this->assertStringContainsString('<ul data-parent-id="1"', $htmlResult, 'htmlResult should contain <ul data-parent-id="1"'),
            fn() => $this->assertStringContainsString('<li class="wikipage" data-project-id="1" data-page-id="5"', $htmlResult, 'htmlResult should contain <li class="wikipage" data-project-id="1" data-page-id="5"'),
        );
    }

    public function testRenderChildrenEscapesTitles(){
        $helper = new WikiHelper($this->container);
        $children = array(
            array('id' => '5', 'title' => '50% done <b>x</b>', 'ordercolumn' => '0', 'children' => array()),
        );

        $htmlResult = $helper->renderChildren($children, 1, array('id' => '1'), 0, false);
        $this->assertMultiple(
            fn() => $this->assertStringContainsString('50% done &lt;b&gt;x&lt;/b&gt;', $htmlResult),
            fn() => $this->assertStringNotContainsString('<b>x</b>', $htmlResult),
        );
    }
}