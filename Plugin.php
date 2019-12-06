<?php

namespace Kanboard\Plugin\Wiki;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Security\Role;
use Kanboard\Core\Translator;

class Plugin extends Base
{
    public function initialize()
    {
        $this->projectAccessMap->add('WikiController', '*', Role::PROJECT_MEMBER);
        $this->projectAccessMap->add('WikiFileController', '*', Role::PROJECT_MEMBER);
        $this->projectAccessMap->add('WikiFileViewController', '*', Role::PROJECT_MEMBER);

        $this->route->addRoute('/wiki/project/:project_id', 'WikiController', 'show', 'wiki');
        $this->route->addRoute('/wiki/project/:project_id', 'WikiController', 'detail', 'wiki');
        $this->route->addRoute('/wiki/project/:project_id', 'WikiController', 'editions', 'wiki');
        $this->route->addRoute('/wiki/project/:project_id', 'WikiController', 'edit', 'wiki');

        // show images as list
        $this->route->addRoute('/wiki/project/:project_id', 'WikiFileController', 'show', 'wiki_file');
        $this->route->addRoute('/wiki/project/:project_id', 'WikiFileController', 'create', 'wiki_file');
        $this->route->addRoute('/wiki/project/:project_id', 'WikiFileController', 'remove', 'wiki_file');
        $this->route->addRoute('/wiki/project/:project_id', 'WikiFileController', 'images', 'wiki_file');
        $this->route->addRoute('/wiki/project/:project_id', 'WikiFileController', 'files', 'wiki_file');



        $this->template->hook->attach('template:config:sidebar', 'Wiki:config/sidebar');

        // $this->route->addRoute('/wiki/project/:project_id&:wikipage_id', 'WikiController', 'detail', 'wiki');
        $this->route->addRoute('/wiki/project/:project_id/breakdown', 'WikiController', 'breakdown', 'wiki');

        $this->template->hook->attach('template:project:dropdown', 'wiki:project/dropdown');

        $this->template->hook->attach('template:project-list:menu:after', 'wiki:wiki_list/menu');

        $this->template->hook->attach('template:header:dropdown', 'wiki:header/dropdown');
        
        $this->template->setTemplateOverride('file_viewer/show', 'wiki:file_viewer/show');

        $this->hook->on('template:layout:css', array('template' => 'plugins/Wiki/Asset/css/wiki.css'));
        $this->hook->on('template:layout:js', array('template' => 'plugins/Wiki/Asset/Javascript/wiki.js'));


        // $this->template->setTemplateOverride('wiki', 'wiki:wiki/layout');
        // can't figure out how to register helper template
        // $this->layout->register('wiki', '\Kanboard\Plugin\Wiki\Helper\layout');
        // $this->helper->register('wiki', '\Kanboard\Plugin\Wiki\Helper\layout');

        // $this->helper->register('wikiHelper', '\Kanboard\Plugin\Wiki\Helper\WikiHelper');
        

    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__ . '/Locale');
    }

    public function getClasses()
    {
        return array(
            'Plugin\Wiki\Model' => array(
                'Wiki',
                'WikiFile'
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
        return '0.3.1';
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
