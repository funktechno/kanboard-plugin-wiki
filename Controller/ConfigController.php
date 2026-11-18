<?php

namespace Kanboard\Plugin\Wiki\Controller;

/**
 * Class ConfigController
 *
 * @package Kanboard\Plugin\Wiki\Controller
 */
class ConfigController extends \Kanboard\Controller\ConfigController
{
    public function show()
    {
        $values = [];
            // 'title' => $editionvalues['title'],
        $values['persistEditions'] =$this->configModel->get('persistEditions');
        $values['wiki_public_access'] = $this->wikiModel->isPublicWikiEnabled() ? 1 : 0;
        $values['wiki_public_attachments'] = $this->wikiModel->isPublicAttachmentsEnabled() ? 1 : 0;

        // persistEditions
        // public function get($name, $default_value = '')

        $this->response->html($this->helper->layout->config('Wiki:config/wiki', array(
            'title' => t('Settings').' &gt; '.t('Wiki settings'),
            'values' => $values
        )));
    }

    public function save()
    {
        $values =  $this->request->getValues();

        foreach (array('persistEditions', 'wiki_public_access', 'wiki_public_attachments') as $checkbox) {
            if (! isset($values[$checkbox])) {
                $values[$checkbox] = 0;
            }
        }

        if ($this->configModel->save($values)) {
            $this->flash->success(t('Settings saved successfully.'));
        } else {
            $this->flash->failure(t('Unable to save your settings.'));
        }

        $this->response->redirect($this->helper->url->to('ConfigController', 'show', array('plugin' => 'wiki')));
    }
}
