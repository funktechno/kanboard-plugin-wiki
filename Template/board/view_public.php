<section id="main" class="public-board">
   <?= $this->url->link(t('wiki'), 'WikiController', 'readonly', array('plugin' => 'wiki', 'token' => $project['token']), false, '', '', true) ?>
   <?= $this->render('board/table_container', array(
            'project' => $project,
            'swimlanes' => $swimlanes,
            'board_private_refresh_interval' => $board_private_refresh_interval,
            'board_highlight_period' => $board_highlight_period,
            'not_editable' => true,
    )) ?>

</section>