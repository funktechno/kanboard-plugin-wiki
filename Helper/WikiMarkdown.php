<?php

namespace Kanboard\Plugin\Wiki\Helper;

use Kanboard\Core\Markdown;
use Pimple\Container;

/**
 * Kanboard's markdown parser plus wiki references:
 *   wiki:<id>                  link to a wiki page, titled with the page title (like #<id> for tasks)
 *   [text](wiki:<id>)          link to a wiki page
 *   [text](wiki-file:<id>)     link to a wiki attachment (download)
 *   ![alt](wiki-file:<id>)     embedded wiki image attachment
 *
 * A reference only becomes a link or image when the viewer may open it; otherwise only the text
 * (or the image's alt text) is kept, so IDs are not exposed. Everything else (safe mode, #task and
 * @user links) is core behavior.
 */
class WikiMarkdown extends Markdown
{
    private $wikiContainer;
    private $wikiIsPublicLink;
    private $wikiUrlCache = array();
    private $inImage = false;

    public function __construct(Container $container, $isPublicLink)
    {
        parent::__construct($container, $isPublicLink);
        $this->wikiContainer = $container;
        $this->wikiIsPublicLink = $isPublicLink;
        $this->InlineTypes['w'][] = 'WikiReference';
        $this->inlineMarkerList .= 'w';
    }

    protected function inlineWikiReference($excerpt)
    {
        // only a whole word: at the start, or after a space or an opening bracket
        $position = strlen($excerpt['context']) - strlen($excerpt['text']);
        $previous = $position > 0 ? $excerpt['context'][$position - 1] : '';

        if (($previous !== '' && ! ctype_space($previous) && $previous !== '(') || ! preg_match('/^wiki:(\d+)(?![\w-])/', $excerpt['text'], $matches)) {
            return null;
        }

        $url = $this->getUrl('page', (int) $matches[1]);

        if ($url === '') {
            return null;
        }

        $page = $this->wikiContainer['wikiModel']->getWikipage((int) $matches[1]);

        return array(
            'extent' => strlen($matches[0]),
            'element' => array(
                'name' => 'a',
                'text' => $page['title'],
                'attributes' => array('href' => $url, 'class' => 'wiki-link'),
            ),
        );
    }

    protected function inlineImage($excerpt)
    {
        // parent::inlineImage() goes through inlineLink(); keep its href raw for the check below
        $this->inImage = true;
        $inline = parent::inlineImage($excerpt);
        $this->inImage = false;

        if (! is_array($inline) || ! preg_match('/^wiki-file:(\d+)$/', (string) $inline['element']['attributes']['src'], $matches)) {
            return $inline;
        }

        $url = $this->getUrl('file', (int) $matches[1], 'image');

        if ($url !== '') {
            $inline['element']['attributes']['src'] = $url;
        } else {
            $inline['element'] = array('name' => 'span', 'text' => $inline['element']['attributes']['alt']);
        }

        return $inline;
    }

    protected function inlineLink($excerpt)
    {
        $inline = parent::inlineLink($excerpt);

        if (is_array($inline)) {
            $inline['element']['nonNestables'][] = 'WikiReference';
        }

        if ($this->inImage || ! is_array($inline) || ! isset($inline['element']['attributes']['href'])) {
            return $inline;
        }

        if (preg_match('/^wiki:(\d+)$/', $inline['element']['attributes']['href'], $matches)) {
            $url = $this->getUrl('page', (int) $matches[1]);
        } elseif (preg_match('/^wiki-file:(\d+)$/', $inline['element']['attributes']['href'], $matches)) {
            $url = $this->getUrl('file', (int) $matches[1], 'download');
        } else {
            return $inline;
        }

        if ($url !== '') {
            $inline['element']['attributes']['href'] = $url;
        } else {
            $inline['element']['name'] = 'span';
            unset($inline['element']['attributes']['href']);
        }

        return $inline;
    }

    /**
     * URL of a wiki page or attachment for the current viewer, or '' when they may not open it
     */
    private function getUrl($type, $id, $action = '')
    {
        $key = $type . ':' . $id . ':' . $action;

        if (! isset($this->wikiUrlCache[$key])) {
            $this->wikiUrlCache[$key] = $type === 'page' ? $this->resolvePageUrl($id) : $this->resolveFileUrl($id, $action);
        }

        return $this->wikiUrlCache[$key];
    }

    private function resolvePageUrl($wiki_id)
    {
        $wikiModel = $this->wikiContainer['wikiModel'];
        $helper = $this->wikiContainer['helper'];
        $page = $wikiModel->getWikipage($wiki_id);

        if (empty($page) || empty($page['is_active'])) {
            return '';
        }

        if ($this->wikiIsPublicLink) {
            $project = $this->wikiContainer['projectModel']->getById($page['project_id']);

            if (! $wikiModel->isPublicWikiEnabled() || empty($project['is_public']) || empty($project['token'])) {
                return '';
            }

            return $helper->url->to('WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $wiki_id), '', true);
        }

        if (! $helper->user->hasProjectAccess('WikiController', 'detail', $page['project_id'])) {
            return '';
        }

        return $helper->url->to('WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $page['project_id'], 'wiki_id' => $wiki_id));
    }

    private function resolveFileUrl($file_id, $action)
    {
        $wikiFileModel = $this->wikiContainer['wikiFileModel'];
        $helper = $this->wikiContainer['helper'];
        $file = $wikiFileModel->getById($file_id);

        if (empty($file) || ($action === 'image' && empty($file['is_image']))) {
            return '';
        }

        $page = $this->wikiContainer['wikiModel']->getWikipage($file['wikipage_id']);

        if (empty($page) || empty($page['is_active'])) {
            return '';
        }

        if ($this->wikiIsPublicLink) {
            $project = $this->wikiContainer['projectModel']->getById($page['project_id']);

            if (empty($project['token']) || $wikiFileModel->getPublicFile($file_id, $project['token']) === null) {
                return '';
            }

            return $helper->url->to('WikiPublicFileController', $action, array('plugin' => 'wiki', 'token' => $project['token'], 'file_id' => $file_id), '', true);
        }

        if (! $helper->user->hasProjectAccess('WikiFileViewController', 'show', $page['project_id'])) {
            return '';
        }

        return $helper->url->to('WikiFileViewController', $action, array('plugin' => 'wiki', 'file_id' => $file_id));
    }
}
