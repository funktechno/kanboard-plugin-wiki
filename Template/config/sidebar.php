<li <?= $this->app->checkMenuSelection('ConfigController', 'show', 'Wiki') ?>>
    <?= $this->url->link(t('Wiki settings'), 'ConfigController', 'show', array('plugin' => 'Wiki')) ?>
</li>