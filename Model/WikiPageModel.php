<?php

declare(strict_types=1);

namespace Kanboard\Plugin\Wiki\Model;

use Kanboard\Core\Base;
use Kanboard\Plugin\Wiki\Domain\WikiPageException;
use PDO;

final class WikiPageModel extends Base
{
    public const PAGE_TABLE = 'wikipage';
    public const REVISION_TABLE = 'wikipage_editions';
    public const FILE_TABLE = 'wikipage_has_files';
    public const MAX_CONTENT_BYTES = 2097152;
    public const MAX_PAGE_LIMIT = 100;

    public function listPages(int $projectId, array $filters = array()): array
    {
        $limit = $this->normalizeLimit($filters['limit'] ?? 50);
        $offset = $this->normalizeOffset($filters['offset'] ?? 0);
        $where = array('project_id = :project_id');
        $parameters = array(':project_id' => $projectId);

        if (! (bool) ($filters['include_archived'] ?? false)) {
            $where[] = 'is_active = 1';
        }
        if (array_key_exists('parent_id', $filters)) {
            if ($filters['parent_id'] === null || $filters['parent_id'] === '' || (int) $filters['parent_id'] === 0) {
                $where[] = 'parent_id IS NULL';
            } else {
                $where[] = 'parent_id = :parent_id';
                $parameters[':parent_id'] = $this->positiveInteger($filters['parent_id'], 'parent_id');
            }
        }

        $whereSql = implode(' AND ', $where);
        $pdo = $this->connection();
        $countStatement = $pdo->prepare('SELECT COUNT(*) FROM '.self::PAGE_TABLE.' WHERE '.$whereSql);
        $this->bind($countStatement, $parameters);
        $countStatement->execute();
        $total = (int) $countStatement->fetchColumn();

        $statement = $pdo->prepare(
            'SELECT id, project_id, title, parent_id, ordercolumn, is_active, editions, current_edition, '.
            'creator_id, modifier_id, date_creation, date_modification '.
            'FROM '.self::PAGE_TABLE.' WHERE '.$whereSql.' '.
            'ORDER BY CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END, parent_id, ordercolumn, id '.
            'LIMIT :limit OFFSET :offset'
        );
        $this->bind($statement, $parameters);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return array(
            'pages' => array_map(array($this, 'summary'), $statement->fetchAll(PDO::FETCH_ASSOC)),
            'pagination' => array('limit' => $limit, 'offset' => $offset, 'total' => $total),
        );
    }

