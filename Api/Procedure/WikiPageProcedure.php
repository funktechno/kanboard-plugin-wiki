<?php

declare(strict_types=1);

namespace Kanboard\Plugin\Wiki\Api\Procedure;

use JsonRPC\Exception\AccessDeniedException;
use Kanboard\Api\Authorization\ProjectAuthorization;
use Kanboard\Api\Procedure\BaseProcedure;
use Kanboard\Core\Security\Role;
use Kanboard\Plugin\Wiki\Domain\WikiPageException;

final class WikiPageProcedure extends BaseProcedure
{
    private const MAX_API_DOWNLOAD_BYTES = 10485760;

    public function getWikiPages($project_id, array $filters = array()): array
    {
        $projectId = (int) $project_id;
        if ($projectId < 1 || ! $this->projectExists($projectId)) {
            return $this->failure('not_found', 'Project not found.');
        }
        $this->authorizeProject($projectId, __FUNCTION__);

        try {
            return $this->success($this->wikiPageModel->listPages($projectId, $filters));
        } catch (\InvalidArgumentException $exception) {
            return $this->failure(
                'validation_failed',
                'The wiki page request is invalid.',
                array('errors' => array($exception->getMessage()))
            );
        }
    }

    public function getWikiPage($page_id): array
    {
        $page = $this->authorizedPageDetail((int) $page_id, __FUNCTION__);
        if ($page === null) {
            return $this->failure('not_found', 'wiki page not found.');
        }

        return $this->success($page);
    }

    public function searchWikiPages($project_id, $query, $limit = 20, $offset = 0): array
    {
        $projectId = (int) $project_id;
        if ($projectId < 1 || ! $this->projectExists($projectId)) {
            return $this->failure('not_found', 'Project not found.');
        }
        $this->authorizeProject($projectId, __FUNCTION__);

        try {
            return $this->success($this->wikiPageModel->searchPages(
                $projectId,
                (string) $query,
                (int) $limit,
                (int) $offset
            ));
        } catch (\InvalidArgumentException $exception) {
            return $this->validationFailure($exception);
        }
    }

    public function createWikiPage($project_id, array $page): array
    {
        $projectId = (int) $project_id;
        if ($projectId < 1 || ! $this->projectExists($projectId)) {
            return $this->failure('not_found', 'Project not found.');
        }
        $this->authorizeProject($projectId, __FUNCTION__);

        try {
            return $this->success($this->wikiPageModel->createPage(
                $projectId,
                $page,
                (int) $this->userSession->getId()
            ));
        } catch (WikiPageException $exception) {
            return $this->failure($exception->errorCode(), $exception->getMessage(), $exception->errorDetails());
        } catch (\InvalidArgumentException $exception) {
            return $this->validationFailure($exception);
        }
    }

    public function updateWikiPage($page_id, $expected_revision, array $patch): array
    {
        $page = $this->authorizedPage((int) $page_id, __FUNCTION__);
        if ($page === null) {
            return $this->failure('not_found', 'wiki page not found.');
        }

        try {
            return $this->success($this->wikiPageModel->updatePage(
                (int) $page_id,
                (int) $expected_revision,
                $patch,
                (int) $this->userSession->getId()
            ));
        } catch (WikiPageException $exception) {
            return $this->failure($exception->errorCode(), $exception->getMessage(), $exception->errorDetails());
        } catch (\InvalidArgumentException $exception) {
            return $this->validationFailure($exception);
        }
    }

    public function archiveWikiPage($page_id, $expected_revision): array
    {
        return $this->changeArchiveState((int) $page_id, (int) $expected_revision, true, __FUNCTION__);
    }

    public function restoreWikiPage($page_id, $expected_revision): array
    {
        return $this->changeArchiveState((int) $page_id, (int) $expected_revision, false, __FUNCTION__);
    }

