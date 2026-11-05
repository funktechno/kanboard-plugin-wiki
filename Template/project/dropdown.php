<?php if ($this->user->hasProjectAccess('WikiController', 'index', $project['id'])): ?>
    <li><?= $this->url->icon('book', t('Wiki Overview'), 'WikiController', 'show', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?></li>
<?php endif ?>
