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

        if ($this->configModel->save($values)) {
            $this->flash->success(t('Settings saved successfully.'));
        } else {
            $this->flash->failure(t('Unable to save your settings.'));
        }

        $this->response->redirect($this->helper->url->to('ConfigController', 'show', array('plugin' => 'Wiki')));
    }
}
