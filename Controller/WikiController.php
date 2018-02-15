<?php

namespace Kanboard\Plugin\Wiki\Controller;

use Kanboard\Controller\BaseController;

/**
 * Wiki
 *
 * @package controller
 * @author  Frederic Guillot
 */
class WikiController extends BaseController
{
    /**
     * list for wikipages
     */
    public function show()
    {
        $project = $this->getProject();

        $this->response->html($this->helper->layout->project('wiki:wiki/show', array(
            'daily_wiki' => $this->wiki->getDailyWikiBreakdown($project['id']),
            'project' => $project,
            'title' => t('Wiki'),
            'wikipages' => $this->wiki->getWikipages($project['id'])
        ), 'wiki:wiki/sidebar'));

        // ,array(
        //     'wikipages' => $this->wiki->getWikipages($project['id'])
        // )
    }
    /**
     * details for single wiki page
     */
    public function detail()
    {
        $project = $this->getProject();

        $this->response->html($this->helper->layout->project('wiki:wiki/show', array(
            'daily_wiki' => $this->wiki->getDailyWikiBreakdown($project['id']),
            'project' => $project,
            'title' => t('Wiki'),
            'wikipages' => $this->wiki->getWikipages($project['id'])
        ), 'wiki:wiki/sidebar'));

        // ,array(
        //     'wikipages' => $this->wiki->getWikipages($project['id'])
        // )
    }

    public function breakdown()
    {
        $project = $this->getProject();

        $paginator = $this->paginator
            ->setUrl('WikiController', 'breakdown', array('plugin' => 'wiki', 'project_id' => $project['id']))
            ->setMax(30)
            ->setOrder('start')
            ->setDirection('DESC')
            ->setQuery($this->wiki->getSubtaskBreakdown($project['id']))
            ->calculate();

        $this->response->html($this->helper->layout->project('wiki:wiki/breakdown', array(
            'paginator' => $paginator,
            'project' => $project,
            'title' => t('Wiki')
        ), 'wiki:wiki/sidebar'));
    }

    /**
     * Confirmation dialog before removing a wiki
     *
     * @access public
     */
    public function confirm()
    {
        $project = $this->getProject();

        $this->response->html($this->template->render('wiki:wiki/remove', array(
            'project' => $project,
            'wiki_id' => $this->request->getIntegerParam('wiki_id'),
        )));
    }

    /**
     * Validate and save a new wikipage
     *
     * @access public
     */
    public function save()
    {
        $project = $this->getProject();

        $values = $this->request->getValues();
        list($valid, $errors) = $this->wiki->validatePageCreation($values);

        if ($valid) {
            if ($this->wiki->createpage($values['project_id'], $values['title'], $values['content'])) {
                $this->flash->success(t('The wikipage have been created successfully.'));
                $this->response->redirect($this->helper->url->to('WikiController', 'create', array('plugin' => 'wiki', 'project_id' => $project['id'])), true);
                return;
            } else {
                $this->flash->failure(t('Unable to create the wikipage.'));
            }
        }

        $this->create($values, $errors);
    }

    public function create(array $values = array(), array $errors = array())
    {
        $project = $this->getProject();

        if (empty($values)) {
            $values['date_creation'] = date('Y-m-d');
            $values['date_modification'] = date('Y-m-d');
        }

        $this->response->html($this->helper->layout->project('wiki:wiki/create', array(
            'values' => $values + array('project_id' => $project['id']),
            'errors' => $errors,
            'project' => $project,
            'title' => t('Wikipage')
        ), 'wiki:wiki/sidebar'));
    }

    /**
     * Remove a wikipage
     *
     * @access public
     */
    public function remove()
    {
        $this->checkCSRFParam();
        $project = $this->getProject();

        if ($this->wiki->removepage($this->request->getIntegerParam('wiki_id'))) {
            $this->flash->success(t('Wiki page removed successfully.'));
        } else {
            $this->flash->failure(t('Unable to remove this wiki page.'));
        }

        $this->response->redirect($this->helper->url->to('WikiController', 'show', array('plugin' => 'wiki', 'project_id' => $project['id'])), true);
    }

    // public function sidebar(){
    //     $project = $this->getProject();
    //     $this->response->html($this->helper->layout->project('wiki:wiki/show', array(
    //         'daily_wiki' => $this->wiki->getDailyWikiBreakdown($project['id']),
    //         'project' => $project,
    //         'title' => t('Wiki'),
    //         'wikipages' => $this->wiki->getWikipages($project['id'])
    //     ), 'wiki:wiki/sidebar',array(
    //         'wikipages' => $this->wiki->getWikipages($project['id'])
    //     )));

    // }
}
