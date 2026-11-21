<?php

namespace Kanboard\Plugin\Wiki\Model;

use Kanboard\Core\Base;
use Kanboard\Core\Controller\PageNotFoundException;
use Kanboard\Core\Controller\AccessForbiddenException;
use Kanboard\Model\UserModel;
use SimpleValidator\Validator;
use SimpleValidator\Validators;
use Kanboard\Plugin\Wiki\Job\WikiEventJob;

/**
 * Wiki
 *
 * @package  model
 * @author   Frederic Guillot
 */
class WikiModel extends Base
{
    /**
     * SQL table name
     *
     * @var string
     */
    const WIKI_EDITION_TABLE = WikiEditionStore::TABLE;

    /**
     * Get all of Wikipages' editions by edition
     *
     * @access public
     * @param  integer   $wiki_id
     * @return array
     */
    public function getEditions($wiki_id)
    {
        return $this->wikiEditionStore->all((int) $wiki_id);
    }

    /**
     * Get latest of Wikipages' editions
     *
     * @access public
     * @param  integer   $wiki_id
     * @return array
     */
    public function getLatestEdition($wiki_id)
    {
        return $this->wikiEditionStore->latest((int) $wiki_id);
    }

    /**
     * SQL table name
     *
     * @var string
     */
    const WIKITABLE = 'wikipage';

    /**
     * Number of stored editions of a page. `editions` is the last edition number, which deleted editions leave higher.
     */
    const EDITION_COUNT_SQL = 'SELECT COUNT(*) FROM '.self::WIKI_EDITION_TABLE.' WHERE wikipage_id='.self::WIKITABLE.'.id';

    /**
     * Public projects show their wiki through the public link (default on, the behavior before the setting existed)
     *
     * @return bool
     */
    public function isPublicWikiEnabled()
    {
        return $this->configModel->get('wiki_public_access', 1) == 1;
    }

    /**
     * Public wiki pages show their attachments (default off: it exposes files that were not shared before)
     *
     * @return bool
     */
    public function isPublicAttachmentsEnabled()
    {
        return $this->isPublicWikiEnabled() && $this->configModel->get('wiki_public_attachments', 0) == 1;
    }

    /**
     * Active pages of the given projects whose id starts with $search, for the wiki: autocomplete
     *
     * @param  integer[] $project_ids
     * @param  string    $search
     * @param  integer   $limit
     * @return array
     */
    public function getSuggestions(array $project_ids, $search = '', $limit = 25)
    {
        if (empty($project_ids)) {
            return array();
        }

        $pages = $this->db->table(self::WIKITABLE)
            ->columns(self::WIKITABLE . '.id', self::WIKITABLE . '.title', 'projects.name AS project_name')
            ->join('projects', 'id', 'project_id')
            ->in(self::WIKITABLE . '.project_id', $project_ids)
            ->eq(self::WIKITABLE . '.is_active', 1)
            ->asc('projects.name')
            ->asc(self::WIKITABLE . '.title')
            ->findAll();

        $search = trim((string) $search);
        $pages = array_filter($pages, function ($page) use ($search) {
            return $search === '' || strpos((string) $page['id'], $search) === 0;
        });

        return array_slice(array_values($pages), 0, $limit);
    }

    /**
     * Events
     *
     * @var string
     */
    const EVENT_UPDATE       = 'wikipage.update';
    const EVENT_CREATE       = 'wikipage.create';
    const EVENT_DELETE       = 'wikipage.delete';


