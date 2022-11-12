<?php

namespace Kanboard\Plugin\Wiki\Model;

use Kanboard\Job\BaseJob;
// use Kanboard\EventBuilder\CommentEventBuilder;
// use Kanboard\Model\CommentModel;

/**
 * Class WikiEventJob
 *
 * @package Kanboard\Job
 */
class WikiEventJob extends BaseJob
{
    /**
     * Set job params
     *
     * @param  int    $wikiPageId
     * @param  string $eventName
     * @return $this
     */
    public function withParams($wikiPageId, $eventName)
    {
        $this->jobParams = array($wikiPageId, $eventName);
        return $this;
    }

    /**
     * Execute job
     *
     * @param  int    $wikiPageId
     * @param  string $eventName
     */
    public function execute($title, $projectId, $wikiPage, $eventName)
    {
        $event = WikiEventBuilder::getInstance($this->container)
            ->withTitle($title, $projectId)
            ->buildEventWiki($wikiPage);

        if ($event !== null) {
            $this->dispatcher->dispatch($eventName, $event);

            // if ($eventName === Wiki::EVENT_CREATE) {
            //     $userMentionJob = $this->userMentionJob->withParams($event['comment']['comment'], Wiki::EVENT_USER_MENTION, $event);
            //     $this->queueManager->push($userMentionJob);
            // }
        }
    }

    public function executeWithId($wikiPageId, $eventName)
    {
        $event = WikiEventBuilder::getInstance($this->container)
            ->withPageId($wikiPageId)
            ->buildEvent();

        if ($event !== null) {
            $this->dispatcher->dispatch($eventName, $event);

            // if ($eventName === Wiki::EVENT_CREATE) {
            //     $userMentionJob = $this->userMentionJob->withParams($event['comment']['comment'], Wiki::EVENT_USER_MENTION, $event);
            //     $this->queueManager->push($userMentionJob);
            // }
        }
    }
}
