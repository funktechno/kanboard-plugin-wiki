<?php if ($this->user->hasProjectAccess('BudgetController', 'index', $project['id'])): ?>
    <li>
        <?= $this->url->icon('pie-chart', t('Budget'), 'BudgetController', 'show', array('plugin' => 'budget', 'project_id' => $project['id'])) ?>
    </li>
<?php endif ?>
