<?php

namespace Kanboard\Plugin\Wiki\Controller;

use Kanboard\Controller\BaseController;
use Kanboard\Core\Controller\AccessForbiddenException;

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
        $query = $this->wikiModel->getQueryByProjectIds($projectIds);


        // echo json_encode($query->findAll());
        // exit();
        // $wikipages = $this->wikiModel->getWikipages($project['id']);

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
            'title'       => t('Wiki pages'),
            'values'      => array('search' => $search),
        )));
    }

    public function readonly()
    {
        $token = $this->request->getStringParam('token');
        $project = $this->projectModel->getByToken($token);

        if (empty($project)) {
            throw AccessForbiddenException::getInstance()->withoutLayout();
        }

        $wikipages = $this->wikiModel->getWikipages($project['id']);
        $result = $this->prepareWikipagesTree($wikipages);
        $wiki_list = $this->helper->wikiHelper->generateIndentedChildren($result['tree'], true, 0, 0, -1);

        $this->response->html($this->helper->layout->app('wiki:wiki/show', array(
            'project' => $project,
            'not_editable' => true,
            'title' => $project['name'],
            'wikipages' => $wiki_list,
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

        $wikipages = $this->wikiModel->getWikipages($project['id']);
        $result = $this->prepareWikipagesTree($wikipages);
        $wiki_list = $this->helper->wikiHelper->generateIndentedChildren($result['tree'], true, 0, 0, -1);

        $this->response->html($this->helper->layout->app('wiki:wiki/show', array(
            'project' => $project,
            'title' => $project['name'],
            'wikipages' => $wiki_list,
        )));

        // ,array(
        //     'wikipages' => $this->wikiModel->getWikipages($project['id'])
        // )
    }

    public function editions()
    {
        $project = $this->getProject();

        $wiki_id = $this->request->getIntegerParam('wiki_id');
        $wikipages = $this->wikiModel->getWikipages($project['id']);
        $result = $this->prepareWikipagesTree($wikipages, $wiki_id);

        $this->response->html($this->helper->layout->app('wiki:wiki/editions', array(
            'project' => $project,
            'title' => $project['name'],
            'wiki_id'=> $result['selected']['id'],
            'wikipage' => $result['selected'],
            'wikipages' => $result['tree'],
            'editions' => $this->wikiModel->getEditions($wiki_id),
        )));

    }

    public function edit(array $values = array(), array $errors = array())
    {
        $wiki_id = $this->request->getIntegerParam('wiki_id');

        $editwiki = $this->wikiModel->getWikipage($wiki_id);

        // if (empty($values)) {
        //     $values['date_creation'] = date('Y-m-d');
        //     $values['date_modification'] = date('Y-m-d');
        // }

        $wikipages = $this->wikiModel->getWikipages($editwiki['project_id']);
        $result = $this->prepareWikipagesTree($wikipages, $wiki_id);
        $wiki_list = $this->helper->wikiHelper->generateIndentedChildren($result['tree'], false, 0, $wiki_id);

        $this->response->html($this->helper->layout->app('wiki:wiki/edit', array(
            'wiki_id' => $wiki_id,
            'values' => $editwiki,
            'errors' => $errors,
            'wiki_list' => $wiki_list,
            'title' => t('Edit Wikipage'),
        )));
    }

    public function detail_readonly() {
        $token = $this->request->getStringParam('token');

        $project = $this->projectModel->getByToken($token);

        if (empty($project)) {
            throw AccessForbiddenException::getInstance()->withoutLayout();
        }
        $wiki_id = $this->request->getIntegerParam('wiki_id');

        $wikipages = $this->wikiModel->getWikipages($project['id']);
        $result = $this->prepareWikipagesTree($wikipages, $wiki_id);
        $wikipage_sublist = $this->helper->wikiHelper->generateIndentedChildren($result['tree'], false, $wiki_id, 0, -1);

        if ($wiki_id != 0) {
            $this->response->html($this->helper->layout->app('wiki:wiki/detail', array(
                'project' => $project,
                'title' => $project['name'],
                'wiki_id' => $result['selected']['id'],
                'wiki' => $result['selected'],
                'not_editable' => true,
                'files' => $this->wikiFileModel->getAllDocuments($wiki_id),
                'images' => $this->wikiFileModel->getAllImages($wiki_id),
                'wikipages' => $result['tree'],
                'wikipage' => $result['selected'],
                'wikipage_sublist' => $wikipage_sublist,
            )));
        } else {
            $this->response->html($this->helper->layout->app('wiki:wiki/sidebar', array(
                'project' => $project,
                'title' => $project['name'],
                'wiki_id' => $wiki_id,
                'wikipages' => $result['tree'],
                'not_editable' => true,
            )));
        }
    }

    /**
     * details for single wiki page
     */
    public function detail()
    {
        $project = $this->getProject();

        $wiki_id = $this->request->getIntegerParam('wiki_id');

        $wikipages = $this->wikiModel->getWikipages($project['id']);
        $result = $this->prepareWikipagesTree($wikipages, $wiki_id);
        $wikipage_sublist = $this->helper->wikiHelper->generateIndentedChildren($result['tree'], false, $wiki_id, 0, -1);

        if ($wiki_id != 0) {
            $this->response->html($this->helper->layout->app('wiki:wiki/detail', array(
                'project' => $project,
                'title' => $project['name'],
                'wiki_id' => $result['selected']['id'],
                'wiki' => $result['selected'],
                'files' => $this->wikiFileModel->getAllDocuments($wiki_id),
                'images' => $this->wikiFileModel->getAllImages($wiki_id),
                'wikipages' => $result['tree'],
                'wikipage' => $result['selected'],
                'wikipage_sublist' => $wikipage_sublist,
            )));
        } else {
            $this->response->html($this->helper->layout->app('wiki:wiki/sidebar', array(
                'project' => $project,
                'title' => $project['name'],
                'wiki_id' => $wiki_id,
                'wikipages' => $result['tree'],
            )));
        }
    }

    // public function breakdown()
    // {
    //     $project = $this->getProject();

    //     $paginator = $this->paginator
    //         ->setUrl('WikiController', 'breakdown', array('plugin' => 'wiki', 'project_id' => $project['id']))
    //         ->setMax(30)
    //         ->setOrder('start')
    //         ->setDirection('DESC')
    //         ->setQuery($this->wikiModel->getSubtaskBreakdown($project['id']))
    //         ->calculate();

    //     $this->response->html($this->helper->layout->project('wiki:wiki/breakdown', array(
    //         'paginator' => $paginator,
    //         'project' => $project,
    //         'title' => t('Wiki'),
    //     )));
    // }

    private function getNestedChildren($parent_id, $items) {
        $children = [];

        foreach ($items as $item) {
            if ($item['parent_id'] === $parent_id) {
                $item['children'] = $this->getNestedChildren($item['id'], $items);
                array_push($children, $item);
            }
        }

        return $children;
    }

    private function prepareWikipagesTree($wikipages, $selected_wiki_id = 0) {
        $treeWikipages = array();
        $defaultWikipage = null;
        $selectedWikipage = null;

        foreach ($wikipages as $page) {
            if ($selected_wiki_id == $page['id']) {
                $selectedWikipage = $page;
            }
            if(!isset($page['parent_id'])){
                $page['children'] = $this->getNestedChildren($page['id'], $wikipages);
                array_push($treeWikipages, $page);

                // set the first root page as default
                if ($page['ordercolumn'] == 0) {
                   $defaultWikipage = $page;
                }
            }
        }

        if (!isset($defaultWikipage)) {
          $defaultWikipage = end($wikipages);
        }
        if (!isset($selectedWikipage)) {
          $selectedWikipage = $defaultWikipage;
        }

        return array('tree' => $treeWikipages, 'selected' => $selectedWikipage);
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
     * Restore a wikipage edition
     *
     * @access public
     */
    public function restore()
    {
        $project = $this->getProject();

        if ($this->wikiModel->restoreEdition($this->request->getIntegerParam('wiki_id'), $this->request->getIntegerParam('edition'))) {
            $this->flash->success(t('Edition was restored successfully.'));
            $this->response->redirect($this->helper->url->to('WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $this->request->getIntegerParam('wiki_id'))), true);
        } else {
            $this->flash->failure(t('Unable to restore this wiki edition.'));
            $this->response->redirect($this->helper->url->to('WikiController', 'editions', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $this->request->getIntegerParam('wiki_id'))), true);
        }
    }

    /**
     * Purge a wikipage edition
     *
     * @access public
     */
    public function purge()
    {
        $project = $this->getProject();
        $wiki_id = $this->request->getIntegerParam('wiki_id');
        $edition = $this->request->getIntegerParam('edition');

        $wikipage = $this->wikiModel->getWikipage($wiki_id);
        if ($wikipage['current_edition'] == $edition) {
            $this->flash->failure(t('Your current wiki edition cannot be purged.'));
            $this->response->redirect($this->helper->url->to('WikiController', 'editions', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $this->request->getIntegerParam('wiki_id'))), true);
            return;
        }

        if ($this->wikiModel->purgeEdition($wiki_id, $edition)) {
            $this->flash->success(t('Edition was purged successfully.'));
        } else {
            $this->flash->failure(t('Unable to purge this wiki edition.'));
        }
        $this->response->redirect($this->helper->url->to('WikiController', 'editions', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $this->request->getIntegerParam('wiki_id'))), true);
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
     * Confirmation dialog before purging an edition
     *
     * @access public
     */
    public function confirm_purge()
    {
        $project = $this->getProject();

        $this->response->html($this->template->render('wiki:wiki/confirm_purge', array(
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
        list($valid, $errors) = $this->wikiModel->validatePageCreation($values);

        if ($valid) {

            $newDate = date('Y-m-d');

            $wiki_id = $this->wikiModel->createpage($values['project_id'], $values['title'], $values['content'], $newDate);
            if ($wiki_id > 0) {

                $this->wikiModel->createEdition($values, $wiki_id, 1, $newDate);
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
        list($valid, $errors) = $this->wikiModel->validatePageUpdate($values);

        if ($valid) {

            $newDate = date('Y-m-d');
            $editions = $values['editions'] + 1;

            $wiki_id = $this->wikiModel->updatepage($values, $editions, $newDate);
            if ($wiki_id > 0) {

                // check config if admin wants editions saved
                $this->wikiModel->createEdition($values, $wiki_id, $editions, $newDate);
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
        )));
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
        $wiki_id = $this->request->getIntegerParam('wiki_id');

        // First delete all associated files, then delete the page itself.
        if ($this->wikiFileModel->removeAll($wiki_id) && $this->wikiModel->removepage($wiki_id)) {
            $this->flash->success(t('Wiki page removed successfully.'));
        } else {
            $this->flash->failure(t('Unable to remove this wiki page.'));
        }

        // FIXME This works only if there are remaining pages.
        $this->response->redirect($this->helper->url->to('WikiController', 'show', array('plugin' => 'wiki', 'project_id' => $project['id'])), true);
    }

}
