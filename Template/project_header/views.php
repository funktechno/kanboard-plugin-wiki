<li <?= $this->app->checkMenuSelection('WikiController') ?>>
    <?= $this->url->icon('book', t('Wiki'), 'WikiController', 'detail', array('project_id' => $project['id'], 'wiki_id' => 0, 'plugin' => 'wiki'), false, 'view-wiki', t('Keyboard shortcut: "%s"', 'v w')) ?>
</li>
