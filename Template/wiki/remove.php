<div class="page-header">
    <h2><?= t('Remove wiki page') ?></h2>
</div>

<div class="confirm">
    <p class="alert alert-info"><?= t('Do you really want to remove this wiki page?') ?></p>
    <?= $this->modal->confirmButtons('WikiController', 'remove', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wiki_id)) ?>
</div>