    /**
     * retrieve wiki pages by parent id
     * @param mixed $project_id
     * @param mixed $parent_id
     * @return mixed
     */
    function getWikiPagesByParentId($project_id, $parent_id){
        if (isset($parent_id)) {
            return $this->db
                ->table(self::WIKITABLE)
                ->columns(
                    'c.name as creator_name',
                    'c.username as creator_username',
                    'mod.name as modifier_name',
                    'mod.username as modifier_username',
                    self::WIKITABLE . '.id',
                    self::WIKITABLE . '.title',
                    self::WIKITABLE . '.parent_id',
                    self::WIKITABLE . '.content',
                    self::WIKITABLE . '.project_id',
                    self::WIKITABLE . '.is_active',
                    self::WIKITABLE . '.ordercolumn',
                    self::WIKITABLE . '.creator_id',
                    self::WIKITABLE . '.date_creation',
                    self::WIKITABLE . '.date_modification',
                    self::WIKITABLE . '.editions',
                    self::WIKITABLE . '.current_edition',
                    self::WIKITABLE . '.modifier_id'
                )
                ->left(UserModel::TABLE, 'c', 'id', self::WIKITABLE, 'creator_id')
                ->left(UserModel::TABLE, 'mod', 'id', self::WIKITABLE, 'modifier_id')
                ->eq('project_id', $project_id)
                ->eq(self::WIKITABLE . '.is_active', 1)
                ->eq('parent_id', $parent_id)
                ->asc('ordercolumn')
                ->findAll();
        } else {
            return $this->db
                ->table(self::WIKITABLE)
                ->columns(
                    'c.name as creator_name',
                    'c.username as creator_username',
                    'mod.name as modifier_name',
                    'mod.username as modifier_username',
                    self::WIKITABLE . '.id',
                    self::WIKITABLE . '.title',
                    self::WIKITABLE . '.parent_id',
                    self::WIKITABLE . '.content',
                    self::WIKITABLE . '.project_id',
                    self::WIKITABLE . '.is_active',
                    self::WIKITABLE . '.ordercolumn',
                    self::WIKITABLE . '.creator_id',
                    self::WIKITABLE . '.date_creation',
                    self::WIKITABLE . '.date_modification',
                    self::WIKITABLE . '.editions',
                    self::WIKITABLE . '.current_edition',
                    self::WIKITABLE . '.modifier_id'
                )
                ->left(UserModel::TABLE, 'c', 'id', self::WIKITABLE, 'creator_id')
                ->left(UserModel::TABLE, 'mod', 'id', self::WIKITABLE, 'modifier_id')
                ->eq('project_id', $project_id)
                ->eq(self::WIKITABLE . '.is_active', 1)
                ->isNull('parent_id')
                ->asc('ordercolumn')
                ->findAll();
        }
    }

    /**
     * Get all Wiki Pages by order for a project
     *
     * @access public
     * @param  integer   $project_id
     * @param  boolean   $withEditionCount add edition_count; only for screens that show it, as it costs a lookup per page
     * @return array
     */
    public function getWikipages($project_id, $withEditionCount = false)
    {
        $query = $this->db->
            table(self::WIKITABLE)
            ->columns(
                'c.name as creator_name',
                'c.username as creator_username',
                // UserModel::TABLE . '.name as creator_name',
                // UserModel::TABLE . '.username as creator_username',
                'mod.name as modifier_name',
                'mod.username as modifier_username',
                // UserModel::TABLE . '.username as modifier_username',
                self::WIKITABLE . '.id',
                self::WIKITABLE . '.title',
                self::WIKITABLE . '.parent_id',
                self::WIKITABLE . '.content',
                self::WIKITABLE . '.project_id',
                self::WIKITABLE . '.is_active',
                self::WIKITABLE . '.ordercolumn',
                self::WIKITABLE . '.creator_id',
                self::WIKITABLE . '.date_creation',
                self::WIKITABLE . '.date_modification',
                self::WIKITABLE . '.editions',
                self::WIKITABLE . '.current_edition',
                self::WIKITABLE . '.modifier_id'
            )
        // ->join(UserModel::TABLE, 'id', 'creator_id')
        // ->left(UserModel::TABLE, 'uc', 'id', WikiModel::TABLE, 'creator_id')
            ->left(UserModel::TABLE, 'c', 'id', self::WIKITABLE, 'creator_id')
            ->left(UserModel::TABLE, 'mod', 'id', self::WIKITABLE, 'modifier_id')
            ->eq('project_id', $project_id)
            ->eq(self::WIKITABLE . '.is_active', 1)
            ->asc('parent_id')
            ->asc('ordercolumn');

        if ($withEditionCount) {
            $query->subquery(self::EDITION_COUNT_SQL, 'edition_count');
        }

        return $query->findAll();

        // return $this->db->table(self::TABLE)
        // ->columns(self::TABLE.'.*', UserModel::TABLE.'.username AS owner_username', UserModel::TABLE.'.name AS owner_name')
        // ->eq(self::TABLE.'.id', $project_id)
        // ->join(UserModel::TABLE, 'id', 'owner_id')
        // ->findOne();
    }

