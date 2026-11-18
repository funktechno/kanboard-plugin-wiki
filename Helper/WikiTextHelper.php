<?php

namespace Kanboard\Plugin\Wiki\Helper;

use Kanboard\Helper\TextHelper;

/**
 * Kanboard's text helper with wiki links in markdown. Only markdown() changes, and it keeps
 * every core parser setting.
 */
class WikiTextHelper extends TextHelper
{
    public function markdown($text, $isPublicLink = false)
    {
        $parser = new WikiMarkdown($this->container, $isPublicLink);
        $parser->setSafeMode(true);
        $parser->setMarkupEscaped(MARKDOWN_ESCAPE_HTML);
        $parser->setBreaksEnabled(true);
        return $parser->text($text ?: '');
    }
}
