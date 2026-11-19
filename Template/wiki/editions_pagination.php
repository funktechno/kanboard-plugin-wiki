<?php if ($pagination['pages'] > 1): ?>
<nav class="wiki-editions-pagination" aria-label="<?= t('Editions pages') ?>">
    <?php if ($pagination['page'] > 1): ?>
        <?= $this->url->link('&larr; '.t('Newer'), 'WikiController', 'editions', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wiki_id, 'page' => $pagination['page'] - 1)) ?>
    <?php endif ?>
    <span><?= t('Page %d of %d (%d editions)', $pagination['page'], $pagination['pages'], $pagination['total']) ?></span>
    <?php if ($pagination['page'] < $pagination['pages']): ?>
        <?= $this->url->link(t('Older').' &rarr;', 'WikiController', 'editions', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wiki_id, 'page' => $pagination['page'] + 1)) ?>
    <?php endif ?>
</nav>
<?php endif ?>
