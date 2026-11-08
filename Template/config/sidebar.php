<li <?= $this->app->checkMenuSelection('ConfigController', 'show', 'wiki') ?>>
    <?= $this->url->link(t('Wiki settings'), 'ConfigController', 'show', array('plugin' => 'wiki')) ?>
</li>