    private function changeArchiveState(int $pageId, int $expectedRevision, bool $archived, string $method): array
    {
        $page = $this->authorizedPage($pageId, $method);
        if ($page === null) {
            return $this->failure('not_found', 'wiki page not found.');
        }

        try {
            return $this->success($this->wikiPageModel->setArchived(
                $pageId,
                $expectedRevision,
                $archived,
                (int) $this->userSession->getId()
            ));
        } catch (WikiPageException $exception) {
            return $this->failure($exception->errorCode(), $exception->getMessage(), $exception->errorDetails());
        }
    }

    public function getWikiPageRevisions($page_id, $limit = 20, $offset = 0): array
    {
        $page = $this->authorizedPage((int) $page_id, __FUNCTION__);
        if ($page === null) {
            return $this->failure('not_found', 'wiki page not found.');
        }

        try {
            return $this->success($this->wikiPageModel->revisions(
                (int) $page_id,
                (int) $limit,
                (int) $offset
            ));
        } catch (\InvalidArgumentException $exception) {
            return $this->validationFailure($exception);
        }
    }

    public function restoreWikiPageRevision($page_id, $expected_revision, $revision): array
    {
        $page = $this->authorizedPage((int) $page_id, __FUNCTION__);
        if ($page === null) {
            return $this->failure('not_found', 'wiki page not found.');
        }

        try {
            return $this->success($this->wikiPageModel->restoreRevision(
                (int) $page_id,
                (int) $expected_revision,
                (int) $revision,
                (int) $this->userSession->getId()
            ));
        } catch (WikiPageException $exception) {
            return $this->failure($exception->errorCode(), $exception->getMessage(), $exception->errorDetails());
        }
    }

    public function getWikiPageFiles($page_id): array
    {
        $page = $this->authorizedPage((int) $page_id, __FUNCTION__);
        if ($page === null) {
            return $this->failure('not_found', 'wiki page not found.');
        }

        return $this->success(array('files' => $this->wikiPageModel->pageFiles((int) $page_id)));
    }

    public function createWikiPageFile($page_id, $name, $blob): array
    {
        $page = $this->authorizedPage((int) $page_id, __FUNCTION__);
        if ($page === null) {
            return $this->failure('not_found', 'wiki page not found.');
        }

        try {
            $filename = $this->validateFilename($name);
            $content = $this->decodeBlob($blob);
            $maxSize = function_exists('get_upload_max_size') ? (int) get_upload_max_size() : self::MAX_API_DOWNLOAD_BYTES;
            if (strlen($content) > $maxSize) {
                return $this->failure('file_too_large', 'The attachment exceeds the configured upload limit.');
            }
            $fileId = $this->wikiFileModel->uploadContent((int) $page_id, $filename, (string) $blob, true);
            if (! is_int($fileId) || $fileId < 1) {
                return $this->failure('validation_failed', 'Unable to store the wiki attachment.');
            }
            $file = $this->wikiPageModel->safeFile($fileId);

            return $file === null
                ? $this->failure('not_found', 'wiki attachment not found after upload.')
                : $this->success($file);
        } catch (\InvalidArgumentException $exception) {
            return $this->validationFailure($exception);
        }
    }

    public function downloadWikiPageFile($file_id): array
    {
        $file = $this->authorizedFile((int) $file_id, __FUNCTION__);
        if ($file === null) {
            return $this->failure('not_found', 'wiki attachment not found.');
        }
        if ($file['size'] > self::MAX_API_DOWNLOAD_BYTES) {
            return $this->failure(
                'file_too_large',
                'Attachments larger than 10 MiB must be downloaded from the web interface.'
            );
        }

        try {
            $content = $this->objectStorage->get($file['path']);
        } catch (\Throwable $exception) {
            return $this->failure('not_found', 'wiki attachment content not found.');
        }
        if (strlen($content) > self::MAX_API_DOWNLOAD_BYTES) {
            return $this->failure(
                'file_too_large',
                'Attachments larger than 10 MiB must be downloaded from the web interface.'
            );
        }
        $mime = 'application/octet-stream';
        if (class_exists(\finfo::class)) {
            $detector = new \finfo(FILEINFO_MIME_TYPE);
            $detected = $detector->buffer($content);
            if (is_string($detected) && $detected !== '') {
                $mime = $detected;
            }
        }

        return $this->success(array(
            'id' => $file['id'],
            'pageId' => $file['pageId'],
            'name' => $file['name'],
            'size' => strlen($content),
            'mimeType' => $mime,
            'blob' => base64_encode($content),
        ));
    }

