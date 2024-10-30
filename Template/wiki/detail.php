<?php
(isset($not_editable)) ?: $not_editable = false;
?>

<?php if (!$not_editable): ?>
    <?= $this->projectHeader->render($project, 'TaskListController', 'show') ?>
<?php endif ?>

<section class="sidebar-container wikicontent">

<?= $this->render('wiki:wiki/sidebar', array(
    'project' => $project,
    'wiki_id' => $wiki_id,
    'wikipages' => $wikipages,
    'not_editable' => $not_editable,
)) ?>

<div class="sidebar-content">
<div class="page-header">
    <h2><?=t($wikipage['title'])?></h2>
    <?php if (!$not_editable): ?>
        <?=$this->modal->medium('edit', t('Edit page'), 'WikiController', 'edit', array('plugin' => 'wiki', 'wiki_id' => $wikipage['id']))?>
        <?=$this->helper->modal->confirm('trash-o', t('Remove page'), 'WikiController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id']))?>
        <button class="separator">&nbsp;&nbsp;&nbsp;&nbsp;</button>
        <?=$this->url->icon('window-restore', t('View Editions'), 'WikiController', 'editions', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id']))?>
    <?php endif ?>
    <?php if(isset($wikipage['parent_id'])): ?>
        <button class="separator">&nbsp;&nbsp;&nbsp;&nbsp;</button>
        <?php if (!$not_editable): ?>
            <?=$this->url->icon('level-up', t('Parent Page'), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['parent_id']))?>
        <?php else: ?>
            <?=$this->url->icon('level-up', t('Parent Page'), 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $wikipage['parent_id']))?>
        <?php endif ?>
    <?php endif ?>
</div>
<ul class="panel">
    <?php if ($wikipage['creator_id'] > 0): ?>
        <li><?=t('Creator')?>: <strong><?=$this->text->e($wikipage['creator_name'] ?: $wikipage['creator_username'])?></strong></li>
    <?php endif?>
    <?php if ($wikipage['modifier_id'] > 0): ?>
        <li><?=t('Modifier')?>: <strong><?=$this->text->e($wikipage['modifier_name'] ?: $wikipage['modifier_username'])?></strong></li>
    <?php endif?>
    <li><?=t('Editions')?>: <strong><?=$wikipage['editions']?></strong> <?=t('Current Edition')?>: <strong> <?=$wikipage['current_edition']?></strong></li>
    <li><?=t('Date Creation')?>: <strong><?=$this->dt->date($wikipage['date_creation'])?></strong></li>
    <li><?=t('Date Modification')?>: <strong><?=$this->dt->date($wikipage['date_modification'])?></strong></li>
</ul>

<div class="wikicontent">
<br>
<?php if (!empty($wikipage['content'])): ?>
    <div class="page-header">
        <h2><?=t('Content')?></h2>
    </div>
    <article class="markdown">
        <?=$this->text->markdown($wikipage['content'])?>
    </article>
<?php endif?>

<?php if (!$not_editable): ?>
<div class="page-header">
        <h2><?=t('Attachments')?></h2>
</div>
<ul>
    <?php
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    ?>
    <?=$this->modal->medium('file', t('Attach a document'), 'WikiFileController', 'create', array('plugin' => 'wiki', 'wiki_id' => $wikipage['id'], 'project_id' => $wikipage['project_id']))?>
    <?= $this->modal->medium('camera', t('Add a screenshot'), 'WikiFileController', 'screenshot', array('plugin' => 'wiki', 'wiki_id' => $wikipage['id'], 'project_id' => $wikipage['project_id'])) ?>
</ul>

<?php if (!empty($files) || !empty($images)): ?>
    <?= $this->hook->render('template:task:show:before-attachments', array('wiki' => $wiki, 'project' => $project)) ?>
    <?= $this->render('wiki:wiki_file/show', array(
        'wiki' => $wiki,
        'files' => $files,
        'images' => $images
    )) ?>
<?php endif ?>
<?php endif ?>

</div>

</div>
<!-- end sidebar-content-->

</section>
<!--end sidebar-container-->
