<div class="page-header">
    <h2><?= t('Restore Wiki page edition') ?></h2>
</div>

<div class="confirm">
    <p class="alert alert-info"><?= t('Do you really want to restore this edition?') ?></p>
    <?= $this->modal->confirmButtons('WikiController', 'restore', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wiki_id, 'edition' => $edition)) ?>
</div>
