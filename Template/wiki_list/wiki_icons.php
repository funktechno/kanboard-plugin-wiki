<div class="table-list-icons">
    &nbsp;

    <?php if ($this->user->hasAccess('ProjectUserOverviewController', 'managers')): ?>
        <?= $this->app->tooltipLink('<i class="fa fa-users"></i>', $this->url->href('ProjectUserOverviewController', 'users', array('project_id' => $wiki['id']))) ?>
    <?php endif ?>

    <?php if (! empty($wiki['description'])): ?>
        <?= $this->app->tooltipMarkdown($wiki['description']) ?>
    <?php endif ?>

    <?php if ($wiki['is_active'] == 0): ?>
        <i class="fa fa-ban fa-fw" aria-hidden="true" title="<?= t('Closed') ?>"></i><?= t('Closed') ?>
    <?php endif ?>
</div>
