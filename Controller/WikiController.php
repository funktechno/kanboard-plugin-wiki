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
     * list for wikipages a user has access to
     */
    public function index()
    {
        if ($this->userSession->isAdmin()) {
            $projectIds = $this->projectModel->getAllIds();
        } else {
            $projectIds = $this->projectPermissionModel->getProjectIds($this->userSession->getId());
        }
        // echo json_encode($projectIds);
        // exit();

        // $query = $this->projectModel->getQueryByProjectIds($projectIds);
        $query = $this->wiki->getQueryByProjectIds($projectIds);


        // echo json_encode($query->findAll());
        // exit(); 
        // $wikipages = $this->wiki->getWikipages($project['id']);

        $search = $this->request->getStringParam('search');

        if ($search !== '') {
            $query->ilike('wikipage.content', '%' . $search . '%');
        }

        $paginator = $this->paginator
            ->setUrl('WikiController', 'index', array('plugin' => 'Wiki'))
            ->setMax(20)
            ->setOrder('title')
            ->setQuery($query)
            ->calculate();

        $this->response->html($this->helper->layout->app('wiki:wiki_list/listing', array(
            'paginator'   => $paginator,
            'title'       => t('Wikis') . ' (' . $paginator->getTotal() . ')',
            'values'      => array('search' => $search),
        )));
    }

    /**
     * list for wikipages for a project
     */
    public function show()
    {
        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);

        $project = $this->getProject();

        $this->response->html($this->helper->layout->app('wiki:wiki/show', array(
            'project' => $project,
            'title' => t('Wiki'),
            'wikipages' => $this->wiki->getWikipages($project['id']),
        ), 'wiki:wiki/sidebar'));

        // ,array(
        //     'wikipages' => $this->wiki->getWikipages($project['id'])
        // )
    }

    public function editions()
    {

        $project = $this->getProject();

        $wiki_id = $this->request->getIntegerParam('wiki_id');
        // $project = $this->getProject();
        //
        // for list use window-restore

        // restore button use undo

        $this->response->html($this->helper->layout->project('wiki:wiki/editions', array(
            'project' => $project,
            'title' => t('Wiki Editions'),
            'wiki_id'=> $wiki_id,
            'editions' => $this->wiki->getEditions($wiki_id),
        ), 'wiki:wiki/sidebar'));

    }

    public function edit(array $values = array(), array $errors = array())
    {

        $wiki_id = $this->request->getIntegerParam('wiki_id');

        $editwiki = $this->wiki->getWikipage($wiki_id);

        // if (empty($values)) {
        //     $values['date_creation'] = date('Y-m-d');
        //     $values['date_modification'] = date('Y-m-d');
        // }

        // $values['wikipage']
        $this->response->html($this->helper->layout->app('wiki:wiki/edit', array(
            'wiki_id' => $wiki_id,
            'values' => $editwiki,
            'errors' => $errors,
            'title' => t('Edit Wikipage'),
        ), 'wiki:wiki/sidebar'));
    }

    /**
     * details for single wiki page
     */
    public function detail()
    {
        $project = $this->getProject();
        $wiki_id = $this->request->getIntegerParam('wiki_id');

        $wikipages = $this->wiki->getWikipages($project['id']);

        foreach ($wikipages as $page) {
            if (t($wiki_id) == t($page['id'])) {
                $wikipage = $page;
                break;
            }
        }

        // use a wiki helper for better side bar TODO:
        $this->response->html($this->helper->layout->app('wiki:wiki/detail', array(
            'project' => $project,
            'title' => t('Wikipage'),
            'wiki_id' => $wiki_id,
            'wiki' => $wikipage,
            'files' => $this->wikiFile->getAllDocuments($wiki_id),
            'images' => $this->wikiFile->getAllImages($wiki_id),
            // 'wikipage' => $this->wiki->getWikipage($wiki_id),
            'wikipage' => $wikipage,
            'wikipages' => $wikipages,
        ), 'wiki:wiki/sidebar'));

        // $wikipage= $wikipages->select(1)->eq('id', $wiki_id)->findOne();

        // $wikipage= $wikipages->eq('id', $wiki_id);

        // $this->response->html($this->helper->layout->project('wiki:wiki/detail', array(
        //     'project' => $project,
        //     'title' => t('Wikipage'),
        //     'wiki_id' => $wiki_id,
        //     // 'wikipage' => $this->wiki->getWikipage($wiki_id),
        //     'wikipage' => $wikipage,
        //     'wikipages' => $wikipages,
        // ), 'wiki:wiki/sidebar'));

        // ,array(
        //     'wikipages' => $this->wiki->getWikipages($project['id'])
        // )
    }

    // public function breakdown()
    // {
    //     $project = $this->getProject();

    //     $paginator = $this->paginator
    //         ->setUrl('WikiController', 'breakdown', array('plugin' => 'wiki', 'project_id' => $project['id']))
    //         ->setMax(30)
    //         ->setOrder('start')
    //         ->setDirection('DESC')
    //         ->setQuery($this->wiki->getSubtaskBreakdown($project['id']))
    //         ->calculate();

    //     $this->response->html($this->helper->layout->project('wiki:wiki/breakdown', array(
    //         'paginator' => $paginator,
    //         'project' => $project,
    //         'title' => t('Wiki'),
    //     ), 'wiki:wiki/sidebar'));
    // }

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
     * Remove a wikipage
     *
     * @access public
     */
    public function restore()
    {
        // $this->checkCSRFParam();
        $project = $this->getProject();

        if ($this->wiki->restoreEdition($this->request->getIntegerParam('wiki_id'), $this->request->getIntegerParam('edition'))) {
            $this->flash->success(t('Edition was restored successfully.'));
            $this->response->redirect($this->helper->url->to('WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $this->request->getIntegerParam('wiki_id'))), true);
            // $this->url->link(t($page['title']), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $page['id']))

        } else {
            $this->flash->failure(t('Unable to restore this wiki edition.'));
            $this->response->redirect($this->helper->url->to('WikiController', 'editions', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $this->request->getIntegerParam('wiki_id'))), true);

        }
        // redirect to detail
        // $this->response->redirect($this->helper->url->to('WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $this->request->getIntegerParam('wiki_id'))), true);


        // $this->response->redirect($this->helper->url->to('WikiController', 'editions', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $this->request->getIntegerParam('wiki_id'))), true);
    }

    /**
     * Confirmation dialog before restoring an edition
     *
     * @access public
     */
    public function confirm_restore()
    {
        $project = $this->getProject();

        $this->response->html($this->template->render('wiki:wiki/confirm_restore', array(
            'project' => $project,
            'wiki_id' => $this->request->getIntegerParam('wiki_id'),
            'edition' => $this->request->getIntegerParam('edition'),
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

            $newDate = date('Y-m-d');

            $wiki_id = $this->wiki->createpage($values['project_id'], $values['title'], $values['content'], $newDate);
            if ($wiki_id > 0) {

                $this->wiki->createEdition($values, $wiki_id, 1, $newDate);
                // don't really care if edition was successful

                $this->flash->success(t('The wikipage has been created successfully.'));
                $this->response->redirect($this->helper->url->to('WikiController', 'create', array('plugin' => 'wiki', 'project_id' => $project['id'])), true);
                return;
            } else {
                $this->flash->failure(t('Unable to create the wikipage.'));
            }
        }

        $this->create($values, $errors);
    }
    /**
     * switch the orders between two wikipages
     * @access public
     */
    public function switchOrder()
    {

    }

    /**
     * Validate and update a wikipage
     *
     * @access public
     */
    public function update()
    {
        // $project = $this->getProject();

        $values = $this->request->getValues();
        list($valid, $errors) = $this->wiki->validatePageUpdate($values);

        if ($valid) {

            $newDate = date('Y-m-d');
            $editions = $values['editions'] + 1;

            $wiki_id = $this->wiki->updatepage($values, $editions, $newDate);
            if ($wiki_id > 0) {

                // check config if admin wants editions saved
                $this->wiki->createEdition($values, $wiki_id, $editions, $newDate);
                // don't really care if editions was successful, begin transaction not really needed

                $this->flash->success(t('The wikipage has been updated successfully.'));
                $this->response->redirect($this->helper->url->to('WikiController', 'edit', array('plugin' => 'wiki', 'wiki_id' => $values['id'])), true);
                return;
            } else {
                $this->flash->failure(t('Unable to update the wikipage.'));
            }
        }

        $this->edit($values, $errors);
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
            'title' => t('Wikipage'),
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
    
}
