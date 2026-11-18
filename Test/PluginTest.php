<?php

require_once 'tests/units/Base.php';
require_once __DIR__ . '/AssertMultipleTrait.php';
use KanboardTests\units\Base;
use Kanboard\Plugin\Wiki\Plugin;

class PluginTest extends Base
{
    use AssertMultipleTrait;

    public function testPlugin()
    {
        $plugin = new Plugin($this->container);
        $this->assertMultiple(
            fn() => $this->assertSame(null, $plugin->initialize()),
            fn() => $this->assertSame(null, $plugin->onStartup()),
            fn() => $this->assertNotEmpty($plugin->getPluginName()),
            fn() => $this->assertNotEmpty($plugin->getPluginDescription()),
            fn() => $this->assertNotEmpty($plugin->getPluginAuthor()),
            fn() => $this->assertNotEmpty($plugin->getPluginVersion()),
            fn() => $this->assertNotEmpty($plugin->getPluginHomepage()),
        );
    }
}