    public function getPage(int $pageId): ?array
    {
        $statement = $this->connection()->prepare(
            'SELECT id, project_id, title, content, parent_id, ordercolumn, is_active, editions, current_edition, '.
            'creator_id, modifier_id, date_creation, date_modification '.
            'FROM '.self::PAGE_TABLE.' WHERE id = :id'
        );
        $statement->bindValue(':id', $pageId, PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->detail($row) : null;
    }

    public function getPageDetail(int $pageId): ?array
    {
        $page = $this->getPage($pageId);
        if ($page === null) {
            return null;
        }

        $page['files'] = $this->files($pageId);
        $page['revisions'] = $this->revisions($pageId, 20, 0)['revisions'];

        return $page;
    }

    public function pageFiles(int $pageId): array
    {
        return $this->files($pageId);
    }

    public function getFileRecord(int $fileId): ?array
    {
        $statement = $this->connection()->prepare(
            'SELECT id, name, path, is_image, size, date, user_id, wikipage_id '.
            'FROM '.self::FILE_TABLE.' WHERE id = :id'
        );
        $statement->bindValue(':id', $fileId, PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (! is_array($row)) {
            return null;
        }
        $page = $this->getPage((int) $row['wikipage_id']);
        if ($page === null) {
            return null;
        }

        return array(
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'path' => (string) $row['path'],
            'image' => (int) $row['is_image'] === 1,
            'size' => (int) $row['size'],
            'createdAt' => (int) $row['date'],
            'createdBy' => (int) $row['user_id'],
            'pageId' => (int) $row['wikipage_id'],
            'projectId' => (int) $page['projectId'],
        );
    }

    public function safeFile(int $fileId): ?array
    {
        $file = $this->getFileRecord($fileId);
        if ($file === null) {
            return null;
        }
        unset($file['path'], $file['projectId']);

        return $file;
    }

    public function searchPages(int $projectId, string $query, int $limit = 20, int $offset = 0): array
    {
        $query = trim($query);
        if ($query === '') {
            throw new \InvalidArgumentException('query must not be empty.');
        }
        $limit = $this->normalizeLimit($limit);
        $offset = $this->normalizeOffset($offset);
        $pdo = $this->connection();
        $pattern = '%'.$query.'%';
        $where = 'project_id = :project_id AND is_active = 1 '.
            'AND (title LIKE :title_query OR content LIKE :content_query)';
        $count = $pdo->prepare('SELECT COUNT(*) FROM '.self::PAGE_TABLE.' WHERE '.$where);
        $count->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $count->bindValue(':title_query', $pattern, PDO::PARAM_STR);
        $count->bindValue(':content_query', $pattern, PDO::PARAM_STR);
        $count->execute();
        $statement = $pdo->prepare(
            'SELECT id, project_id, title, parent_id, ordercolumn, is_active, editions, current_edition, '.
            'creator_id, modifier_id, date_creation, date_modification '.
            'FROM '.self::PAGE_TABLE.' WHERE '.$where.' '.
            'ORDER BY id ASC LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $statement->bindValue(':title_query', $pattern, PDO::PARAM_STR);
        $statement->bindValue(':content_query', $pattern, PDO::PARAM_STR);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return array(
            'pages' => array_map(array($this, 'summary'), $statement->fetchAll(PDO::FETCH_ASSOC)),
            'pagination' => array('limit' => $limit, 'offset' => $offset, 'total' => (int) $count->fetchColumn()),
        );
    }

    public function createPage(int $projectId, array $page, int $actorId): array
    {
        $title = $this->validateTitle($page['title'] ?? null);
        $content = $this->validateContent($page['content'] ?? '');
        $parentId = $this->nullablePositiveInteger($page['parent_id'] ?? null, 'parent_id');
        $pageId = $this->transactional(fn (): int => $this->createPageRecord(
            $projectId,
            $title,
            $content,
            $parentId,
            $page['position'] ?? null,
            $actorId
        ));

        return $this->getPageDetail($pageId) ?? array();
    }

    public function updatePage(int $pageId, int $expectedRevision, array $patch, int $actorId): array
    {
        $allowed = array('title', 'content', 'parent_id', 'position');
        $unknown = array_diff(array_keys($patch), $allowed);
        if ($unknown !== array()) {
            throw new \InvalidArgumentException('Unknown fields: '.implode(', ', $unknown).'.');
        }
        $projectId = $this->requiredPage($pageId)['projectId'];
        $this->transactional(fn (): int => $this->updatePageRecord(
            $pageId,
            $projectId,
            $expectedRevision,
            $patch,
            $actorId
        ));

        return $this->getPageDetail($pageId) ?? array();
    }

    public function setArchived(int $pageId, int $expectedRevision, bool $archived, int $actorId): array
    {
        $projectId = $this->requiredPage($pageId)['projectId'];
        $this->transactional(fn (): int => $this->changeArchiveRecord(
            $pageId,
            $projectId,
            $expectedRevision,
            $archived,
            $actorId
        ));

        return $this->getPageDetail($pageId) ?? array();
    }

    public function restoreRevision(int $pageId, int $expectedRevision, int $targetRevision, int $actorId): array
    {
        $projectId = $this->requiredPage($pageId)['projectId'];
        $this->transactional(fn (): int => $this->restoreRevisionRecord(
            $pageId,
            $projectId,
            $expectedRevision,
            $targetRevision,
            $actorId
        ));

        return $this->getPageDetail($pageId) ?? array();
    }

    private function createPageRecord(
        int $projectId,
        string $title,
        string $content,
        ?int $parentId,
        mixed $requestedPosition,
        int $actorId
    ): int {
        $this->lockProjectPages($projectId);
        $this->assertParent($projectId, $parentId);
        $position = $this->normalizePosition(
            $requestedPosition,
            $this->siblingCount($projectId, $parentId) + 1
        );
        $this->shiftSiblings($projectId, $parentId, $position, 1);

        $pdo = $this->connection();
        $date = date('Y-m-d');
        $returning = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql' ? ' RETURNING id' : '';
        $statement = $pdo->prepare(
            'INSERT INTO '.self::PAGE_TABLE.' '.
            '(project_id, title, content, is_active, creator_id, modifier_id, date_creation, date_modification, '.
            'ordercolumn, editions, current_edition, parent_id) '.
            'VALUES (:project_id, :title, :content, 1, :creator_id, :modifier_id, :created_at, :updated_at, '.
            ':position, 1, 1, :parent_id)'.$returning
        );
        $statement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $statement->bindValue(':title', $title, PDO::PARAM_STR);
        $statement->bindValue(':content', $content, PDO::PARAM_STR);
        $statement->bindValue(':creator_id', $actorId, PDO::PARAM_INT);
        $statement->bindValue(':modifier_id', $actorId, PDO::PARAM_INT);
        $statement->bindValue(':created_at', $date, PDO::PARAM_STR);
        $statement->bindValue(':updated_at', $date, PDO::PARAM_STR);
        $statement->bindValue(':position', $position, PDO::PARAM_INT);
        $statement->bindValue(':parent_id', $parentId, $parentId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->execute();
        $pageId = $returning === '' ? (int) $pdo->lastInsertId() : (int) $statement->fetchColumn();
        $this->persistRevision($pageId, 1, $title, $content, $actorId, $date);

        return $pageId;
    }

    private function updatePageRecord(
        int $pageId,
        int $projectId,
        int $expectedRevision,
        array $patch,
        int $actorId
    ): int
    {
        $current = $this->lockedPage($pageId, $projectId);
        $this->assertExpectedRevision($current, $expectedRevision);
        $change = $this->preparePageUpdate($pageId, $current, $patch);
        if (! $change['contentChanged'] && ! $change['moved']) {
            return $pageId;
        }

        $revision = $expectedRevision + 1;
        $date = date('Y-m-d');
        $this->writePageUpdate($pageId, $expectedRevision, $revision, $change, $current, $actorId, $date);
        if ($change['moved']) {
            $this->normalizeSiblingOrder($current['projectId'], $current['parentId'], $pageId);
            $this->insertIntoSiblingOrder(
                $current['projectId'],
                $change['parentId'],
                $pageId,
                $change['position']
            );
        }
        if ($change['contentChanged']) {
            $this->persistRevision($pageId, $revision, $change['title'], $change['content'], $actorId, $date);
        }

        return $pageId;
    }

    private function preparePageUpdate(int $pageId, array $current, array $patch): array
    {
        $title = array_key_exists('title', $patch) ? $this->validateTitle($patch['title']) : $current['title'];
        $content = array_key_exists('content', $patch) ? $this->validateContent($patch['content']) : $current['content'];
        $parentId = array_key_exists('parent_id', $patch)
            ? $this->nullablePositiveInteger($patch['parent_id'], 'parent_id')
            : $current['parentId'];
        $this->assertParent($current['projectId'], $parentId);
        $this->assertAcyclicParent($pageId, $current['projectId'], $parentId);
        $targetSiblings = $this->siblingIds($current['projectId'], $parentId, $pageId);
        $position = array_key_exists('position', $patch)
            ? $this->normalizePosition($patch['position'], count($targetSiblings) + 1)
            : ($parentId === $current['parentId'] ? $current['position'] : count($targetSiblings) + 1);

        return array(
            'title' => $title,
            'content' => $content,
            'parentId' => $parentId,
            'position' => $position,
            'contentChanged' => $title !== $current['title'] || $content !== $current['content'],
            'moved' => $parentId !== $current['parentId'] || $position !== $current['position'],
        );
    }

    private function writePageUpdate(
        int $pageId,
        int $expectedRevision,
        int $revision,
        array $change,
        array $current,
        int $actorId,
        string $date
    ): void {
        $statement = $this->connection()->prepare(
            'UPDATE '.self::PAGE_TABLE.' SET title = :title, content = :content, parent_id = :parent_id, '.
            'ordercolumn = :position, editions = :revision, current_edition = :current_revision, '.
            'modifier_id = :modifier_id, date_modification = :updated_at '.
            'WHERE id = :id AND editions = :expected_revision'
        );
        $statement->bindValue(':title', $change['title'], PDO::PARAM_STR);
        $statement->bindValue(':content', $change['content'], PDO::PARAM_STR);
        $statement->bindValue(
            ':parent_id',
            $change['parentId'],
            $change['parentId'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT
        );
        $statement->bindValue(':position', $change['position'], PDO::PARAM_INT);
        $statement->bindValue(':revision', $revision, PDO::PARAM_INT);
        $statement->bindValue(
            ':current_revision',
            $change['contentChanged'] ? $revision : $current['currentRevision'],
            PDO::PARAM_INT
        );
        $statement->bindValue(':modifier_id', $actorId, PDO::PARAM_INT);
        $statement->bindValue(':updated_at', $date, PDO::PARAM_STR);
        $statement->bindValue(':id', $pageId, PDO::PARAM_INT);
        $statement->bindValue(':expected_revision', $expectedRevision, PDO::PARAM_INT);
        $statement->execute();
        $this->assertCasUpdated($statement, $pageId);
    }

    private function changeArchiveRecord(
        int $pageId,
        int $projectId,
        int $expectedRevision,
        bool $archived,
        int $actorId
    ): int {
        $current = $this->lockedPage($pageId, $projectId);
        $this->assertExpectedRevision($current, $expectedRevision);
        if ($archived && $this->activeChildCount($current['projectId'], $pageId) > 0) {
            throw new WikiPageException(
                'validation_failed',
                'Move or archive the child pages before archiving this page.',
                array('field' => 'children')
            );
        }
        if (! $archived) {
            $this->assertParent($current['projectId'], $current['parentId']);
        }
        if ($current['archived'] === $archived) {
            return $pageId;
        }

        $statement = $this->connection()->prepare(
            'UPDATE '.self::PAGE_TABLE.' SET is_active = :active, editions = :revision, '.
            'modifier_id = :modifier_id, date_modification = :updated_at '.
            'WHERE id = :id AND editions = :expected_revision'
        );
        $statement->bindValue(':active', $archived ? 0 : 1, PDO::PARAM_INT);
        $statement->bindValue(':revision', $expectedRevision + 1, PDO::PARAM_INT);
        $statement->bindValue(':modifier_id', $actorId, PDO::PARAM_INT);
        $statement->bindValue(':updated_at', date('Y-m-d'), PDO::PARAM_STR);
        $statement->bindValue(':id', $pageId, PDO::PARAM_INT);
        $statement->bindValue(':expected_revision', $expectedRevision, PDO::PARAM_INT);
        $statement->execute();
        $this->assertCasUpdated($statement, $pageId);

        return $pageId;
    }

    private function restoreRevisionRecord(
        int $pageId,
        int $projectId,
        int $expectedRevision,
        int $targetRevision,
        int $actorId
    ): int {
        $current = $this->lockedPage($pageId, $projectId);
        if ($current['archived']) {
            throw new WikiPageException('archived', 'Restore the wiki page before restoring a revision.');
        }
        $this->assertExpectedRevision($current, $expectedRevision);
        $target = $this->revision($pageId, $targetRevision);
        if ($target === null) {
            throw new WikiPageException('not_found', 'wiki page revision not found.');
        }
        $revision = $expectedRevision + 1;
        $date = date('Y-m-d');
        $this->writeRevisionRestore($pageId, $expectedRevision, $revision, $target, $actorId, $date);
        $this->persistRevision($pageId, $revision, $target['title'], $target['content'], $actorId, $date);

        return $pageId;
    }

    private function writeRevisionRestore(
        int $pageId,
        int $expectedRevision,
        int $revision,
        array $target,
        int $actorId,
        string $date
    ): void {
        $statement = $this->connection()->prepare(
            'UPDATE '.self::PAGE_TABLE.' SET title = :title, content = :content, editions = :revision, '.
            'current_edition = :revision, modifier_id = :modifier_id, date_modification = :updated_at '.
            'WHERE id = :id AND editions = :expected_revision'
        );
        $statement->bindValue(':title', $target['title'], PDO::PARAM_STR);
        $statement->bindValue(':content', $target['content'], PDO::PARAM_STR);
        $statement->bindValue(':revision', $revision, PDO::PARAM_INT);
        $statement->bindValue(':modifier_id', $actorId, PDO::PARAM_INT);
        $statement->bindValue(':updated_at', $date, PDO::PARAM_STR);
        $statement->bindValue(':id', $pageId, PDO::PARAM_INT);
        $statement->bindValue(':expected_revision', $expectedRevision, PDO::PARAM_INT);
        $statement->execute();
        $this->assertCasUpdated($statement, $pageId);
    }

    private function lockedPage(int $pageId, int $projectId): array
    {
        $this->lockProjectPages($projectId);

        return $this->requiredPage($pageId);
    }

    private function requiredPage(int $pageId): array
    {
        $page = $this->getPage($pageId);
        if ($page === null) {
            throw new WikiPageException('not_found', 'wiki page not found.');
        }

        return $page;
    }

    private function assertExpectedRevision(array $page, int $expectedRevision): void
    {
        if ($page['revision'] !== $expectedRevision) {
            throw $this->revisionConflict($page['revision']);
        }
    }

    private function assertCasUpdated(\PDOStatement $statement, int $pageId): void
    {
        if ($statement->rowCount() !== 1) {
            $latest = $this->getPage($pageId);
            throw $this->revisionConflict((int) ($latest['revision'] ?? 0));
        }
    }

    private function revisionConflict(int $currentRevision): WikiPageException
    {
        return new WikiPageException(
            'revision_conflict',
            'The wiki page has changed. Read the latest revision and retry.',
            array('currentRevision' => $currentRevision)
        );
    }

    private function transactional(callable $operation): mixed
    {
        $pdo = $this->connection();
        $sqlite = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        if ($sqlite) {
            $pdo->exec('BEGIN IMMEDIATE');
        } else {
            $pdo->beginTransaction();
        }
        try {
            $result = $operation();
            if ($sqlite) {
                $pdo->exec('COMMIT');
            } else {
                $pdo->commit();
            }

            return $result;
        } catch (\Throwable $exception) {
            if ($sqlite) {
                try {
                    $pdo->exec('ROLLBACK');
                } catch (\Throwable) {
                }
            } elseif ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function summary(array $row): array
    {
        return array(
            'id' => (int) $row['id'],
            'projectId' => (int) $row['project_id'],
            'title' => (string) $row['title'],
            'parentId' => $row['parent_id'] === null ? null : (int) $row['parent_id'],
            'position' => (int) $row['ordercolumn'],
            'archived' => (int) $row['is_active'] !== 1,
            'revision' => (int) $row['editions'],
            'currentRevision' => (int) $row['current_edition'],
            'createdBy' => (int) $row['creator_id'],
            'updatedBy' => (int) $row['modifier_id'],
            'createdAt' => $row['date_creation'],
            'updatedAt' => $row['date_modification'],
        );
    }

    private function detail(array $row): array
    {
        $page = $this->summary($row);
        $page['content'] = (string) $row['content'];

        return $page;
    }

    private function files(int $pageId): array
    {
        $statement = $this->connection()->prepare(
            'SELECT id, name, is_image, size, date, user_id '.
            'FROM '.self::FILE_TABLE.' WHERE wikipage_id = :page_id ORDER BY id'
        );
        $statement->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $statement->execute();

        return array_map(static function (array $row): array {
            return array(
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'image' => (int) $row['is_image'] === 1,
                'size' => (int) $row['size'],
                'createdAt' => (int) $row['date'],
                'createdBy' => (int) $row['user_id'],
            );
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function revisions(int $pageId, int $limit = 20, int $offset = 0): array
    {
        $limit = $this->normalizeLimit($limit);
        $offset = $this->normalizeOffset($offset);
        $pdo = $this->connection();
        $count = $pdo->prepare('SELECT COUNT(*) FROM '.self::REVISION_TABLE.' WHERE wikipage_id = :page_id');
        $count->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $count->execute();
        $statement = $pdo->prepare(
            'SELECT edition, title, creator_id, date_creation '.
            'FROM '.self::REVISION_TABLE.' WHERE wikipage_id = :page_id '.
            'ORDER BY edition DESC LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return array(
            'revisions' => array_map(static function (array $row): array {
                return array(
                    'revision' => (int) $row['edition'],
                    'title' => (string) $row['title'],
                    'createdBy' => (int) $row['creator_id'],
                    'createdAt' => $row['date_creation'],
                );
            }, $statement->fetchAll(PDO::FETCH_ASSOC)),
            'pagination' => array('limit' => $limit, 'offset' => $offset, 'total' => (int) $count->fetchColumn()),
        );
    }

    private function revision(int $pageId, int $revision): ?array
    {
        $statement = $this->connection()->prepare(
            'SELECT edition, title, content, creator_id, date_creation '.
            'FROM '.self::REVISION_TABLE.' WHERE wikipage_id = :page_id AND edition = :revision'
        );
        $statement->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $statement->bindValue(':revision', $revision, PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    private function normalizeLimit(mixed $value): int
    {
        $limit = $this->positiveInteger($value, 'limit');
        if ($limit > self::MAX_PAGE_LIMIT) {
            throw new \InvalidArgumentException('limit must not exceed '.self::MAX_PAGE_LIMIT.'.');
        }

        return $limit;
    }

    private function normalizeOffset(mixed $value): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < 0) {
            throw new \InvalidArgumentException('offset must be a non-negative integer.');
        }

        return (int) $value;
    }

    private function validateTitle(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new \InvalidArgumentException('title is required.');
        }
        $title = trim($value);
        $length = function_exists('mb_strlen') ? mb_strlen($title, 'UTF-8') : strlen($title);
        if ($length > 255) {
            throw new \InvalidArgumentException('title must not exceed 255 characters.');
        }

        return $title;
    }

    private function validateContent(mixed $value): string
    {
        if (! is_string($value)) {
            throw new \InvalidArgumentException('content must be a string.');
        }
        if (strlen($value) > self::MAX_CONTENT_BYTES) {
            throw new \InvalidArgumentException('content exceeds the 2 MiB limit.');
        }

        return $value;
    }

    private function nullablePositiveInteger(mixed $value, string $field): ?int
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return null;
        }

        return $this->positiveInteger($value, $field);
    }

    private function normalizePosition(mixed $value, int $maximum): int
    {
        if ($value === null || $value === '') {
            return $maximum;
        }
        $position = $this->positiveInteger($value, 'position');
        if ($position > $maximum) {
            throw new \InvalidArgumentException('position must not exceed '.($maximum).'.');
        }

        return $position;
    }

    private function assertParent(int $projectId, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }
        $parent = $this->getPage($parentId);
        if ($parent === null || $parent['projectId'] !== $projectId || $parent['archived']) {
            throw new WikiPageException('invalid_parent', 'The parent page is not available in this project.');
        }
    }

    private function assertAcyclicParent(int $pageId, int $projectId, ?int $parentId): void
    {
        $seen = array();
        $cursor = $parentId;
        while ($cursor !== null) {
            if ($cursor === $pageId || isset($seen[$cursor])) {
                throw new WikiPageException('hierarchy_cycle', 'A wiki page cannot be moved into its own subtree.');
            }
            $seen[$cursor] = true;
            $parent = $this->getPage($cursor);
            if ($parent === null || $parent['projectId'] !== $projectId) {
                throw new WikiPageException('invalid_parent', 'The parent page is not available in this project.');
            }
            $cursor = $parent['parentId'];
        }
    }

    private function siblingIds(int $projectId, ?int $parentId, int $excludePageId = 0): array
    {
        $sql = 'SELECT id FROM '.self::PAGE_TABLE.' WHERE project_id = :project_id AND '.
            ($parentId === null ? 'parent_id IS NULL' : 'parent_id = :parent_id');
        if ($excludePageId > 0) {
            $sql .= ' AND id <> :exclude_id';
        }
        $sql .= ' ORDER BY ordercolumn, id';
        $statement = $this->connection()->prepare($sql);
        $statement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        if ($parentId !== null) {
            $statement->bindValue(':parent_id', $parentId, PDO::PARAM_INT);
        }
        if ($excludePageId > 0) {
            $statement->bindValue(':exclude_id', $excludePageId, PDO::PARAM_INT);
        }
        $statement->execute();

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    private function normalizeSiblingOrder(int $projectId, ?int $parentId, int $excludePageId = 0): void
    {
        $this->writeSiblingPositions($this->siblingIds($projectId, $parentId, $excludePageId));
    }

    private function insertIntoSiblingOrder(int $projectId, ?int $parentId, int $pageId, int $position): void
    {
        $ids = $this->siblingIds($projectId, $parentId, $pageId);
        array_splice($ids, $position - 1, 0, array($pageId));
        $this->writeSiblingPositions($ids);
    }

    private function writeSiblingPositions(array $pageIds): void
    {
        $statement = $this->connection()->prepare(
            'UPDATE '.self::PAGE_TABLE.' SET ordercolumn = :position WHERE id = :id'
        );
        foreach ($pageIds as $index => $pageId) {
            $statement->bindValue(':position', $index + 1, PDO::PARAM_INT);
            $statement->bindValue(':id', $pageId, PDO::PARAM_INT);
            $statement->execute();
        }
    }

    private function lockProjectPages(int $projectId): void
    {
        $pdo = $this->connection();
        if (! in_array($pdo->getAttribute(PDO::ATTR_DRIVER_NAME), array('mysql', 'pgsql'), true)) {
            return;
        }
        $statement = $pdo->prepare(
            'SELECT id FROM projects WHERE id = :project_id FOR UPDATE'
        );
        $statement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $statement->execute();
        if ($statement->fetchColumn() === false) {
            throw new WikiPageException('not_found', 'Project not found.');
        }
    }

    private function siblingCount(int $projectId, ?int $parentId): int
    {
        $sql = 'SELECT COUNT(*) FROM '.self::PAGE_TABLE.' WHERE project_id = :project_id AND '.
            ($parentId === null ? 'parent_id IS NULL' : 'parent_id = :parent_id');
        $statement = $this->connection()->prepare($sql);
        $statement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        if ($parentId !== null) {
            $statement->bindValue(':parent_id', $parentId, PDO::PARAM_INT);
        }
        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    private function activeChildCount(int $projectId, int $parentId): int
    {
        $statement = $this->connection()->prepare(
            'SELECT COUNT(*) FROM '.self::PAGE_TABLE.' '.
            'WHERE project_id = :project_id AND parent_id = :parent_id AND is_active = 1'
        );
        $statement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $statement->bindValue(':parent_id', $parentId, PDO::PARAM_INT);
        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    private function shiftSiblings(int $projectId, ?int $parentId, int $fromPosition, int $delta): void
    {
        $sql = 'UPDATE '.self::PAGE_TABLE.' SET ordercolumn = ordercolumn + :delta '.
            'WHERE project_id = :project_id AND ordercolumn >= :position AND '.
            ($parentId === null ? 'parent_id IS NULL' : 'parent_id = :parent_id');
        $statement = $this->connection()->prepare($sql);
        $statement->bindValue(':delta', $delta, PDO::PARAM_INT);
        $statement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $statement->bindValue(':position', $fromPosition, PDO::PARAM_INT);
        if ($parentId !== null) {
            $statement->bindValue(':parent_id', $parentId, PDO::PARAM_INT);
        }
        $statement->execute();
    }

    private function persistRevision(
        int $pageId,
        int $revision,
        string $title,
        string $content,
        int $actorId,
        string $date
    ): void {
        if ((string) $this->configModel->get('persistEditions') !== '1') {
            return;
        }
        $statement = $this->connection()->prepare(
            'INSERT INTO '.self::REVISION_TABLE.' '.
            '(edition, title, content, creator_id, date_creation, wikipage_id) '.
            'VALUES (:revision, :title, :content, :creator_id, :created_at, :page_id)'
        );
        $statement->bindValue(':revision', $revision, PDO::PARAM_INT);
        $statement->bindValue(':title', $title, PDO::PARAM_STR);
        $statement->bindValue(':content', $content, PDO::PARAM_STR);
        $statement->bindValue(':creator_id', $actorId, PDO::PARAM_INT);
        $statement->bindValue(':created_at', $date, PDO::PARAM_STR);
        $statement->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $statement->execute();
    }

    private function positiveInteger(mixed $value, string $field): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < 1) {
            throw new \InvalidArgumentException($field.' must be a positive integer.');
        }

        return (int) $value;
    }

    private function connection(): PDO
    {
        return $this->db->getConnection();
    }

    private function bind(\PDOStatement $statement, array $parameters): void
    {
        foreach ($parameters as $name => $value) {
            $statement->bindValue($name, $value, PDO::PARAM_INT);
        }
    }
}
