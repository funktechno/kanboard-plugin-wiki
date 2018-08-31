<?php

namespace Kanboard\Plugin\Wiki\Model;

use Kanboard\Core\Base;
// use Kanboard\Model\WikiModel;
use Kanboard\Model\UserModel;
use SimpleValidator\Validator;
use SimpleValidator\Validators;

/**
 * Wiki
 *
 * @package  model
 * @author   Frederic Guillot
 */
class Wiki extends Base
{
    /**
     * SQL table name
     *
     * @var string
     */
    const WIKI_EDITION_TABLE = 'wikipage_editions';

    /**
     * Get all of a Wikipages'edition by edition
     *
     * @access public
     * @param  integer   $project_id
     * @return array
     */
    public function getEditions($wiki_id)
    {
        return $this->db->table(self::WIKI_EDITION_TABLE)->eq('wikipage_id', $wiki_id)->desc('edition')->findAll();
    }

    /**
     * SQL table name
     *
     * @var string
     */
    const WIKITABLE = 'wikipage';
    /**
     * Get all Wiki Pages by order for a project
     *
     * @access public
     * @param  integer   $project_id
     * @return array
     */
    public function getWikipages($project_id)
    {
        return $this->db->
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
            ->asc('ordercolumn')->findAll();

        // return $this->db->table(self::TABLE)
        // ->columns(self::TABLE.'.*', UserModel::TABLE.'.username AS owner_username', UserModel::TABLE.'.name AS owner_name')
        // ->eq(self::TABLE.'.id', $project_id)
        // ->join(UserModel::TABLE, 'id', 'owner_id')
        // ->findOne();
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
            ->left(UserModel::TABLE, 'mod', 'id', self::WIKITABLE, 'modifier_id')
            ->in(self::WIKITABLE.'.project_id', $projectIds);
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

        if ($project_id !== 0 && $project_id != $wikipage['project_id']) {
            throw new AccessForbiddenException();
        }

        return $wikipage;
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
        ];

        if ($this->userSession->isLogged()) {
            $values['modifier_id'] = $this->userSession->getId();
        }
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
            'ordercolumn' => $order ?: time(),
        );
        $this->prepare($values);

        // $values['creator_id'] = $this->userSession->getId();
        //     $values['modifier_id'] = $this->userSession->getId();
        // date_modification

        return $this->db->table(self::WIKITABLE)->persist($values);

        // need to also save to editions
    }

    const EDITIONTABLE = 'wikipage_editions';

    /**
     * save an edition of a wiki page on every save or creation
     *
     * @access public
     * @param  integer   $project_id
     * @param  float     $amount
     * @param  string    $comment
     * @param  string    $date
     * @return boolean|integer
     */
    // , $date = ''
    public function createEdition($values, $wiki_id, $edition, $date='')
    {

        $persistEditions = $this->configModel->get('persistEditions');

        if ($persistEditions == 1) {

            $editionvalues = array(
                'title' => $values['title'],
                'edition' => $edition,
                'content' => $values['content'],
                'date_creation' => $date ?: date('Y-m-d'), // should alway be the last date
                // 'creator_id' => $this->userSession->getId(),
                'wikipage_id' => $wiki_id,
            );

            if ($this->userSession->isLogged()) {
                $editionvalues['creator_id'] = $this->userSession->getId();
            }

            // $values['creator_id'] = $this->userSession->getId();
            //     $values['modifier_id'] = $this->userSession->getId();
            // date_modification

            return $this->db->table(self::EDITIONTABLE)->persist($editionvalues);
        } else {
            return null;
        }

        // need to also save to editions
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

        $date = date('Y-m-d');
        $editionvalues = $this->db->
            table(self::WIKI_EDITION_TABLE)
            ->eq('edition', $edition)
            ->eq('wikipage_id', $wiki_id)
            ->findOne(); // this may possibly not support joins

        // $values = array(
        //     'title' => $editionvalues['title'],
        //     'current_edition' => $edition,
        //     'content' => $editionvalues['title'],
        //     'date_modification' => $date ?: date('Y-m-d'),
        //     'modifier_id' => $this->userSession->getId(),
        // );

        $values = [
            'title' => $editionvalues['title'],
            'current_edition' => $edition,
            'content' => $editionvalues['content'],
            'date_modification' => $date ?: date('Y-m-d'),
            'modifier_id' => $this->userSession->getId(),
        ];

        if ($this->userSession->isLogged()) {
            $values['modifier_id'] = $this->userSession->getId();
        }

        // return $this->db->table(self::WIKITABLE)->eq('id', $wiki_id)->remove();
        return $this->db->table(self::WIKITABLE)->eq('id', $wiki_id)->update($values);

    }
}
