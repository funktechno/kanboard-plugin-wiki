<?php

namespace Kanboard\Plugin\Wiki;

use Kanboard\Core\Translator;
use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Security\Role;

class Plugin extends Base
{
    public function initialize()
    {
        $this->applicationAccessMap->add('HourlyRateController', '*', Role::APP_ADMIN);
        $this->projectAccessMap->add('WikiController', '*', Role::PROJECT_MANAGER);

        $this->route->addRoute('/wiki/project/:project_id', 'WikiController', 'show', 'wiki');
        $this->route->addRoute('/wiki/project/:project_id/lines', 'WikiLineController', 'show', 'wiki');
        $this->route->addRoute('/wiki/project/:project_id/breakdown', 'WikiController', 'breakdown', 'wiki');

        $this->template->hook->attach('template:project:dropdown', 'wiki:project/dropdown');
        $this->template->hook->attach('template:user:sidebar:actions', 'wiki:user/sidebar');

        $this->helper->register('wikiHelper', '\Kanboard\Plugin\Wiki\Helper\WikiHelper');

    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getClasses()
    {
        return array(
            'Plugin\Wiki\Model' => array(
                'HourlyRate',
                'Wiki',
            )
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
        return '0.1.0';
    }

    public function getPluginHomepage()
    {
        return 'https://bitbucket.org/lastlink/kanboard-plugin-wiki/';
    }

    public function getCompatibleVersion()
    {
        return '>=1.0.37';
    }
}
