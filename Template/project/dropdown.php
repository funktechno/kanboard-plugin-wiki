<?php if ($this->user->hasProjectAccess('WikiController', 'index', $project['id'])): ?>
    <li>
        <?= $this->url->icon('book', t('Wiki'), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => 0)) ?>
    </li>
<?php endif ?>
