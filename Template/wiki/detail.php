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
    <h2><?=$this->text->e($wikipage['title'])?></h2>
    <?php if (!$not_editable): ?>
        <?=$this->modal->medium('edit', t('Edit page'), 'WikiController', 'edit', array('plugin' => 'wiki', 'wiki_id' => $wikipage['id']))?>
        <?=$this->helper->modal->confirm('trash-o', t('Remove page'), 'WikiController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id']))?>
        <span class="separator" aria-hidden="true">&nbsp;&nbsp;&nbsp;&nbsp;</span>
        <?=$this->url->icon('window-restore', t('View Editions'), 'WikiController', 'editions', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id']))?>
        <?php if (! empty($last_edition) && $last_edition['added'] !== null): ?>
            <?=$this->url->link(
                t('Last change').' <span class="wiki-diff-stats"><span class="wiki-diff-added">+'.(int) $last_edition['added'].'</span> <span class="wiki-diff-removed">&minus;'.(int) $last_edition['removed'].'</span></span>',
                'WikiController', 'editions', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id']), false, '', '', false, 'edition-'.(int) $last_edition['edition']
            )?>
        <?php endif ?>
    <?php endif ?>
    <?php if(isset($wikipage['parent_id'])): ?>
        <span class="separator" aria-hidden="true">&nbsp;&nbsp;&nbsp;&nbsp;</span>
        <?php if (!$not_editable): ?>
            <?=$this->url->icon('level-up', t('Parent Page'), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['parent_id']))?>
        <?php else: ?>
            <?=$this->url->icon('level-up', t('Parent Page'), 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $wikipage['parent_id']))?>
        <?php endif ?>
    <?php endif ?>
</div>

<div style="float:left">
<details open class="panel" style="margin:0">
<summary><h3 class="wiki-summary"><?=t('Details')?></h3></summary>
<ul class="wiki-details">
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
</details>
</div>

<div style="float:left">&nbsp;</div>

<div class="sidebar panel" style="float:left;margin:0;width:auto;max-width:25%;">
<details open>
<summary><h3 class="wiki-summary"><?=t('Subpages')?></h3></summary>
<ul id="subpages">
<?php if (!empty($wikipage_sublist)): ?>
    <?php foreach ($wikipage_sublist as $subpage_id => $subpage_title): ?>
        <?php if (!$not_editable): ?>
            <li class="wikilink">&nbsp;
            <?=$this->url->link($this->text->e($subpage_title), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $subpage_id), false, 'wikilink')?>
            </li>
        <?php else: ?>
            <li class="wikilink">&nbsp;
            <?=$this->url->link($this->text->e($subpage_title), 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $subpage_id), false, 'wikilink')?>
            </li>
        <?php endif ?>
    <?php endforeach ?>
<?php else: ?>
    <li class="alert alert-info">
        <?=t('There are no Wiki pages.')?>
    </li>
<?php endif?>
</ul>
</details>
</div>

<div class="wikicontent" style="clear:both">
<br>
<?php if (!empty($wikipage['content'])): ?>
    <div class="page-header">
        <h2><?=t('Content')?></h2>
    </div>
    <article class="markdown">
        <?=$this->text->markdown($wikipage['content'], $not_editable)?>
    </article>
<?php endif?>

<?php if (!$not_editable): ?>
<div class="page-header">
        <h2><?=t('Attachments')?></h2>
</div>
<div class="wiki-attachment-actions">
    <?=$this->modal->medium('file', t('Attach a document'), 'WikiFileController', 'create', array('plugin' => 'wiki', 'wiki_id' => $wikipage['id'], 'project_id' => $wikipage['project_id']))?>
    <?= $this->modal->medium('camera', t('Add a screenshot'), 'WikiFileController', 'screenshot', array('plugin' => 'wiki', 'wiki_id' => $wikipage['id'], 'project_id' => $wikipage['project_id'])) ?>
</div>

<?php if (!empty($files) || !empty($images)): ?>
    <?= $this->hook->render('template:task:show:before-attachments', array('wiki' => $wiki, 'project' => $project)) ?>
    <?= $this->render('wiki:wiki_file/show', array(
        'wiki' => $wiki,
        'files' => $files,
        'images' => $images
    )) ?>
<?php endif ?>
<?php elseif (! empty($public_token) && (! empty($files) || ! empty($images))): ?>
<div class="page-header">
        <h2><?=t('Attachments')?></h2>
</div>
    <?= $this->render('wiki:wiki_file/show', array(
        'wiki' => $wiki,
        'files' => $files,
        'images' => $images,
        'public_token' => $public_token,
    )) ?>
<?php endif ?>

</div>

</div>
<!-- end sidebar-content-->

</section>
<!--end sidebar-container-->