    public function removeWikiPageFile($file_id): array
    {
        $file = $this->authorizedFile((int) $file_id, __FUNCTION__);
        if ($file === null) {
            return $this->failure('not_found', 'wiki attachment not found.');
        }
        if (! $this->wikiFileModel->remove((int) $file_id)) {
            return $this->failure('validation_failed', 'Unable to remove the wiki attachment.');
        }

        return $this->success(array('removed' => true, 'id' => (int) $file_id));
    }

    private function projectExists(int $projectId): bool
    {
        $project = $this->projectModel->getById($projectId);

        return is_array($project) && ! empty($project['id']);
    }

    private function authorizeProject(int $projectId, string $method): void
    {
        if ($this->userSession->isLogged()) {
            $applicationRole = $this->userSession->getRole();
            if ($applicationRole === Role::APP_ADMIN) {
                return;
            }
        }

        ProjectAuthorization::getInstance($this->container)->check(
            $this->getClassName(),
            $method,
            $projectId
        );
    }

    private function authorizedPage(int $pageId, string $method): ?array
    {
        $page = $this->wikiPageModel->getPage($pageId);
        if ($page === null || ! $this->canAccessResourceProject((int) $page['projectId'], $method)) {
            return null;
        }

        return $page;
    }

    private function authorizedPageDetail(int $pageId, string $method): ?array
    {
        $page = $this->authorizedPage($pageId, $method);

        return $page === null ? null : $this->wikiPageModel->getPageDetail($pageId);
    }

    private function authorizedFile(int $fileId, string $method): ?array
    {
        $file = $this->wikiPageModel->getFileRecord($fileId);
        if ($file === null || ! $this->canAccessResourceProject((int) $file['projectId'], $method)) {
            return null;
        }

        return $file;
    }

    private function canAccessResourceProject(int $projectId, string $method): bool
    {
        try {
            $this->authorizeProject($projectId, $method);

            return true;
        } catch (AccessDeniedException $exception) {
            return false;
        }
    }

    private function success(array $data): array
    {
        return array('ok' => true, 'data' => $data);
    }

    private function failure(string $code, string $message, array $details = array()): array
    {
        $error = array('code' => $code, 'message' => $message);
        if ($details !== array()) {
            $error['details'] = $details;
        }

        return array('ok' => false, 'error' => $error);
    }

    private function validationFailure(\InvalidArgumentException $exception): array
    {
        return $this->failure(
            'validation_failed',
            'The wiki page request is invalid.',
            array('errors' => array($exception->getMessage()))
        );
    }

    private function validateFilename(mixed $name): string
    {
        if (! is_string($name) || strpos($name, "\0") !== false) {
            throw new \InvalidArgumentException('name must be a valid filename.');
        }
        $filename = basename(str_replace('\\', '/', trim($name)));
        if ($filename === '' || $filename === '.' || $filename === '..') {
            throw new \InvalidArgumentException('name must be a valid filename.');
        }
        $length = function_exists('mb_strlen') ? mb_strlen($filename, 'UTF-8') : strlen($filename);
        if ($length > 50) {
            throw new \InvalidArgumentException('name must not exceed 50 characters.');
        }

        return $filename;
    }

    private function decodeBlob(mixed $blob): string
    {
        if (! is_string($blob) || $blob === '') {
            throw new \InvalidArgumentException('blob must be a non-empty Base64 string.');
        }
        $content = base64_decode($blob, true);
        if (! is_string($content) || $content === '') {
            throw new \InvalidArgumentException('blob must be valid Base64.');
        }

        return $content;
    }
}
