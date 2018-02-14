<?php

namespace Kanboard\Plugin\Wiki\Controller;

use Kanboard\Controller\BaseController;

class WikiLineController extends BaseController
{
    public function show()
    {
        $project = $this->getProject();

        if (empty($values)) {
            $values['date'] = date('Y-m-d');
        }

        $this->response->html($this->helper->layout->project('wiki:wiki_lines/show', array(
            'lines' => $this->wiki->getAll($project['id']),
            'project' => $project,
            'title' => t('Wiki lines')
        ), 'wiki:wiki/sidebar'));
    }

    public function create(array $values = array(), array $errors = array())
    {
        $project = $this->getProject();

        if (empty($values)) {
            $values['date'] = date('Y-m-d');
        }

        $this->response->html($this->helper->layout->project('wiki:wiki_lines/create', array(
            'values' => $values + array('project_id' => $project['id']),
            'errors' => $errors,
            'project' => $project,
            'title' => t('Wiki lines')
        ), 'wiki:wiki/sidebar'));
    }

    /**
     * Validate and save a new wiki
     *
     * @access public
     */
    public function save()
    {
        $project = $this->getProject();

        $values = $this->request->getValues();
        list($valid, $errors) = $this->wiki->validateCreation($values);

        if ($valid) {

            if ($this->wiki->create($values['project_id'], $values['amount'], $values['comment'], $values['date'])) {
                $this->flash->success(t('The wiki line have been created successfully.'));
                $this->response->redirect($this->helper->url->to('WikiLineController', 'create', array('plugin' => 'wiki', 'project_id' => $project['id'])), true);
                return;
            } else {
                $this->flash->failure(t('Unable to create the wiki line.'));
            }
        }

        $this->create($values, $errors);
    }

    /**
     * Confirmation dialog before removing a wiki
     *
     * @access public
     */
    public function confirm()
    {
        $project = $this->getProject();

        $this->response->html($this->template->render('wiki:wiki_lines/remove', array(
            'project' => $project,
            'wiki_id' => $this->request->getIntegerParam('wiki_id'),
        )));
    }

    /**
     * Remove a wiki
     *
     * @access public
     */
    public function remove()
    {
        $this->checkCSRFParam();
        $project = $this->getProject();

        if ($this->wiki->remove($this->request->getIntegerParam('wiki_id'))) {
            $this->flash->success(t('Wiki line removed successfully.'));
        } else {
            $this->flash->failure(t('Unable to remove this wiki line.'));
        }

        $this->response->redirect($this->helper->url->to('WikiLineController', 'show', array('plugin' => 'wiki', 'project_id' => $project['id'])), true);
    }
}
