<?php

namespace Kanboard\Plugin\Wiki\Model;

use Kanboard\EventBuilder\BaseEventBuilder;
use Kanboard\Event\CommentEvent;

/**
 * Class WikiEventBuilder
 *
 * @package Kanboard\EventBuilder
 */
class WikiEventBuilder extends BaseEventBuilder
{
    protected $wiki_id = 0;

    protected $title = "";

    protected $projectId = 0;

    /**
     * Set wiki_id
     *
     * @param  int $wiki_id
     * @return $this
     */
    public function withPageId($wiki_id)
    {
        $this->wiki_id = $wiki_id;
        return $this;
    }
    /**
     * @param  string $title
     * @param  int $projectId
     * @return $this
     */
    public function withTitle($title, $projectId)
    {
        $this->title = $title;
        $this->projectId = $projectId;
        return $this;
    }

    /**
     * Build event data
     *
     * @access public
     * @return CommentEvent|null
     */
    public function buildEvent()
    {
        $wikiPage = $this->wiki->getWikipage($this->wiki_id);

        if (empty($wikiPage)) {
            return null;
        }

        return new CommentEvent(array(
            'wiki' => $wikiPage,
            'project' => $this->projectModel->getById($wikiPage['project_id']),
        ));
    }

    public function buildEventWiki($wikiPage)
    {
        if (empty($wikiPage)) {
            return null;
        }

        return new CommentEvent(array(
            'wiki' => $wikiPage,
            'project' => $this->projectModel->getById($wikiPage['project_id']),
        ));
    }

    /**
     * Get event title with author
     *
     * @access public
     * @param  string $author
     * @param  string $eventName
     * @param  array  $eventData
     * @return string
     */
    public function buildTitleWithAuthor($author, $eventName, array $eventData)
    {
        switch ($eventName) {
            case Wiki::EVENT_UPDATE:
                return e('%s updated a wikipage on the project #%d', $author, $eventData['project']['id']);
            case Wiki::EVENT_CREATE:
                return e('%s created a wikipage on the project #%d', $author, $eventData['project']['id']);
            case Wiki::EVENT_DELETE:
                return e('%s removed a wikipage on the project #%d', $author, $eventData['project']['id']);
            default:
                return '';
        }
    }

    /**
     * Get event title without author
     *
     * @access public
     * @param  string $eventName
     * @param  array  $eventData
     * @return string
     */
    public function buildTitleWithoutAuthor($eventName, array $eventData)
    {
        switch ($eventName) {
            case Wiki::EVENT_CREATE:
                return e('New wikipage on project #%d', $eventData['project']['task_id']);
            case Wiki::EVENT_UPDATE:
                return e('wikipage updated on project #%d', $eventData['project']['task_id']);
            case Wiki::EVENT_DELETE:
                return e('wikipage removed on project #%d', $eventData['project']['task_id']);
            default:
                return '';
        }
    }
}
