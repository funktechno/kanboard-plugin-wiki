<?php

namespace Kanboard\Plugin\Wiki\Controller;

use Kanboard\Controller\BaseController;

/**
 * Wiki File Controller
 *
 * @package  Kanboard\Controller
 * @author   Frederic Guillot
 */
class WikiFileController extends BaseController
{
    /**
     * Screenshot
     *
     * @access public
     */
    public function screenshot()
    {
        $wiki = $this->wikiModel->getWiki();

        if ($this->request->isPost() && $this->wikiFileModel->uploadScreenshot($wiki['id'], $this->request->getValue('screenshot')) !== false) {
            $this->flash->success(t('Screenshot uploaded successfully.'));
            return $this->response->redirect($this->helper->url->to('WikiController', 'show', array('wiki_id' => $wiki['id'], 'project_id' => $wiki['project_id'])), true);
        }

        return $this->response->html($this->template->render('wiki:wiki_file/screenshot', array(
            'wiki' => $wiki,
        )));
    }

    /**
     * File upload form
     *
     * @access public
     */
    public function create()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $wiki = $this->wikiModel->getWiki();

        // $this->hourlyRate->getAllByProject($records[0]['project_id']);
        // $this->wikiFileModel->getAllByProject($records[0]['project_id']);

        // $this->wikiModel->getDailyWikiBreakdown($project['id']),

        $this->response->html($this->template->render('wiki:wiki_file/create', array(
            'wiki' => $wiki,
            'max_size' => get_upload_max_size(),
        )));
    }

    /**
     * File upload (save files)
     *
     * @access public
     */
    public function save()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $wiki = $this->wikiModel->getWiki();
        
        $result = $this->wikiFileModel->uploadFiles($wiki['id'], $this->request->getFileInfo('files'));

        if ($this->request->isAjax()) {
            if (!$result) {
                $this->response->json(array('message' => t('Unable to upload files, check the permissions of your data folder.')), 500);
            } else {
                $this->response->json(array('message' => 'OK'));
            }
        } else {
            if (!$result) {
                $this->flash->failure(t('Unable to upload files, check the permissions of your data folder.'));
            }
            
            $this->response->redirect($this->helper->url->to('WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $wiki['project_id'], 'wiki_id' => $wiki['id'])), true);

            // $this->response->redirect($this->helper->url->to('WikiController', 'show', array('wiki_id' => $wiki['id'], 'project_id' => $wiki['project_id'])), true);
        }
    }

    /**
     * Remove a file
     *
     * @access public
     */
    public function remove()
    {
        $this->checkCSRFParam();
        $wiki = $this->wikiModel->getWiki();
        $file = $this->wikiFileModel->getById($this->request->getIntegerParam('file_id'));

        if ($file['wikipage_id'] == $wiki['id'] && $this->wikiFileModel->remove($file['id'])) {
            $this->flash->success(t('File removed successfully.'));
        } else {
            $this->flash->failure(t('Unable to remove this file.'));
        }
            $this->response->redirect($this->helper->url->to('WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $wiki['project_id'], 'wiki_id' => $wiki['id'])), true);

    }

    /**
     * Confirmation dialog before removing a file
     *
     * @access public
     */
    public function confirm()
    {
        $wiki = $this->wikiModel->getWiki();
        $file = $this->wikiFileModel->getById($this->request->getIntegerParam('file_id'));

        $this->response->html($this->template->render('wiki:wiki_file/remove', array(
            'wiki' => $wiki,
            'file' => $file,
        )));
    }
}
