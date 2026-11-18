<?php

/**
 * Run several checks and report every failure at once, like Assert.Multiple in xUnit/NUnit.
 *
 * Every check runs, even after an earlier one fails. Assertion failures and any other
 * exception or error are collected and reported together as a single test failure.
 *
 *   $this->assertMultiple(
 *       fn() => $this->assertEquals(1, $a),
 *       fn() => $this->assertEquals(2, $b),
 *   );
 */
trait AssertMultipleTrait
{
    protected function assertMultiple(callable ...$checks): void
    {
        $failures = array();

        foreach ($checks as $i => $check) {
            try {
                $check();
            } catch (\Throwable $e) {
                $line = $check instanceof \Closure ? ' (line ' . (new \ReflectionFunction($check))->getStartLine() . ')' : '';
                $type = $e instanceof \PHPUnit\Framework\AssertionFailedError ? '' : get_class($e) . ': ';
                $failures[] = '#' . ($i + 1) . $line . ' ' . $type . $e->getMessage();
            }
        }

        if ($failures) {
            $this->fail(count($failures) . ' of ' . count($checks) . " checks failed:\n" . implode("\n", $failures));
        }
    }
}
