<?php

declare(strict_types=1);

namespace Kanboard\Plugin\Wiki\Model;

/**
 * Line diff (Myers) used to store editions as deltas.
 *
 * A patch is a list of hunks [start, deleteCount, insertedLines] against the old lines, in order.
 * Lines are split on "\n" only, so joining with "\n" gives back the exact text (CRLF included).
 */
final class WikiDiff
{
    /** Give up (caller stores a full copy) when more lines than this differ. */
    public const MAX_EDIT_DISTANCE = 1000;

    public static function lines(string $text): array
    {
        return explode("\n", $text);
    }

    public static function text(array $lines): string
    {
        return implode("\n", $lines);
    }

    /**
     * @return array|null hunks, or null when the texts differ too much to be worth a delta
     */
    public static function diff(string $old, string $new): ?array
    {
        if ($old === $new) {
            return array();
        }
        $a = self::lines($old);
        $b = self::lines($new);
        if ($old === '') {
            return array(array(0, 1, $b));
        }

        $n = count($a);
        $m = count($b);
        $prefix = 0;
        while ($prefix < $n && $prefix < $m && $a[$prefix] === $b[$prefix]) {
            $prefix++;
        }
        $suffix = 0;
        while ($suffix < $n - $prefix && $suffix < $m - $prefix && $a[$n - 1 - $suffix] === $b[$m - 1 - $suffix]) {
            $suffix++;
        }

        $edits = self::myers(
            array_slice($a, $prefix, $n - $prefix - $suffix),
            array_slice($b, $prefix, $m - $prefix - $suffix)
        );

        return $edits === null ? null : self::hunks($edits, $prefix);
    }

    /**
     * Every line as ['=', line], ['-', line] or ['+', line]. Past MAX_EDIT_DISTANCE: all old lines removed, all new added.
     */
    public static function edits(string $old, string $new): array
    {
        $a = self::lines($old);
        $b = self::lines($new);
        $n = count($a);
        $m = count($b);
        $prefix = 0;
        while ($prefix < $n && $prefix < $m && $a[$prefix] === $b[$prefix]) {
            $prefix++;
        }
        $suffix = 0;
        while ($suffix < $n - $prefix && $suffix < $m - $prefix && $a[$n - 1 - $suffix] === $b[$m - 1 - $suffix]) {
            $suffix++;
        }
        $middleA = array_slice($a, $prefix, $n - $prefix - $suffix);
        $middleB = array_slice($b, $prefix, $m - $prefix - $suffix);
        $middle = self::myers($middleA, $middleB);
        if ($middle === null) {
            $middle = array_merge(
                array_map(static fn ($line) => array('-', $line), $middleA),
                array_map(static fn ($line) => array('+', $line), $middleB)
            );
        }

        return array_merge(
            array_map(static fn ($line) => array('=', $line), array_slice($a, 0, $prefix)),
            $middle,
            array_map(static fn ($line) => array('=', $line), array_slice($a, $n - $suffix))
        );
    }

    /**
     * Unified diff text, like `diff -u` / `git diff`.
     */
    public static function unified(string $old, string $new, string $oldLabel, string $newLabel, int $context = 3): string
    {
        $edits = self::edits($old, $new);
        $changes = array();
        foreach ($edits as $index => $edit) {
            if ($edit[0] !== '=') {
                $changes[] = $index;
            }
        }
        if ($changes === array()) {
            return '';
        }

        // line numbers before each edit
        $oldNo = array();
        $newNo = array();
        $o = 1;
        $n = 1;
        foreach ($edits as $index => $edit) {
            $oldNo[$index] = $o;
            $newNo[$index] = $n;
            $o += $edit[0] === '+' ? 0 : 1;
            $n += $edit[0] === '-' ? 0 : 1;
        }

        $out = array('--- '.$oldLabel, '+++ '.$newLabel);
        $count = count($edits);
        $i = 0;
        while ($i < count($changes)) {
            $start = max(0, $changes[$i] - $context);
            $end = min($count - 1, $changes[$i] + $context);
            while ($i + 1 < count($changes) && $changes[$i + 1] - $context <= $end + 1) {
                $i++;
                $end = min($count - 1, $changes[$i] + $context);
            }
            $lines = array();
            $oldCount = 0;
            $newCount = 0;
            for ($j = $start; $j <= $end; $j++) {
                [$type, $line] = $edits[$j];
                $lines[] = ($type === '=' ? ' ' : $type).$line;
                $oldCount += $type === '+' ? 0 : 1;
                $newCount += $type === '-' ? 0 : 1;
            }
            $out[] = sprintf(
                '@@ -%d,%d +%d,%d @@',
                $oldCount === 0 ? $oldNo[$start] - 1 : $oldNo[$start], $oldCount,
                $newCount === 0 ? $newNo[$start] - 1 : $newNo[$start], $newCount
            );
            array_push($out, ...$lines);
            $i++;
        }

        return implode("\n", $out)."\n";
    }

