<h1>
    <span class="logo">
        <?= $this->url->link('K<span>B</span>', 'DashboardController', 'show', array(), false, '', t('Dashboard')) ?>
    </span>
    <span class="title">
        <?php if (! empty($wiki)): ?>
            <?= $this->url->link($this->text->e($wiki['title']), 'WikiController', 'detail', array('plugin' => 'Wiki', 'project_id' => $wiki['project_id'], 'wiki_id' => $wiki['id'])) ?>
        <?php else: ?>
            <?= $this->text->e($title) ?>
        <?php endif ?>
    </span>
    <?php if (! empty($description)): ?>
        <?= $this->app->tooltipHTML($description) ?>
    <?php endif ?>
</h1>
