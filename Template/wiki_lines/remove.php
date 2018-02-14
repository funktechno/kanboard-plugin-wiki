<div class="page-header">
    <h2><?= t('Remove wiki line') ?></h2>
</div>

<div class="confirm">
    <p class="alert alert-info"><?= t('Do you really want to remove this wiki line?') ?></p>
    <?= $this->modal->confirmButtons('WikiLineController', 'remove', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wiki_id)) ?>
</div>
