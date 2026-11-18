<?php

namespace Kanboard\Plugin\Wiki\Controller;

use Kanboard\Core\Controller\AccessForbiddenException;

/**
 * Serves attachments of public wiki pages to anonymous visitors.
 * Every request needs the project's public token; see WikiFileModel::getPublicFile().
 */
class WikiPublicFileController extends WikiFileViewController
{
    /**
     * Get the requested file, only if it may be shown on a public wiki page
     *
     * @access protected
     * @return array
     */
    protected function getWikiFile()
    {
        $file = $this->wikiFileModel->getPublicFile(
            $this->request->getIntegerParam('file_id'),
            $this->request->getStringParam('token')
        );

        if (empty($file)) {
            throw AccessForbiddenException::getInstance()->withoutLayout();
        }

        return $file;
    }

    /**
     * The preview popover is only for logged in users
     *
     * @access public
     */
    public function show()
    {
        throw AccessForbiddenException::getInstance()->withoutLayout();
    }
}
