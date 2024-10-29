<?php

namespace Kanboard\Plugin\Wiki;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Security\Role;
use Kanboard\Core\Translator;

class Plugin extends Base
{
    public function initialize()
    {
        // access map
        $this->projectAccessMap->add('WikiController', '*', Role::PROJECT_MEMBER);
        $this->projectAccessMap->add('WikiAjaxController', '*', Role::PROJECT_MEMBER);
        $this->projectAccessMap->add('WikiFileController', '*', Role::PROJECT_MEMBER);
        $this->projectAccessMap->add('WikiFileViewController', '*', Role::PROJECT_MEMBER);
        $this->applicationAccessMap->add('WikiController', array('readonly','detail_readonly'), Role::APP_PUBLIC);

        // page routes
        $this->route->addRoute('/wiki/project/:project_id', 'WikiController', 'show', 'wiki');
        $this->route->addRoute('/wiki/project/:project_id/readonly', 'WikiController', 'readonly', 'wiki');
        $this->route->addRoute('/wiki/project/:project_id/detail/:wiki_id', 'WikiController', 'detail', 'wiki');
        $this->route->addRoute('/wiki/project/:project_id/detail/:wiki_id/readonly', 'WikiController', 'detail_readonly', 'wiki');
        $this->route->addRoute('/wiki/project/:project_id/editions/:wiki_id', 'WikiController', 'editions', 'wiki');
        // $this->route->addRoute('/wiki/project/:project_id/breakdown', 'WikiController', 'breakdown', 'wiki');
        $this->route->addRoute('/wiki/file/:file_id', 'WikiFileViewController', 'show', 'wiki');
        $this->route->addRoute('/wiki/file/:file_id/image', 'WikiFileViewController', 'image', 'wiki');
        $this->route->addRoute('/wiki/file/:file_id/thumbnail', 'WikiFileViewController', 'thumbnail', 'wiki');
        $this->route->addRoute('/wiki/file/:file_id/browser', 'WikiFileViewController', 'browser', 'wiki');
        $this->route->addRoute('/wiki/file/:file_id/download', 'WikiFileViewController', 'download', 'wiki');

        // template hooks
        $this->template->hook->attach('template:config:sidebar', 'Wiki:config/sidebar');
        $this->template->hook->attach('template:project:dropdown', 'wiki:project/dropdown');
        $this->template->hook->attach('template:project-list:menu:after', 'wiki:wiki_list/menu');
        $this->template->hook->attach('template:header:dropdown', 'wiki:header/dropdown');
        $this->template->hook->attach('template:project-header:view-switcher', 'Wiki:project_header/views');

        // template overrides
        $this->template->setTemplateOverride('board/view_public', 'wiki:board/view_public');
        $this->template->setTemplateOverride('file_viewer/show', 'wiki:file_viewer/show');

        // CSS + JS
        $this->hook->on('template:layout:css', array('template' => 'plugins/Wiki/Asset/css/wiki.css'));
        $this->hook->on('template:layout:js', array('template' => 'plugins/Wiki/Asset/Javascript/main.js'));


        // $this->template->setTemplateOverride('wiki', 'wiki:wiki/layout');
        // can't figure out how to register helper template
        // $this->layout->register('wiki', '\Kanboard\Plugin\Wiki\Helper\layout');
        // $this->helper->register('wiki', '\Kanboard\Plugin\Wiki\Helper\layout');

        // helpers
        $this->helper->register('wikiHelper', '\Kanboard\Plugin\Wiki\Helper\WikiHelper');
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__ . '/Locale');
    }

    public function getClasses()
    {
        return array(
            'Plugin\Wiki\Controller' => [
                'WikiAjaxController'
            ],
            'Plugin\Wiki\Model' => array(
                'WikiModel',
                'WikiFileModel'
            ),
        );
    }

    public function getPluginName()
    {
        return 'Wiki';
    }

    public function getPluginDescription()
    {
        return t('Wiki to document projects');
    }

    public function getPluginAuthor()
    {
        return 'lastlink';
    }

    public function getPluginVersion()
    {
        return '0.3.8';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/funktechno/kanboard-plugin-wiki';
    }

    public function getCompatibleVersion()
    {
        return '>=1.0.37';
    }
}
