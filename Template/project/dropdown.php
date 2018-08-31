<?php if ($this->user->hasProjectAccess('WikiController', 'index', $project['id'])): ?>
    <li>
        <?= $this->url->icon('book', t('Wiki'), 'WikiController', 'show', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?>
    </li>
<?php endif ?>