    public function reorderPagesByIndex($project_id, $src_wiki_id, $index, $parent_id){
        // echo "project_id: " . $project_id . " src_wiki_id: " . $src_wiki_id . " index: " . $index . " parent_id: " . $parent_id . " <br>" . PHP_EOL;

        // retrieve src wiki page and wiki pages by parent
        $wikiPageSrc = $this->getWikipage($src_wiki_id);
        if (empty($wikiPageSrc) || (int) $wikiPageSrc['project_id'] !== (int) $project_id) {
            return false;
        }
        if ($parent_id !== null) {
            $parentPage = $this->getWikipage($parent_id);
            if (empty($parentPage) || (int) $parentPage['project_id'] !== (int) $project_id) {
                return false;
            }
        }

        $wikiPages = $this->getWikiPagesByParentId($project_id, $parent_id);
        // echo "count list: " . count($wikiPages) . " <br>" . PHP_EOL;
        // print_r($wikiPages);

        // if the new parent list is empty add src_wiki_id as first subpage
        if (empty($wikiPages)) {
            // echo "updating wikipage ". $src_wiki_id ." column to ". $orderColumn . " for parent " . $parent_id . " <br>" . PHP_EOL;
            return $this->savePagePosition($src_wiki_id, 1, $parent_id);
        }

        // determine what needs to change
        $orderColumn = 1;
        $changeIndex = 0;
        $oldIndex = $wikiPageSrc['ordercolumn'];
        $changeParent = ($parent_id != $wikiPageSrc['parent_id']);
        if ($changeParent || $index < $oldIndex) {
            $changeIndex = 1;
        }
        if (!$changeParent && $index > $oldIndex) {
            $changeIndex = -1;
        }
        if (!$changeParent && $changeIndex == 0) {
            return true; // nothing changes !!!
        }

        // shift the order of affected subpages in the target parent
        foreach ($wikiPages as $wikipage) {
            $id = $wikipage['id'];
            // echo "id: " . $id . " oldIndex: " . $oldIndex . " index: " . $index . " => orderColumn: " . $orderColumn . " <br>" . PHP_EOL;
            if ($id != $src_wiki_id) {
                if ($changeIndex > 0 && $orderColumn == $index) {
                    $orderColumn++;
                }
                if ($changeIndex < 0 && $orderColumn - 1 == $oldIndex) {
                    $orderColumn--;
                    $changeIndex = 1;
                }

                if ($orderColumn != $wikipage['ordercolumn']) {
                    // echo "updating " . $id . " column to " . $orderColumn . " for parent " . ($wikipage['parent_id'] ?? null) . " <br>" . PHP_EOL;
                    if(!$this->savePagePosition($id, $orderColumn, $wikipage['parent_id'] ?? null)) {
                        return false;
                    }
                }
            }
            $orderColumn++;
        }

        // update moved src
        // echo "updating src " . $src_wiki_id . " column to " . $index . " <br>" . PHP_EOL;
        return $this->savePagePosition($src_wiki_id, $index, $parent_id ?? null);
    }

