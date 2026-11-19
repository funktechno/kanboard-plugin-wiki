<?php

declare(strict_types=1);

namespace Kanboard\Plugin\Wiki\Model;

use Kanboard\Core\Base;

/**
 * The only class that reads or writes wikipage_editions.
 *
 * Rows are either full text (keyframes, and every row written before deltas existed) or a delta from an earlier row:
 *   "\x1Fwiki-delta:1:<base edition>:<flags>\n" followed by the JSON hunks from WikiDiff.
 * Base 0 means the empty text. Flag "u": the edition holds changes made while edition saving was off.
 * A full copy is written every KEYFRAME_INTERVAL rows, and whenever a delta would not round-trip.
 */
class WikiEditionStore extends Base
{
    public const TABLE = 'wikipage_editions';
    public const MARKER = "\x1Fwiki-delta:1:";
    public const KEYFRAME_INTERVAL = 20;
    public const FLAG_UNRECORDED = 'u';

    public function isEnabled(): bool
    {
        return (string) $this->configModel->get('persistEditions') === '1';
    }

    /**
     * Record a new edition (when edition saving is on).
     *
     * $before is the page as it was before this save (title, content, modifier_id, date_modification). When it differs
     * from the latest edition, edits were made while saving was off: they are recorded first as their own edition.
     *
     * @return bool whether a row was written
     */
    public function record(int $pageId, int $edition, string $title, string $content, int $actorId, ?array $before = null, bool $skipUnchanged = true): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $rows = $this->rebuild($pageId);
        $latest = $rows === array() ? null : end($rows);

        if ($before !== null && $edition - 1 > ($latest['edition'] ?? 0)) {
            $beforeTitle = (string) $before['title'];
            $beforeContent = (string) $before['content'];
            if ($latest === null || $latest['title'] !== $beforeTitle || $latest['content'] !== $beforeContent) {
                $this->insert($pageId, $edition - 1, $beforeTitle, $beforeContent, (int) ($before['modifier_id'] ?? 0), (string) ($before['date_modification'] ?? ''), $latest, self::FLAG_UNRECORDED);
                $rows = $this->rebuild($pageId);
                $latest = end($rows);
            }
        }

        if ($skipUnchanged && $latest !== null && $latest['title'] === $title && $latest['content'] === $content) {
            return false;
        }

        $this->insert($pageId, $edition, $title, $content, $actorId, (string) time(), $latest);

