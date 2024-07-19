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
     * reorder for wikipages using src and target page moving src before target
     */
    public function reorder()
    {
        $this->checkReusableGETCSRFParam();
        $project_id = $this->request->getIntegerParam('project_id');

        if (! $project_id || ! $this->request->isAjax()) {
            throw new AccessForbiddenException();
        }

        $values = $this->request->getJson();

        if(!isset($values['src_wiki_id']) || !isset($values['target_wiki_id'])) {
            throw new AccessForbiddenException();
        }

        // if (! $this->helper->projectRole->canMoveTask($project_id, $values['src_column_id'], $values['dst_column_id'])) {
        //     throw new AccessForbiddenException(e("You don't have the permission to move this task"));
        // }

        try {
            $result = $this->wiki->reorderPages($project_id, $values['src_wiki_id'], $values['target_wiki_id']);

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
