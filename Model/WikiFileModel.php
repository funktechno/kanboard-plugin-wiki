<?php

namespace Kanboard\Plugin\Wiki\Model;

use Kanboard\Model\FileModel;

/**
 * Wiki File Model
 *
 * @package  Kanboard\Model
 * @author   Frederic Guillot
 */
class WikiFileModel extends FileModel
{
    /**
     * Table name
     *
     * @var string
     */
    const TABLE = 'wikipage_has_files';

    /**
     * Events
     *
     * @var string
     */
    const EVENT_CREATE = 'wiki.file.create';


    /**
     * Get the table
     *
     * @abstract
     * @access protected
     * @return string
     */
    protected function getTable()
    {
        return self::TABLE;
    }


    /**
     * Define the foreign key
     *
     * @abstract
     * @access protected
     * @return string
     */
    protected function getForeignKey()
    {
        return 'wikipage_id';
    }

    /**
     * Define the path prefix
     *
     * @abstract
     * @access protected
     * @return string
     */
    protected function getPathPrefix()
    {
        return 'wikipages';
    }

    /**
     * Get projectId from fileId
     *
     * @access public
     * @param  integer $file_id
     * @return integer
     */
    public function getProjectId($file_id)
    {
        return $this->db
            ->table(self::TABLE)
            ->eq(self::TABLE.'.id', $file_id)
            ->join(WikiModel::WIKITABLE, 'id', 'wikipage_id')
            ->findOneColumn(WikiModel::WIKITABLE . '.project_id') ?: 0;
    }

    /**
     * Get a file for a public wiki page, or null when it may not be shown there.
     * The token must belong to a public project that owns the file's (active) page,
     * and public wiki attachments must be enabled.
     *
     * @access public
     * @param  integer $file_id
     * @param  string  $token   Public project token
     * @return array|null
     */
    public function getPublicFile($file_id, $token)
    {
        if (empty($token) || ! $this->wikiModel->isPublicAttachmentsEnabled()) {
            return null;
        }

        $project = $this->projectModel->getByToken($token);
        $file = $this->getById($file_id);

        if (empty($project) || empty($file)) {
            return null;
        }

        $wikipage = $this->wikiModel->getWikipage($file['wikipage_id']);

        if (empty($wikipage) || empty($wikipage['is_active']) || $wikipage['project_id'] != $project['id']) {
            return null;
        }

        return $file;
    }

    /**
     * Handle screenshot upload
     *
     * @access public
     * @param  integer  $wiki_id      Wiki id
     * @param  string   $blob         Base64 encoded image
     * @return bool|integer
     */
    public function uploadScreenshot($wiki_id, $blob)
    {
        $original_filename = e('Screenshot taken %s', $this->helper->dt->datetime(time())).'.png';
        return $this->uploadContent($wiki_id, $original_filename, $blob);
    }

    /**
     * Fire file creation event
     *
     * @access protected
     * @param  integer $file_id
     */
    protected function fireCreationEvent($file_id)
    {
        return null;
    }

    protected function fireDestructionEvent($file_id)
    {
        return null;
    }
}