    public function reorderPages($project_id, $src_wiki_id, $target_wiki_id){
        // echo "project_id: " . $project_id . " src_wiki_id: " . $src_wiki_id . " target_wiki_id: " . $target_wiki_id . " <br>" . PHP_EOL;

        // retrieve src/trg wiki pages
        $wikiPageSrc = $this->getWikipage($src_wiki_id);
        $wikiPageTrg = $this->getWikipage($target_wiki_id);
        if (
            empty($wikiPageSrc) ||
            empty($wikiPageTrg) ||
            (int) $wikiPageSrc['project_id'] !== (int) $project_id ||
            (int) $wikiPageTrg['project_id'] !== (int) $project_id
        ) {
            return false;
        }


        // ensure both wiki pages are under the same parent
        if ($wikiPageSrc['parent_id'] != $wikiPageTrg['parent_id']) {
            return false;
        }

        // retrieve wiki pages by parent
        $wikiPages = $this->getWikiPagesByParentId($project_id, $wikiPageSrc['parent_id']);
        // echo "count list: " . count($wikiPages) . " <br>" . PHP_EOL;
        // print_r($wikiPages);

        $orderColumn = 0;
        $targetColumn = 0;
        $oldColumn = $wikiPageSrc['ordercolumn'];
        // shift the order of affected subpages
        foreach ($wikiPages as $wikipage) {
            $orderColumn++;
            $id = $wikipage['id'];
            if($id == $target_wiki_id){
                if ($orderColumn != $wikipage['ordercolumn']) {
                    // echo "updating " . $id . " column to " . $orderColumn . " <br>" . PHP_EOL;
                    if(!$this->savePagePosition($id, $orderColumn, $wikiPageSrc['parent_id'] ?? null)) {
                        return false;
                    }
                }
                $orderColumn++;
                $targetColumn = $orderColumn;
            }
            // echo "id: " . $id . " oldColumn: " . $oldColumn . " orderColumn: " . $orderColumn . " => targetColumn: " . $targetColumn . " <br>" . PHP_EOL;
            if ($id == $src_wiki_id) {
                $orderColumn--;
                $targetColumn--;
            } else {
                if ($orderColumn != $wikipage['ordercolumn']) {
                    // echo "updating " . $id . " column to " . $orderColumn . " <br>" . PHP_EOL;
                    if(!$this->savePagePosition($id, $orderColumn, $wikiPageSrc['parent_id'] ?? null)) {
                        return false;
                    }
                }
            }
        }

        // update moved src
        if ($oldColumn != $targetColumn) {
            // echo "updating src " . $src_wiki_id . " column to " . $targetColumn . " <br>" . PHP_EOL;
            if(!$this->savePagePosition($src_wiki_id, $targetColumn, $wikiPageSrc['parent_id'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * update page position and parent id
     * @param mixed $wiki_id
     * @param mixed $orderColumn
     * @param mixed $parent_id
     * @return bool
     */
    public function savePagePosition($wiki_id, $orderColumn, $parent_id) {
        $result = $this->db->table(self::WIKITABLE)->eq('id', $wiki_id)->update(array(
            'ordercolumn' => $orderColumn,
            'parent_id' => $parent_id
        ));

        if (!$result) {
            $this->db->cancelTransaction();
            return false;
        }

        return true;
    }



    /**
     * Get query for list of all wikis without column statistics
     *
     * @access public
     * @param  array $projectIds
     * @return \PicoDb\Table
     */
    public function getQueryByProjectIds(array $projectIds)
    {
        if (empty($projectIds)) {
            return $this->db->table(self::WIKITABLE)->eq(self::WIKITABLE.'.project_id', 0);
        }

        return $this->db->
            table(self::WIKITABLE)
            ->columns(
                'c.name as creator_name',
                'c.username as creator_username',
                'mod.name as modifier_name',
                'mod.username as modifier_username',
                self::WIKITABLE . '.id',
                self::WIKITABLE . '.title',
                self::WIKITABLE . '.content',
                self::WIKITABLE . '.project_id',
                self::WIKITABLE . '.is_active',
                self::WIKITABLE . '.ordercolumn',
                self::WIKITABLE . '.creator_id',
                self::WIKITABLE . '.date_creation',
                self::WIKITABLE . '.date_modification',
                self::WIKITABLE . '.editions',
                self::WIKITABLE . '.current_edition',
                self::WIKITABLE . '.modifier_id'
            )
            ->left(UserModel::TABLE, 'c', 'id', self::WIKITABLE, 'creator_id')
            ->subquery(self::EDITION_COUNT_SQL, 'edition_count')
            ->left(UserModel::TABLE, 'mod', 'id', self::WIKITABLE, 'modifier_id')
            ->in(self::WIKITABLE.'.project_id', $projectIds)
            ->eq(self::WIKITABLE.'.is_active', 1);
    }

    /**
     * Get a single Wiki Page
     *
     * @access public
     * @param  integer   $project_id
     * @return array
     */
    public function getWikipage($wiki_id)
    {
        return $this->db->
            table(self::WIKITABLE)
            ->columns(
                // 'c.name as creator_name',
                // 'c.username as creator_username',
                // UserModel::TABLE . '.name as creator_name',
                // UserModel::TABLE . '.username as creator_username',
                // 'mod.name as modifier_name',
                // 'mod.username as modifier_username',
                // UserModel::TABLE . '.username as modifier_username',
                self::WIKITABLE . '.id',
                self::WIKITABLE . '.title',
                self::WIKITABLE . '.parent_id',
                self::WIKITABLE . '.content',
                self::WIKITABLE . '.project_id',
                self::WIKITABLE . '.ordercolumn',
                self::WIKITABLE . '.is_active',
                self::WIKITABLE . '.creator_id',
                self::WIKITABLE . '.date_creation',
                self::WIKITABLE . '.date_modification',
                self::WIKITABLE . '.editions',
                self::WIKITABLE . '.current_edition',
                self::WIKITABLE . '.modifier_id'
            )
        // ->left(UserModel::TABLE, 'c', 'id', self::WIKITABLE, 'creator_id')
        // ->left(UserModel::TABLE, 'mod', 'id', self::WIKITABLE, 'modifier_id')
        // ->eq('wiki_id', $wiki_id)->findOne();
            ->eq('id', $wiki_id)->findOne(); // this may possibly not support joins
        // ->desc('order')->findAll();

        // return $this->db->table(self::TABLE)
        // ->columns(self::TABLE.'.*', UserModel::TABLE.'.username AS owner_username', UserModel::TABLE.'.name AS owner_name')
        // ->eq(self::TABLE.'.id', $project_id)
        // ->join(UserModel::TABLE, 'id', 'owner_id')
        // ->findOne();
    }

    public function getWiki()
    {
        $project_id = $this->request->getIntegerParam('project_id');
        $wikipage = $this->getWikipage($this->request->getIntegerParam('wiki_id'));
        // $wikipage = $this->wikiFinderModel->getDetails($this->request->getIntegerParam('wiki_id'));

        if (empty($wikipage)) {
            throw new PageNotFoundException();
        }

        if ($project_id != $wikipage['project_id']) {
            throw new AccessForbiddenException();
        }

        return $wikipage;
    }

    private function getLastOrderPosition($project_id)
    {
        $lastPosition = $this->db->table(self::WIKITABLE)
            ->eq('project_id', $project_id)
            ->desc('ordercolumn')
            ->findOneColumn('ordercolumn');

        if (empty($lastPosition)) {
            $lastPosition = 0;
        }

        return $lastPosition;
    }

    /**
     * Add a new wikipage into the database
     *
     * @access public
     * @param  integer   $project_id
     * @param  float     $amount
     * @param  string    $comment
     * @param  string    $date
     * @return boolean|integer
     */
    // , $date = ''
    // $values, $editions, $newDate
    public function updatepage($paramvalues, $editions, $date = '')
    {

        // $this->prepare($values);
        $values = [
            'title' => $paramvalues['title'],
            'editions' => $editions,
            'content' => $paramvalues['content'],
            'current_edition' => $editions,
            'date_modification' => $date ?: date('Y-m-d'),
            // 'parent_id' => $paramvalues['parent_id']
        ];

        if(isset($paramvalues['parent_id']) && $paramvalues['parent_id'] != '') {
            $values['parent_id'] = $paramvalues['parent_id'];
        } else {
            $values['parent_id'] = null;
        }

        // if parent changes, then force change the ordercolumn
        $wikipage = $this->getWikipage($paramvalues['id']);
        if ($wikipage['parent_id'] != $values['parent_id']) {
            $values['ordercolumn'] = $this->getLastOrderPosition($wikipage['project_id']) + 1;
        }

        if ($this->userSession->isLogged()) {
            $values['modifier_id'] = $this->userSession->getId();
        }

        $wikiEventJob = new WikiEventJob($this->container);
        $wikiEventJob->executeWithId($paramvalues['id'], self::EVENT_UPDATE);
        // $wikiEventJob = new WikiEventJob($this->container);
        // $wikiEventJob->execute($paramvalues['title'], $paramvalues['project_id'], $values, self::EVENT_UPDATE);
        $this->db->table(self::WIKITABLE)->eq('id', $paramvalues['id'])->update($values);

        return (int) $paramvalues['id'];

        // need to also save to editions
    }

    /**
     * Add a new wikipage into the database
     *
     * @access public
     * @param  integer   $project_id
     * @param  float     $amount
     * @param  string    $comment
     * @param  string    $date
     * @return boolean|integer
     */
    // , $date = ''
    public function createpage($project_id, $title, $content, $date = '', $order = null)
    {
        // $this->prepare($values);
        $values = array(
            'project_id' => $project_id,
            'title' => $title,
            'content' => $content,
            'date_creation' => $date ?: date('Y-m-d'),
            'ordercolumn' => $this->getLastOrderPosition($project_id) + 1,
        );
        $this->prepare($values);

        // $values['creator_id'] = $this->userSession->getId();
        //     $values['modifier_id'] = $this->userSession->getId();
        // date_modification
        // TODO notification
        $wikiEventJob = new WikiEventJob($this->container);
        $wikiEventJob->execute($title, $project_id, $values, self::EVENT_CREATE);

        return $this->db->table(self::WIKITABLE)->persist($values);

        // need to also save to editions
    }

    /**
     * Save an edition of a wiki page (when edition saving is on and the title or content changed)
     *
     * @access public
     * @param  array     $values   title and content
     * @param  integer   $wiki_id
     * @param  integer   $edition
     * @param  string    $date     unused, editions are stamped with the current time
     * @param  array     $before   the page before this save, so edits made while saving was off are kept
     * @return boolean|null
     */
    public function createEdition($values, $wiki_id, $edition, $date = '', $before = null)
    {
        $actor = $this->userSession->isLogged() ? (int) $this->userSession->getId() : 0;
        $saved = $this->wikiEditionStore->record((int) $wiki_id, (int) $edition, (string) $values['title'], (string) $values['content'], $actor, $before ?: null);

        return $saved ?: null;
    }

    /**
     * Prepare data
     *
     * @access protected
     * @param  array    $values    Form values
     */
    protected function prepare(array &$values)
    {
        // $values = $this->dateParser->convert($values, array('date_due'), true);
        // $values = $this->dateParser->convert($values, array('date_started'), true);

        // $this->helper->model->removeFields($values, array('another_wiki', 'duplicate_multiple_projects'));
        // $this->helper->model->resetFields($values, array('creator_id', 'owner_id', 'date_due', 'date_started', 'score', 'category_id', 'time_estimated', 'time_spent'));

        // if (empty($values['column_id'])) {
        //     $values['column_id'] = $this->columnModel->getFirstColumnId($values['project_id']);
        // }

        // if (empty($values['color_id'])) {
        //     $values['color_id'] = $this->colorModel->getDefaultColor();
        // }

        if (empty($values['title'])) {
            $values['title'] = t('Untitled');
        }

        if ($this->userSession->isLogged()) {
            $values['creator_id'] = $this->userSession->getId();
            $values['modifier_id'] = $this->userSession->getId();
        }

        // $values['swimlane_id'] = empty($values['swimlane_id']) ? $this->swimlaneModel->getFirstActiveSwimlaneId($values['project_id']) : $values['swimlane_id'];
        if (empty($values['date_creation'])) {
            $values['date_creation'] = time();
        }

        if (empty($values['date_modification'])) {
            $values['date_modification'] = $values['date_creation'];
        }
        // $values['order'] = $this->wikiFinderModel->countByColumnAndSwimlaneId($values['project_id'], $values['column_id'], $values['swimlane_id']) + 1;

        $this->hook->reference('model:wikipage:creation:prepare', $values);
    }

    // $this->prepare($values);

    public function validatePageCreation(array $values)
    {
        $v = new Validator($values, array(
            new Validators\Required('project_id', t('Field required')),
            new Validators\Required('title', t('Field required')),
        ));

        return array(
            $v->execute(),
            $v->getErrors(),
        );
    }

    public function validatePageUpdate(array $values)
    {
        $v = new Validator($values, array(
            new Validators\Required('title', t('Field required')),
        ));

        return array(
            $v->execute(),
            $v->getErrors(),
        );
    }

    /**
     * Remove a specific wiki page
     *
     * @access public
     * @param  integer    $wiki_id
     * @return boolean
     */
    public function removepage($wiki_id)
    {
        $wikiEventJob = new WikiEventJob($this->container);
        $wikiEventJob->executeWithId($wiki_id, self::EVENT_DELETE);
        return $this->db->table(self::WIKITABLE)->eq('id', $wiki_id)->remove();
    }

    /**
     * restore a specific edition
     *
     * @access public
     * @param  integer    $wiki_id
     * @param  integer    $edition
     * @return boolean
     */
    public function restoreEdition($wiki_id, $edition)
    {
        $wikipage = $this->getWikipage($wiki_id);
        $target = $this->wikiEditionStore->get((int) $wiki_id, (int) $edition);
        if (empty($wikipage) || $target === null) {
            return false;
        }

        if ($target['title'] === $wikipage['title'] && $target['content'] === $wikipage['content']) {
            return true; // already the current text
        }

        // like a git revert: the restored text becomes a new edition
        $newEdition = $this->wikiEditionStore->nextEdition((int) $wiki_id, (int) $wikipage['editions']);
        $values = [
            'title' => $target['title'],
            'content' => $target['content'],
            'editions' => $newEdition,
            'current_edition' => $newEdition,
            'date_modification' => date('Y-m-d'),
        ];

        if ($this->userSession->isLogged()) {
            $values['modifier_id'] = $this->userSession->getId();
        }

        if (! $this->db->table(self::WIKITABLE)->eq('id', $wiki_id)->update($values)) {
            return false;
        }
        $this->createEdition($values, $wiki_id, $newEdition, '', $wikipage);

        return true;
    }

    /**
     * purge a specific edition
     *
     * @access public
     * @param  integer    $wiki_id
     * @param  integer    $edition
     * @return boolean
     */
    public function purgeEdition($wiki_id, $edition)
    {
        return $this->wikiEditionStore->purge((int) $wiki_id, (int) $edition);
    }
}
