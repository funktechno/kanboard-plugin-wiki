<li <?= $this->app->checkMenuSelection('WikiController') ?>>
    <?= $this->url->icon('book', t('Wiki'), 'WikiController', 'project', array('project_id' => $project['id'], 'search' => $filters['search'], 'plugin' => 'wiki'), false, 'view-wiki', t('Keyboard shortcut: "%s"', 'v w')) ?>
</li>