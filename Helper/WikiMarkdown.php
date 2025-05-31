<?php

namespace Kanboard\Plugin\Wiki\Helper;

use Kanboard\Core\Markdown;
use Kanboard\Helper\TextHelper;
use Kanboard\Helper\UrlHelper;

class WikiMarkdown extends Markdown
{
    public function setContainer($container)
    {
        $this->container = $container;
    }
    protected function inlineLink($Excerpt)
    {
        $retval = parent::inlineLink($Excerpt);
        if (!is_array($retval)) {
            return $retval;
        }

        $href = $retval['element']['attributes']['href'];
        if (!is_string($href))
        {
            return $retval;
        }

        echo $href;
        if (!preg_match('!^wiki:(\d+)$!', $href, $matches))
        {
            echo "Hello";
            return $retval;
        }

        $wiki_id = $matches[1];

        $href = $this->container['helper']->url->to(
            'WikiController',
            'detail',
            array(
                'plugin' => 'wiki',
                'wiki_id' => $wiki_id,
                'project_id' => $this->container['helper']->url->request->getIntegerParam('project_id')
            ),
            'wiki'
        );
        $retval['element']['attributes']['href'] = $href;
        return $retval;
    }   
}
