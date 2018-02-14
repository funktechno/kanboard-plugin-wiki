<?php

namespace Kanboard\Plugin\Budget\Controller;

use Kanboard\Controller\BaseController;

class BudgetLineController extends BaseController
{
    public function show()
    {
        $project = $this->getProject();

        if (empty($values)) {
            $values['date'] = date('Y-m-d');
        }

        $this->response->html($this->helper->layout->project('budget:budget_lines/show', array(
            'lines' => $this->budget->getAll($project['id']),
            'project' => $project,
            'title' => t('Budget lines')
        ), 'budget:budget/sidebar'));
    }

    public function create(array $values = array(), array $errors = array())
    {
        $project = $this->getProject();

        if (empty($values)) {
            $values['date'] = date('Y-m-d');
        }

        $this->response->html($this->helper->layout->project('budget:budget_lines/create', array(
            'values' => $values + array('project_id' => $project['id']),
            'errors' => $errors,
            'project' => $project,
            'title' => t('Budget lines')
        ), 'budget:budget/sidebar'));
    }

    /**
     * Validate and save a new budget
     *
     * @access public
     */
    public function save()
    {
        $project = $this->getProject();

        $values = $this->request->getValues();
        list($valid, $errors) = $this->budget->validateCreation($values);

        if ($valid) {

            if ($this->budget->create($values['project_id'], $values['amount'], $values['comment'], $values['date'])) {
                $this->flash->success(t('The budget line have been created successfully.'));
                $this->response->redirect($this->helper->url->to('BudgetLineController', 'create', array('plugin' => 'budget', 'project_id' => $project['id'])), true);
                return;
            } else {
                $this->flash->failure(t('Unable to create the budget line.'));
            }
        }

        $this->create($values, $errors);
    }

    /**
     * Confirmation dialog before removing a budget
     *
     * @access public
     */
    public function confirm()
    {
        $project = $this->getProject();

        $this->response->html($this->template->render('budget:budget_lines/remove', array(
            'project' => $project,
            'budget_id' => $this->request->getIntegerParam('budget_id'),
        )));
    }

    /**
     * Remove a budget
     *
     * @access public
     */
    public function remove()
    {
        $this->checkCSRFParam();
        $project = $this->getProject();

        if ($this->budget->remove($this->request->getIntegerParam('budget_id'))) {
            $this->flash->success(t('Budget line removed successfully.'));
        } else {
            $this->flash->failure(t('Unable to remove this budget line.'));
        }

        $this->response->redirect($this->helper->url->to('BudgetLineController', 'show', array('plugin' => 'budget', 'project_id' => $project['id'])), true);
    }
}
