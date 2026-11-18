<?php

namespace Kanboard\Plugin\Wiki\Controller;

use Kanboard\Controller\FileViewerController;
use Kanboard\Core\Controller\AccessForbiddenException;
use Kanboard\Core\Controller\PageNotFoundException;
use Kanboard\Core\ObjectStorage\ObjectStorageException;

class WikiFileViewController extends FileViewerController
{
    /**
     * Get the requested file, only if the user has access to its project
     *
     * @access private
     * @return array
     */
    private function getWikiFile()
    {
        $file = $this->wikiFileModel->getById($this->request->getIntegerParam('file_id'));

        if (empty($file)) {
            throw new PageNotFoundException();
        }

        $project_id = $this->wikiFileModel->getProjectId($file['id']);

        if (! $this->helper->user->hasProjectAccess('WikiFileViewController', 'show', $project_id)) {
            throw new AccessForbiddenException();
        }

        return $file;
    }

    /**
     * Show file content in a popover
     *
     * @access public
     */
    public function show()
    {
        $file = $this->getWikiFile();

        $this->response->html($this->template->render('file_viewer/show', array(
            'file' => $file,
            'type' => $this->helper->file->getPreviewType($file['name']),
            'content' => $this->getFileContent($file),
            'params' => array(
                'file_id' => $file['id'],
                'project_id' => $this->request->getIntegerParam('project_id'),
                'wikipage_id' => $file['wikipage_id'],
            )
        )));
    }

    /**
     * Display image
     *
     * @access public
     */
    public function image()
    {
        $file = $this->getWikiFile();
        $this->renderFileWithCache($file, $this->helper->file->getImageMimeType($file['name']));
    }

    /**
     * Display file in browser
     *
     * @access public
     */
    public function browser()
    {
        $file = $this->getWikiFile();
        $this->renderFileWithCache($file, $this->helper->file->getBrowserViewType($file['name']));
    }

    /**
     * Display image thumbnail
     *
     * @access public
     */
    public function thumbnail()
    {
        $file = $this->getWikiFile();
        $filename = $this->wikiFileModel->getThumbnailPath($file['path']);
        
        $etag = md5($filename);

        $this->response->withCache(5 * 86400, $etag);
        $this->response->withContentType('image/png');

        if ($this->request->getHeader('If-None-Match') === '"'.$etag.'"') {
            $this->response->status(304);
        } else {
            $this->response->send();

            try {
                $this->objectStorage->output($filename);
            } catch (ObjectStorageException $e) {
                $this->logger->error($e->getMessage());

                // Try to generate thumbnail on the fly for images uploaded before Kanboard < 1.0.19
                $data = $this->objectStorage->get($file['path']);
                $this->wikiFileModel->generateThumbnailFromData($file['path'], $data);
                $this->objectStorage->output($this->wikiFileModel->getThumbnailPath($file['path']));
            }
        }
    }

    /**
     * File download
     *
     * @access public
     */
    public function download()
    {
        try {
            $file = $this->getWikiFile();
            $this->response->withFileDownload($file['name']);
            $this->response->send();
            $this->objectStorage->output($file['path']);
        } catch (ObjectStorageException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