        return true;
    }

    /**
     * Every edition of a page with its full text, newest first.
     * Extra keys: base (edition it was compared with, or null), added, removed, unrecorded.
     */
    public function all(int $pageId): array
    {
        return array_reverse(array_values($this->rebuild($pageId)));
    }

    public function latest(int $pageId): ?array
    {
        $rows = $this->rebuild($pageId);

        return $rows === array() ? null : end($rows);
    }

    public function get(int $pageId, int $edition): ?array
    {
        return $this->rebuild($pageId, $edition)[$edition] ?? null;
    }

    /**
     * Delete one edition. The edition after it is re-stored against the purged edition's base, so the chain stays intact.
     */
    public function purge(int $pageId, int $edition): bool
    {
        $rows = $this->rebuild($pageId);
        if (! isset($rows[$edition])) {
            return false;
        }

        foreach ($rows as $row) {
            if ($row['base'] === $edition && $row['stored_delta']) {
                $base = $rows[$edition]['base'] === null ? null : $rows[$rows[$edition]['base']] ?? null;
                $this->db->table(self::TABLE)
                    ->eq('wikipage_id', $pageId)
                    ->eq('edition', $row['edition'])
                    ->update(array('content' => $this->encode($row['content'], $base, $row['unrecorded'] ? self::FLAG_UNRECORDED : '')));
            }
        }

        return $this->db->table(self::TABLE)->eq('wikipage_id', $pageId)->eq('edition', $edition)->remove();
    }

    /**
     * Rewrite every page's editions: as deltas ($compact) or as full copies, which older plugin versions need.
     *
     * @return array{pages: int, rows: int, failed: int[]} rows rewritten, and pages left unchanged because they did not verify
     */
    public function rewriteAll(bool $compact): array
    {
        $result = array('pages' => 0, 'rows' => 0, 'failed' => array());
        $pageIds = $this->db->table(self::TABLE)->columns('wikipage_id')->groupBy('wikipage_id')->findAllByColumn('wikipage_id');
        foreach ($pageIds as $pageId) {
            $rows = $this->rewritePage((int) $pageId, $compact);
            if ($rows === null) {
                $result['failed'][] = (int) $pageId;
            } elseif ($rows > 0) {
                $result['pages']++;
                $result['rows'] += $rows;
            }
        }

        return $result;
    }

    /**
     * @return int|null rows rewritten, or null when the page was left unchanged (unreadable rows, or the result did not verify)
     */
    public function rewritePage(int $pageId, bool $compact): ?int
    {
        $raw = $this->db->table(self::TABLE)->eq('wikipage_id', $pageId)->asc('edition')->findAllByColumn('content');
        $rows = $this->rebuild($pageId);
        if (count($raw) !== count($rows)) {
            return null;
        }
        $stored = array_combine(array_keys($rows), $raw);

        $this->db->startTransaction();
        $previous = null;
        $depth = 0;
        $changed = 0;
        foreach ($rows as $edition => $row) {
            if ($compact) {
                $base = ($previous !== null && $depth < self::KEYFRAME_INTERVAL - 1) ? $previous : null;
                $content = $this->encode($row['content'], $base, $row['unrecorded'] ? self::FLAG_UNRECORDED : '');
                $delta = $this->decode($content);
                $depth = ($delta !== null && $delta['base'] !== 0) ? $depth + 1 : 0;
            } else {
                $content = $row['content'];
            }
            if ($content !== $stored[$edition]) {
                $this->db->table(self::TABLE)->eq('wikipage_id', $pageId)->eq('edition', $edition)->update(array('content' => $content));
                $changed++;
            }
            $previous = $row;
        }

        if (array_column($this->rebuild($pageId), 'content', 'edition') !== array_column($rows, 'content', 'edition')) {
            $this->db->cancelTransaction();
            return null;
        }
        $this->db->closeTransaction();

        return $changed;
    }

    /**
     * Rebuild full text for the editions of a page, oldest first, keyed by edition.
     */
    private function rebuild(int $pageId, ?int $upTo = null): array
    {
        $query = $this->db->table(self::TABLE)
            ->columns('edition', 'title', 'content', 'creator_id', 'date_creation')
            ->eq('wikipage_id', $pageId);
        if ($upTo !== null) {
            $query->lte('edition', $upTo);
        }

        $rows = array();
        $depth = 0;
        foreach ($query->asc('edition')->findAll() as $row) {
            $edition = (int) $row['edition'];
            $stored = (string) $row['content'];
            $previous = $rows === array() ? null : end($rows);
            $delta = $this->decode($stored);

            if ($delta === null) {
                $content = $stored;
                $base = $previous === null ? null : $previous['edition'];
                $stats = $previous === null ? null : $this->statsBetween($previous['content'], $content);
                $depth = 0;
            } else {
                $baseText = $delta['base'] === 0 ? '' : ($rows[$delta['base']]['content'] ?? null);
                if ($baseText === null) {
                    continue; // base edition missing: skip rather than show wrong text
                }
                try {
                    $content = WikiDiff::apply($baseText, $delta['hunks']);
                } catch (\UnexpectedValueException $exception) {
                    continue;
                }
                $base = $delta['base'] === 0 ? null : $delta['base'];
                $stats = $base === null ? null : WikiDiff::stats($delta['hunks']);
                $depth = $base === null ? 0 : $depth + 1;
            }

            $rows[$edition] = array(
                'edition' => $edition,
                'title' => (string) $row['title'],
                'content' => $content,
                'creator_id' => (int) $row['creator_id'],
                'date_creation' => $row['date_creation'],
                'base' => $base,
                'added' => $stats['added'] ?? null,
                'removed' => $stats['removed'] ?? null,
                'unrecorded' => $delta !== null && strpos($delta['flags'], self::FLAG_UNRECORDED) !== false,
                'stored_delta' => $delta !== null,
                'depth' => $depth,
            );
        }

        return $rows;
    }

    private function insert(int $pageId, int $edition, string $title, string $content, int $actorId, string $date, ?array $latest, string $flags = ''): void
    {
        $base = ($latest !== null && $latest['depth'] < self::KEYFRAME_INTERVAL - 1) ? $latest : null;

        $this->db->table(self::TABLE)->insert(array(
            'edition' => $edition,
            'title' => $title,
            'content' => $this->encode($content, $base, $flags),
            'creator_id' => $actorId,
            'date_creation' => $date,
            'wikipage_id' => $pageId,
        ));
    }

    /**
     * Stored form of $content: a delta from $base when that round-trips, else the full text.
     */
    private function encode(string $content, ?array $base, string $flags = ''): string
    {
        if ($base !== null) {
            $hunks = WikiDiff::diff($base['content'], $content);
            if ($hunks !== null) {
                $stored = $this->deltaString((int) $base['edition'], $flags, $hunks);
                $smaller = $stored !== null && ($flags !== '' || strlen($stored) < strlen($content));
                if ($smaller && $this->roundTrips($stored, $base['content'], $content)) {
                    return $stored;
                }
            }
        }

        // a full copy. It must not look like a delta, and flags need the delta form: store those as a delta from ''
        if ($flags === '' && strpos($content, "\x1F") !== 0) {
            return $content;
        }
        $stored = $this->deltaString(0, $flags, WikiDiff::diff('', $content) ?? array());
        if ($stored === null || ! $this->roundTrips($stored, '', $content)) {
            return $content; // not representable (e.g. invalid UTF-8): keep the text, lose the flag
        }

        return $stored;
    }

    private function deltaString(int $base, string $flags, array $hunks): ?string
    {
        $json = json_encode($hunks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $json === false ? null : self::MARKER.$base.':'.$flags."\n".$json;
    }

    private function roundTrips(string $stored, string $baseText, string $content): bool
    {
        $delta = $this->decode($stored);
        if ($delta === null) {
            return false;
        }
        try {
            return WikiDiff::apply($baseText, $delta['hunks']) === $content;
        } catch (\UnexpectedValueException $exception) {
            return false;
        }
    }

    /**
     * @return array{base: int, flags: string, hunks: array}|null null when the row is full text
     */
    private function decode(string $stored): ?array
    {
        if (strpos($stored, self::MARKER) !== 0) {
            return null;
        }
        $newline = strpos($stored, "\n");
        if ($newline === false) {
            return null;
        }
        $header = explode(':', substr($stored, strlen(self::MARKER), $newline - strlen(self::MARKER)));
        $hunks = json_decode(substr($stored, $newline + 1), true);
        if (count($header) !== 2 || ! ctype_digit($header[0]) || ! is_array($hunks)) {
            return null;
        }

        return array('base' => (int) $header[0], 'flags' => $header[1], 'hunks' => $hunks);
    }

    private function statsBetween(string $old, string $new): ?array
    {
        $hunks = WikiDiff::diff($old, $new);

        return $hunks === null ? null : WikiDiff::stats($hunks);
    }
}
