<?php

require_once 'tests/units/Base.php';
require_once __DIR__ . '/../AssertMultipleTrait.php';

use KanboardTests\units\Base;
use Kanboard\Plugin\Wiki\Model\WikiDiff;

class WikiDiffTest extends Base
{
    use AssertMultipleTrait;

    private function roundTrip(string $old, string $new): array
    {
        $hunks = WikiDiff::diff($old, $new);
        $this->assertNotNull($hunks);
        $this->assertSame($new, WikiDiff::apply($old, $hunks), json_encode(array($old, $new)));

        return $hunks;
    }

    public function testEditsRoundTrip()
    {
        $this->assertMultiple(
            fn() => $this->assertSame(array(), $this->roundTrip("a\nb", "a\nb")),
            fn() => $this->assertSame(array(array(1, 0, array('x'))), $this->roundTrip("a\nb", "a\nx\nb")),
            fn() => $this->assertSame(array(array(1, 1, array())), $this->roundTrip("a\nb\nc", "a\nc")),
            fn() => $this->assertSame(array(array(1, 1, array('B'))), $this->roundTrip("a\nb\nc", "a\nB\nc")),
            fn() => $this->roundTrip('', "first\nsecond"),
            fn() => $this->roundTrip("first\nsecond", ''),
            fn() => $this->roundTrip("start\nmiddle\nend", "START\nmiddle\nEND"),
            fn() => $this->roundTrip("a\r\nb\r\n", "a\r\nb\r\nc\r\n"),
            fn() => $this->roundTrip("ünïcødé\n日本語", "ünïcødé\n日本語!\n✓"),
            fn() => $this->roundTrip("no newline", "no newline\n"),
        );
    }

    public function testRandomEditsRoundTrip()
    {
        mt_srand(42);
        $words = array('alpha', 'beta', 'gamma', '', '# heading', '* item', 'beta');
        for ($i = 0; $i < 200; $i++) {
            $old = array();
            for ($j = mt_rand(0, 30); $j > 0; $j--) {
                $old[] = $words[mt_rand(0, count($words) - 1)];
            }
            $new = $old;
            for ($j = mt_rand(0, 8); $j > 0; $j--) {
                $at = mt_rand(0, count($new));
                switch (mt_rand(0, 2)) {
                    case 0:
                        array_splice($new, $at, 0, array($words[mt_rand(0, count($words) - 1)]));
                        break;
                    case 1:
                        array_splice($new, $at, 1);
                        break;
                    default:
                        array_splice($new, $at, 1, array('changed '.$i));
                }
            }
            $this->roundTrip(implode("\n", $old), implode("\n", $new));
        }
    }

    public function testStatsAndLimits()
    {
        $hunks = WikiDiff::diff("a\nb\nc", "a\nB\nc\nd");
        $many = implode("\n", range(1, WikiDiff::MAX_EDIT_DISTANCE + 10));
        $this->assertMultiple(
            fn() => $this->assertSame(array('added' => 2, 'removed' => 1), WikiDiff::stats($hunks)),
            fn() => $this->assertNull(WikiDiff::diff($many, strrev($many)), 'a full rewrite is not worth a delta'),
        );
    }

    public function testUnifiedMatchesDiffU()
    {
        $old = implode("\n", range('a', 'j'));
        $new = str_replace("\nb\n", "\nB\n", $old)."\nk";
        $expected = "--- old\n+++ new\n"
            ."@@ -1,5 +1,5 @@\n a\n-b\n+B\n c\n d\n e\n"
            ."@@ -8,3 +8,4 @@\n h\n i\n j\n+k\n";
        $this->assertMultiple(
            fn() => $this->assertSame($expected, WikiDiff::unified($old, $new, 'old', 'new')),
            fn() => $this->assertSame('', WikiDiff::unified($old, $old, 'old', 'new')),
            fn() => $this->assertSame("--- old\n+++ new\n@@ -1,1 +1,1 @@\n-\n+x\n", WikiDiff::unified('', 'x', 'old', 'new'), 'empty text is one empty line'),
        );
    }

    public function testApplyRejectsPatchForOtherText()
    {
        $this->expectException(\UnexpectedValueException::class);
        WikiDiff::apply("one line", array(array(3, 1, array('x'))));
    }
}
