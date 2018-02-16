<?php

require_once 'tests/units/Base.php';

use Kanboard\Core\Plugin\Loader;
use Kanboard\Plugin\Wiki\Model\Wiki;

class WikiPageTest extends Base
{
    public function setUp()
    {
        parent::setUp();

        $plugin = new Loader($this->container);
        $plugin->scan();
    }

    public function testCreation()
    {
        $wikimodel = new Wiki($this->container);
        // $this->assertEquals(1, $wikimodel->createpage(1, "Security", "Some content", '2015-01-01'));
        // $this->assertEquals(2, $wikimodel->createpage(1, "Conventions", 'More content'));

        
        // $editions = $wikimodel->getEditions(1);
        // $this->assertEmpty($editions);

        // $values = [
        //     'title' => "Security",
        //     'content' => "Some content",
        // ];

        // $this->assertEquals(1, $wikimodel->createEdition($values, 1, 1));

        // createpage

        // $rates = $hr->getAllByUser(0);
        // $this->assertEmpty($rates);

        // $editions = $wikimodel->getEditions(1);
        // $this->assertNotEmpty($editions);
        // $rates = $hr->getAllByUser(1);
        // $this->assertNotEmpty($rates);
        // $this->assertCount(1, $editions);

        // $this->assertEquals(42, $rates[0]['rate']);
        // $this->assertEquals('Security', $editions[0]['title']);
        // $this->assertEquals('Some content', $editions[0]['content']);

        // $this->assertEquals('2015-02-01', date('Y-m-d', $rates[0]['date_effective']));

        // $this->assertEquals(32.4, $rates[1]['rate']);
        // $this->assertEquals('EUR', $rates[1]['currency']);
        // $this->assertEquals('2015-01-01', date('Y-m-d', $rates[1]['date_effective']));

        // $this->assertEquals(0, $hr->getCurrentRate(0));
        // $this->assertEquals(42, $hr->getCurrentRate(1));

        // $this->assertTrue($wikimodel->removepage(1));
        // $this->assertEquals(32.4, $hr->getCurrentRate(1));

        // $this->assertTrue($hr->remove(1));
        // $this->assertEquals(0, $hr->getCurrentRate(1));

        // $rates = $hr->getAllByUser(1);
        // $this->assertEmpty($rates);
    }
}
