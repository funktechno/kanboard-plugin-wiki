<?php

namespace Kanboard\Plugin\Wiki\Controller;

use Exception;
use Kanboard\Controller\BaseController;
use Kanboard\Core\Controller\AccessForbiddenException;
use Kanboard\Model\UserMetadataModel;

/**
 * Class WikiAjaxController
 *
 * @package Kanboard\Controller
 * @author  lastlink
 */
class WikiAjaxController extends BaseController
{
    /**
     * reorder wiki page list by index and parent id
     * @throws \Kanboard\Core\Controller\AccessForbiddenException
     * @return void
     */
    public function reorder_by_index(){
        $this->checkReusableGETCSRFParam();
        $project_id = $this->request->getIntegerParam('project_id');

        if (!$project_id || !$this->request->isAjax()) {
            throw new AccessForbiddenException();
        }

        $values = $this->request->getJson();

        if(!isset($values['src_wiki_id']) || !isset($values['index'])) {
            throw new AccessForbiddenException();
        }

        try {
            $parent_id = (isset($values['parent_id']) && $values['parent_id'] != '' && $values['parent_id'] != '0') ? $values['parent_id'] : null;
            $result = $this->wikiModel->reorderPagesByIndex($project_id, $values['src_wiki_id'], $values['index'], $parent_id);

            if (!$result) {
                $this->response->status(400);
            } else {
                $this->response->status(200);
            }
        } catch (Exception $e) {
            $this->response->html('<div class="alert alert-error">'.$e->getMessage().'</div>');
        }

    }
    /**
     * reorder for wikipages using src and target page moving src before target
     */
    public function reorder()
    {
        $this->checkReusableGETCSRFParam();
        $project_id = $this->request->getIntegerParam('project_id');

        if (!$project_id || !$this->request->isAjax()) {
            throw new AccessForbiddenException();
        }

        $values = $this->request->getJson();

        if(!isset($values['src_wiki_id']) || !isset($values['target_wiki_id'])) {
            throw new AccessForbiddenException();
        }

        try {
            $result = $this->wikiModel->reorderPages($project_id, $values['src_wiki_id'], $values['target_wiki_id']);

            if (!$result) {
                $this->response->status(400);
            } else {
                $this->response->status(200);
            }
        } catch (Exception $e) {
            $this->response->html('<div class="alert alert-error">'.$e->getMessage().'</div>');
        }
    }
    
}
