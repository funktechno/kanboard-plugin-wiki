<?php

namespace Kanboard\Plugin\Budget\Controller;

use Kanboard\Controller\BaseController;

/**
 * Hourly Rate controller
 *
 * @package  controller
 * @author   Frederic Guillot
 */
class HourlyRateController extends BaseController
{
    public function show()
    {
        $user = $this->getUser();

        $this->response->html($this->helper->layout->user('budget:hourlyrate/show', array(
            'rates' => $this->hourlyRate->getAllByUser($user['id']),
            'currencies_list' => $this->currencyModel->getCurrencies(),
            'user' => $user,
        )));
    }

    public function create(array $values = array(), array $errors = array())
    {
        $user = $this->getUser();

        $this->response->html($this->helper->layout->user('budget:hourlyrate/create', array(
            'rates' => $this->hourlyRate->getAllByUser($user['id']),
            'currencies_list' => $this->currencyModel->getCurrencies(),
            'values' => $values + array('user_id' => $user['id']),
            'errors' => $errors,
            'user' => $user,
        )));
    }

    public function save()
    {
        $values = $this->request->getValues();
        list($valid, $errors) = $this->hourlyRate->validateCreation($values);

        if ($valid) {
            if ($this->hourlyRate->create($values['user_id'], $values['rate'], $values['currency'], $values['date_effective'])) {
                $this->flash->success(t('Hourly rate created successfully.'));
                $this->response->redirect($this->helper->url->to('HourlyRateController', 'show', array('plugin' => 'budget', 'user_id' => $values['user_id'])), true);
                return;
            } else {
                $this->flash->failure(t('Unable to save the hourly rate.'));
            }
        }

        $this->create($values, $errors);
    }

    public function confirm()
    {
        $user = $this->getUser();

        $this->response->html($this->template->render('budget:hourlyrate/remove', array(
            'rate_id' => $this->request->getIntegerParam('rate_id'),
            'user' => $user,
        )));
    }

    public function remove()
    {
        $this->checkCSRFParam();
        $user = $this->getUser();

        if ($this->hourlyRate->remove($this->request->getIntegerParam('rate_id'))) {
            $this->flash->success(t('Rate removed successfully.'));
        } else {
            $this->flash->success(t('Unable to remove this rate.'));
        }

        $this->response->redirect($this->helper->url->to('HourlyRateController', 'show', array('plugin' => 'budget', 'user_id' => $user['id'])), true);
    }
}
