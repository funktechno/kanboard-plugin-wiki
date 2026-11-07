<section id="main" class="public-board">
   <?php if ($this->app->configModel->get('enable_wiki_public_access', 0) == 1): ?>
    <div class="page-header">
        <h2><?= $this->url->link(t('Wiki'), 'WikiController', 'readonly', array('plugin' => 'wiki', 'token' => $project['token']), false, 'btn') ?></h2>
    </div>
    <?php endif ?>
    <?= $this->render('board/table_container', array(
            'project' => $project,
            'swimlanes' => $swimlanes,
            'board_private_refresh_interval' => $board_private_refresh_interval,
            'board_highlight_period' => $board_highlight_period,
            'not_editable' => true,
    )) ?>

</section>