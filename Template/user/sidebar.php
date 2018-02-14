<?php if ($this->user->hasAccess('HourlyRateController', 'index')): ?>
    <li <?= $this->app->getRouterController() === 'HourlyRateController' ? 'class="active"' : '' ?>>
        <?= $this->url->link(t('Hourly rate'), 'HourlyRateController', 'show', array('plugin' => 'budget', 'user_id' => $user['id'])) ?>
    </li>
<?php endif ?>
