<div class="sidebar">
    <ul>
        <li <?= $this->app->checkMenuSelection('WikiController', 'show') ?>>
            <?= $this->url->link(t('Wiki overview'), 'WikiController', 'show', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?>
        </li>
        <li <?= $this->app->checkMenuSelection('WikiLineController', 'show') ?>>
            <?= $this->url->link(t('Wiki lines'), 'WikiLineController', 'show', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?>
        </li>
        <li <?= $this->app->checkMenuSelection('WikiController', 'breakdown') ?>>
            <?= $this->url->link(t('Cost breakdown'), 'WikiController', 'breakdown', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?>
        </li>
    </ul>
</div>
