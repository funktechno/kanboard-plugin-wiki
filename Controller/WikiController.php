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
     * Remove a wiki
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
