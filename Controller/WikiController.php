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
     * Get a wiki page, only if it belongs to the given project
     *
     * @access private
     * @param  integer $wiki_id
     * @param  integer $project_id
     * @return array
     */
    private function getProjectWikipage($wiki_id, $project_id)
    {
        $wikipage = $this->wikiModel->getWikipage($wiki_id);

        if (empty($wikipage) || $wikipage['project_id'] != $project_id) {
            throw new AccessForbiddenException();
        }

        return $wikipage;
    }

    /**
     * Get a wiki page, only if the user has access to its project.
     * For actions that do not have the project_id in the URL.
     *
     * @access private
     * @param  integer $wiki_id
     * @return array
     */
    private function getAuthorizedWikipage($wiki_id)
    {
        $wikipage = $this->wikiModel->getWikipage($wiki_id);

        if (empty($wikipage) || ! $this->helper->user->hasProjectAccess('WikiController', 'edit', $wikipage['project_id'])) {
            throw new AccessForbiddenException();
        }

        return $wikipage;
    }

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

        $query = $this->wikiModel->getQueryByProjectIds($projectIds);

        // echo json_encode($query->findAll());
        // exit();

        $search = $this->request->getStringParam('search');

        if ($search !== '') {
            $query->beginOr();
            $query->ilike('wikipage.title', '%' . $search . '%');
            $query->ilike('wikipage.content', '%' . $search . '%');
            $query->closeOr();
        }

        $paginator = $this->paginator
            ->setUrl('WikiController', 'index', array('plugin' => 'wiki', 'search' => $search))
            ->setMax(10)
            ->setOrder('title')
            ->setQuery($query)
            ->calculate();

        $this->response->html($this->helper->layout->app('wiki:wiki_search/listing', array(
            'paginator'   => $paginator,
            'title'       => t('Wiki Pages'),
            'values'      => array('search' => $search),
        )));
    }

    public function readonly()
    {
        $token = $this->request->getStringParam('token');
        $project = $this->projectModel->getByToken($token);

        if (empty($project) || ! $this->wikiModel->isPublicWikiEnabled()) {
            throw AccessForbiddenException::getInstance()->withoutLayout();
        }

        $wikipages = $this->wikiModel->getWikipages($project['id'], true);
        $result = $this->prepareWikipagesTree($wikipages);
        $wiki_list = $this->helper->wikiHelper->generateIndentedChildren($result['tree'], true, 0, 0, -1);

        $this->response->html($this->helper->layout->app('wiki:wiki/show', array(
            'project' => $project,
            'not_editable' => true,
            'title' => $project['name'],
            'description' => $this->helper->projectHeader->getDescription($project),
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

        $wikipages = $this->wikiModel->getWikipages($project['id'], true);
        $result = $this->prepareWikipagesTree($wikipages);
        $wiki_list = $this->helper->wikiHelper->generateIndentedChildren($result['tree'], true, 0, 0, -1);

        $this->response->html($this->helper->layout->app('wiki:wiki/show', array(
            'project' => $project,
            'title' => $project['name'],
            'description' => $this->helper->projectHeader->getDescription($project),
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
        $this->getProjectWikipage($wiki_id, $project['id']);
        $wikipages = $this->wikiModel->getWikipages($project['id']);
        $result = $this->prepareWikipagesTree($wikipages, $wiki_id);
        $all = $this->wikiModel->getEditions($wiki_id);

        // one page of editions; the editions they are compared with come along (hidden) when they are on another page
        $per_page = 20;
        $pages = max(1, (int) ceil(count($all) / $per_page));
        $page = min(max(1, $this->request->getIntegerParam('page', 1)), $pages);
        $editions = array_slice($all, ($page - 1) * $per_page, $per_page);
        $shown = array_column($editions, 'edition');
        $bases = array();
        foreach ($all as $edition) {
            if (in_array($edition['edition'], array_column($editions, 'base'), true) && ! in_array($edition['edition'], $shown, true)) {
                $bases[] = $edition;
            }
        }

        $authors = array();
        foreach (array_unique(array_column($editions, 'creator_id')) as $user_id) {
            $user = $user_id > 0 ? $this->userModel->getById($user_id) : null;
            $authors[$user_id] = $user ? ($user['name'] ?: $user['username']) : '';
        }

        $this->response->html($this->helper->layout->app('wiki:wiki/editions', array(
            'project' => $project,
            'title' => $project['name'],
            'description' => $this->helper->projectHeader->getDescription($project),
            'wiki_id'=> $result['selected']['id'],
            'wikipage' => $result['selected'],
            'wikipages' => $result['tree'],
            'editions' => $editions,
            'base_editions' => $bases,
            'titles' => array_column($all, 'title', 'edition'),
            'pagination' => array('page' => $page, 'pages' => $pages, 'total' => count($all)),
            'authors' => $authors,
        )));

    }

    public function edit(array $values = array(), array $errors = array())
    {
        $wiki_id = $this->request->getIntegerParam('wiki_id');

        $editwiki = $this->getAuthorizedWikipage($wiki_id);

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

        if (empty($project) || ! $this->wikiModel->isPublicWikiEnabled()) {
            throw AccessForbiddenException::getInstance()->withoutLayout();
        }

        $wiki_id = $this->request->getIntegerParam('wiki_id', -1);
        if ($wiki_id >= 0) {
            $_SESSION["last_wiki_id"] = $wiki_id; // cache last selected wiki page
        } else if (array_key_exists("last_wiki_id", $_SESSION) && $_SESSION["last_wiki_id"]) {
            $wiki_id = $_SESSION["last_wiki_id"]; // restore last selected page
        } else {
            $wiki_id = 0; // redirect to (root) page
        }

        if ($wiki_id != 0) {
            $wikipage = $this->wikiModel->getWikipage($wiki_id);

            if (empty($wikipage) || $wikipage['project_id'] != $project['id'] || empty($wikipage['is_active'])) {
                if ($this->request->getIntegerParam('wiki_id', -1) >= 0) {
                    throw new AccessForbiddenException();
                }

                $wiki_id = 0; // the cached page is from another project or archived, show the root
            }
        }

        $wikipages = $this->wikiModel->getWikipages($project['id']);
        $result = $this->prepareWikipagesTree($wikipages, $wiki_id);

        if ($wiki_id != 0) {
            $this->response->html($this->helper->layout->app('wiki:wiki/detail', array(
                'project' => $project,
                'title' => $project['name'],
                'description' => $this->helper->projectHeader->getDescription($project),
                'wiki_id' => $result['selected']['id'],
                'wiki' => $result['selected'],
                'not_editable' => true,
                'public_token' => $this->wikiModel->isPublicAttachmentsEnabled() ? $project['token'] : '',
                'files' => $this->wikiFileModel->getAllDocuments($wiki_id),
                'images' => $this->wikiFileModel->getAllImages($wiki_id),
                'wikipages' => $result['tree'],
                'wikipage' => $result['selected'] + array('edition_count' => $this->wikiEditionStore->count((int) $wiki_id)),
                'wikipage_sublist' => $this->helper->wikiHelper->generateIndentedChildren($result['tree'], false, $wiki_id, 0, -1, true),
            )));
        } else {
            $this->response->html($this->helper->layout->app('wiki:wiki/sidebar', array(
                'project' => $project,
                'title' => $project['name'],
                'description' => $this->helper->projectHeader->getDescription($project),
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

        $wiki_id = $this->request->getIntegerParam('wiki_id', -1);
        if ($wiki_id >= 0) {
            $_SESSION["last_wiki_id"] = $wiki_id; // cache last selected wiki page
        } else if (array_key_exists("last_wiki_id", $_SESSION) && $_SESSION["last_wiki_id"]) {
            $wiki_id = $_SESSION["last_wiki_id"]; // restore last selected page
        } else {
            $wiki_id = 0; // redirect to (root) page
        }

        if ($wiki_id != 0) {
            $wikipage = $this->wikiModel->getWikipage($wiki_id);

            if (empty($wikipage) || $wikipage['project_id'] != $project['id']) {
                if ($this->request->getIntegerParam('wiki_id', -1) >= 0) {
                    throw new AccessForbiddenException();
                }

                $wiki_id = 0; // the cached page is from another project, show the root
            }
        }

        $wikipages = $this->wikiModel->getWikipages($project['id']);
        $result = $this->prepareWikipagesTree($wikipages, $wiki_id);

        if ($wiki_id != 0) {
            $this->response->html($this->helper->layout->app('wiki:wiki/detail', array(
                'project' => $project,
                'title' => $project['name'],
                'description' => $this->helper->projectHeader->getDescription($project),
                'wiki_id' => $result['selected']['id'],
                'wiki' => $result['selected'],
                'last_edition' => $this->wikiModel->getLatestEdition($wiki_id),
                'files' => $this->wikiFileModel->getAllDocuments($wiki_id),
                'images' => $this->wikiFileModel->getAllImages($wiki_id),
                'wikipages' => $result['tree'],
                'wikipage' => $result['selected'] + array('edition_count' => $this->wikiEditionStore->count((int) $wiki_id)),
                'wikipage_sublist' => $this->helper->wikiHelper->generateIndentedChildren($result['tree'], false, $wiki_id, 0, -1, true),
            )));
        } else {
            $this->response->html($this->helper->layout->app('wiki:wiki/sidebar', array(
                'project' => $project,
                'title' => $project['name'],
                'description' => $this->helper->projectHeader->getDescription($project),
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
    //         'description' => $this->helper->projectHeader->getDescription($project),
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
     * Store wiki tree expanded branches in session
     *
     * @access public
     */
    public function wikitree()
    {
        $expandedWikiBranches = json_decode($this->request->getStringParam('wiki_tree'));
        $_SESSION["wiki_tree"] = $expandedWikiBranches;

        echo(json_encode( $_SESSION["wiki_tree"] ));
    }

    /**
     * Confirmation dialog before removing a wiki
     *
     * @access public
     */
    public function confirm()
    {
        $project = $this->getProject();
        $this->getProjectWikipage($this->request->getIntegerParam('wiki_id'), $project['id']);

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
        $this->checkCSRFParam();
        $project = $this->getProject();
        $this->getProjectWikipage($this->request->getIntegerParam('wiki_id'), $project['id']);

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
        $this->checkCSRFParam();
        $project = $this->getProject();
        $wiki_id = $this->request->getIntegerParam('wiki_id');
        $edition = $this->request->getIntegerParam('edition');

        $wikipage = $this->getProjectWikipage($wiki_id, $project['id']);
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
        $this->getProjectWikipage($this->request->getIntegerParam('wiki_id'), $project['id']);

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
        $this->getProjectWikipage($this->request->getIntegerParam('wiki_id'), $project['id']);

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
        $values['project_id'] = $project['id'];
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
        $wikipage = $this->getAuthorizedWikipage(isset($values['id']) ? $values['id'] : 0);

        if (! empty($values['parent_id'])) {
            $this->getProjectWikipage($values['parent_id'], $wikipage['project_id']);
        }

        list($valid, $errors) = $this->wikiModel->validatePageUpdate($values);

        if ($valid) {

            $newDate = date('Y-m-d');
            $editions = $this->wikiEditionStore->nextEdition((int) $wikipage['id'], (int) $wikipage['editions']);

            $wiki_id = $this->wikiModel->updatepage($values, $editions, $newDate);
            if ($wiki_id > 0) {

                // check config if admin wants editions saved; $wikipage is the page before this save
                $this->wikiModel->createEdition($values, $wiki_id, $editions, $newDate, $wikipage);
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
        $this->getProjectWikipage($wiki_id, $project['id']);

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
