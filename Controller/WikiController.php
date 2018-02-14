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
    public function show()
    {
        $project = $this->getProject();

        $this->response->html($this->helper->layout->project('wiki:wiki/show', array(
            'daily_wiki' => $this->wiki->getDailyWikiBreakdown($project['id']),
            'project' => $project,
            'title' => t('Wiki'),
            'wikipages' => $this->wiki->getWikipages($project['id'])
        ), 'wiki:wiki/sidebar',array(
            'wikipages' => $this->wiki->getWikipages($project['id'])
        )));
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
}
