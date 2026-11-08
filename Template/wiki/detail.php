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

<div class="sidebar-content" style="margin:0">
<div class="page-header" style="margin:0">
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

<div style="float:left">
<ul class="panel" style="margin:0">
<details open>
<summary><h4 style="display:inline-block"><?=t('Details')?></h4></summary>
    <?php if ($wikipage['creator_id'] > 0): ?>
        <li><?=t('Creator')?>: <strong><?=$this->text->e($wikipage['creator_name'] ?: $wikipage['creator_username'])?></strong></li>
    <?php endif?>
    <?php if ($wikipage['modifier_id'] > 0): ?>
        <li><?=t('Modifier')?>: <strong><?=$this->text->e($wikipage['modifier_name'] ?: $wikipage['modifier_username'])?></strong></li>
    <?php endif?>
    <li><?=t('Editions')?>: <strong><?=$wikipage['editions']?></strong> <?=t('Current Edition')?>: <strong> <?=$wikipage['current_edition']?></strong></li>
    <li><?=t('Date Creation')?>: <strong><?=$this->dt->date($wikipage['date_creation'])?></strong></li>
    <li><?=t('Date Modification')?>: <strong><?=$this->dt->date($wikipage['date_modification'])?></strong></li>
</details>
</ul>
</div>

<div style="float:left">&nbsp;</div>

<div class="sidebar panel" style="float:left;margin:0;width:auto;max-width:25%;">
<ul id="subpages">
<details open>
<summary><h4 style="display:inline-block"><?=t('Subpages')?></h4></summary>
<?php if (!empty($wikipage_sublist)): ?>
    <?php foreach ($wikipage_sublist as $subpage_id => $subpage_title): ?>
        <?php if (!$not_editable): ?>
            <li class="wikilink">&nbsp;
            <?=$this->url->link(t($subpage_title), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $subpage_id), false, 'wikilink')?>
            </li>
        <?php else: ?>
            <li class="wikilink">&nbsp;
            <?=$this->url->link(t($subpage_title), 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $subpage_id), false, 'wikilink')?>
            </li>
        <?php endif ?>
    <?php endforeach ?>
<?php else: ?>
    <li class="alert alert-info">
        <?=t('There are no Wiki pages.')?>
    </li>
<?php endif?>
</details>
</ul>
</div>

<div class="wikicontent" style="clear:both">
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
