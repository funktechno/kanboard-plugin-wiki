<?php

namespace Kanboard\Plugin\Wiki\Helper;

use Kanboard\Helper\TextHelper;
use ParsedownExtra;

class MarkdownHelper extends TextHelper
{
    /**
     * Markdown transformation
     *
     * @param  string    $text
     * @param  boolean   $isPublicLink
     * @return string
     */
    public function markdown($text, $isPublicLink = false)
    {
        $parser = new WikiMarkdown($this->container, $isPublicLink);
        $parser->setMarkupEscaped(MARKDOWN_ESCAPE_HTML);
        $parser->setBreaksEnabled(true);
        $parser->setContainer($this->container);

        return $parser->text($text ?: '');
    }
}