    public static function apply(string $old, array $hunks): string
    {
        $a = self::lines($old);
        $count = count($a);
        $out = array();
        $position = 0;
        foreach ($hunks as $hunk) {
            if (! is_array($hunk) || count($hunk) !== 3 || ! is_int($hunk[0]) || ! is_int($hunk[1]) || ! is_array($hunk[2])) {
                throw new \UnexpectedValueException('Malformed wiki delta hunk.');
            }
            [$start, $delete, $insert] = $hunk;
            if ($start < $position || $delete < 0 || $start + $delete > $count) {
                throw new \UnexpectedValueException('Wiki delta does not match its base edition.');
            }
            for (; $position < $start; $position++) {
                $out[] = $a[$position];
            }
            foreach ($insert as $line) {
                if (! is_string($line)) {
                    throw new \UnexpectedValueException('Malformed wiki delta line.');
                }
                $out[] = $line;
            }
            $position += $delete;
        }
        for (; $position < $count; $position++) {
            $out[] = $a[$position];
        }

        return self::text($out);
    }

    /**
     * @return array{added: int, removed: int}
     */
    public static function stats(array $hunks): array
    {
        $added = 0;
        $removed = 0;
        foreach ($hunks as $hunk) {
            $removed += $hunk[1];
            $added += count($hunk[2]);
        }

        return array('added' => $added, 'removed' => $removed);
    }

    /**
     * Myers' O(ND) diff. Returns a list of ['=', '-', '+'] edits with the line, or null past MAX_EDIT_DISTANCE.
     *
     * The furthest x per diagonal k is kept in a zero-based list at k + $offset (not in negative keys: PHP 8.5's tracing
     * JIT was seen to compute wrong paths with them). Each round stores the slice for diagonals -d-1..d+1.
     */
    private static function myers(array $a, array $b): ?array
    {
        $n = count($a);
        $m = count($b);
        $max = min($n + $m, self::MAX_EDIT_DISTANCE);
        $offset = $max + 1;
        $v = array_fill(0, 2 * $max + 3, 0);
        $trace = array();

        for ($d = 0; $d <= $max; $d++) {
            $trace[] = array_slice($v, $offset - $d - 1, 2 * $d + 3);
            for ($k = -$d; $k <= $d; $k += 2) {
                $down = $k === -$d || ($k !== $d && $v[$offset + $k - 1] < $v[$offset + $k + 1]);
                $x = $down ? $v[$offset + $k + 1] : $v[$offset + $k - 1] + 1;
                $y = $x - $k;
                while ($x < $n && $y < $m && $a[$x] === $b[$y]) {
                    $x++;
                    $y++;
                }
                $v[$offset + $k] = $x;
                if ($x >= $n && $y >= $m) {
                    return self::backtrack($trace, $a, $b);
                }
            }
        }

        return null;
    }

    private static function backtrack(array $trace, array $a, array $b): array
    {
        $x = count($a);
        $y = count($b);
        $edits = array();
        for ($d = count($trace) - 1; $d >= 0; $d--) {
            $v = $trace[$d]; // diagonal k is at index k + d + 1
            $k = $x - $y;
            $down = $k === -$d || ($k !== $d && $v[$k + $d] < $v[$k + $d + 2]);
            $previousK = $down ? $k + 1 : $k - 1;
            $previousX = $v[$previousK + $d + 1];
            $previousY = $previousX - $previousK;
            while ($x > $previousX && $y > $previousY) {
                $edits[] = array('=', $a[$x - 1]);
                $x--;
                $y--;
            }
            if ($d > 0) {
                $edits[] = $x === $previousX ? array('+', $b[$y - 1]) : array('-', $a[$x - 1]);
            }
            $x = $previousX;
            $y = $previousY;
        }

        return array_reverse($edits);
    }

    private static function hunks(array $edits, int $offset): array
    {
        $hunks = array();
        $position = $offset;
        $current = null;
        foreach ($edits as [$type, $line]) {
            if ($type === '=') {
                if ($current !== null) {
                    $hunks[] = $current;
                    $current = null;
                }
                $position++;
                continue;
            }
            if ($current === null) {
                $current = array($position, 0, array());
            }
            if ($type === '-') {
                $current[1]++;
                $position++;
            } else {
                $current[2][] = $line;
            }
        }
        if ($current !== null) {
            $hunks[] = $current;
        }

        return $hunks;
    }
}
