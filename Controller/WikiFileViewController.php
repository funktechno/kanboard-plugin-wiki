<?php

namespace Kanboard\Plugin\Wiki\Controller;

use Kanboard\Controller\FileViewerController;
use Kanboard\Core\ObjectStorage\ObjectStorageException;

class WikiFileViewController extends FileViewerController
{
    /**
     * Show file content in a popover
     *
     * @access public
     */
    public function show()
    {
        $file = $this->wikiFileModel->getById($this->request->getIntegerParam('file_id'));

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
        $file = $this->wikiFileModel->getById($this->request->getIntegerParam('file_id'));
        $this->renderFileWithCache($file, $this->helper->file->getImageMimeType($file['name']));
    }

    /**
     * Display file in browser
     *
     * @access public
     */
    public function browser()
    {
        $file = $this->wikiFileModel->getById($this->request->getIntegerParam('file_id'));
        $this->renderFileWithCache($file, $this->helper->file->getBrowserViewType($file['name']));
    }

    /**
     * Display image thumbnail
     *
     * @access public
     */
    public function thumbnail()
    {
        $file = $this->wikiFileModel->getById($this->request->getIntegerParam('file_id'));
        $filename = $this->wikiFileModel->getThumbnailPath($file['path']);

        $this->response->withCache(5 * 86400, $file['etag']);
        $this->response->withContentType('image/png');

        if ($this->request->getHeader('If-None-Match') === '"'.$file['etag'].'"') {
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
            $file = $this->wikiFileModel->getById($this->request->getIntegerParam('file_id'));
            $this->response->withFileDownload($file['name']);
            $this->response->send();
            $this->objectStorage->output($file['path']);
        } catch (ObjectStorageException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
