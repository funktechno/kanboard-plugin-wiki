<?php
(isset($not_editable)) ?: $not_editable = false;
$is_selected = ($wiki_id == 0) ? ' selected' : '';
?>

<?php if ($wiki_id == 0 && !$not_editable): ?>
    <?= $this->projectHeader->render($project, 'TaskListController', 'show') ?>
<?php endif ?>

<!--base app dir hidden data-->
<div class="hideMe" id="_Wiki_BaseAppDir_"> <?=$this->url->dir()?> </div>

<!--wiki tree expanded branches-->
<?php
    $expandedWikiBranches = $_SESSION["wiki_tree"] ?? [];
?>
<div class="hideMe" id="_Wiki_ExpandedBranches_"> <?= json_encode($expandedWikiBranches) ?> </div>
<!-- -------- -->

<?php if (!$not_editable): ?>
    <?=$this->wikiHelper->js("plugins/Wiki/Asset/vendor/jquery-sortable/jquery-sortable.js")?>
    <?=$this->wikiHelper->js("plugins/Wiki/Asset/Javascript/wiki.js")?>
<?php endif ?>

<?php if ($wiki_id == 0): ?>
<section class="sidebar-container wikicontent">
<?php endif ?>
<div class="sidebar wikisidebar wikicontent">

<div class="page-header">
    <div style="float: left">
        <h2><?= t('Wiki').' '.t('Content') ?></h2>
    </div>
    &nbsp;
    <div style="float: right">
        <button class="gotoSelected actionBigger" title="<?= t('Go to Selected Wiki Page') ?>"><a><i class="fa fa-share-square"></i></a></button>
        <button class="expandAll actionBigger"title="<?= t('Expand All Wiki Subpages') ?>"><a><i class="fa fa-plus-square"></i></a></button>
        <button class="collapseAll actionBigger"title="<?= t('Collapse All Wiki Subpages') ?>"><a><i class="fa fa-minus-square"></i></a></button>
    </div>

    <div style="clear: both">
<?php if (!$not_editable): ?>
    <?= $this->url->icon('book', t('Wiki overview'), 'WikiController', 'show', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?>
    <button class="separator">&nbsp;&nbsp;</button>
    <?=$this->modal->medium('plus', t('New Wiki page'), 'WikiController', 'create', array('plugin' => 'wiki', 'project_id' => $project['id']))?>
<?php else: ?>
    <?= $this->url->icon('book', t('Wiki overview'), 'WikiController', 'readonly', array('plugin' => 'wiki', 'token' => $project['token'])) ?>
<?php endif ?>
    </div>
</div>

<?php if (!empty($wikipages)): ?>
    <ul id="wikitree" data-selected-wiki-id="<?=$wiki_id?>" <?php if (!$not_editable): ?>data-reorder-url="<?= $this->url->href('WikiAjaxController', 'reorder_by_index', array('plugin' => 'wiki', 'project_id' => $project['id'], 'csrf_token' => $this->app->getToken()->getReusableCSRFToken())) ?>"<?php endif ?>>
    <li class="wikipage<?=$is_selected?>" data-project-id="<?= $project['id'] ?>" data-page-id="0" data-page-order="0">
    <button class="branch actionBigger" title="' . t('Expand/Collapse Subpages') . '"><a><i class="fa fa-plus-square-o"></i></a></button>
    <?php if (!$not_editable): ?>
        <?=$this->helper->url->icon('home', t('(root)'), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => 0), false, 'wikilink'.$is_selected)?>
    <?php else: ?>
        <?=$this->helper->url->icon('home', t('(root)'), 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => 0), false, 'wikilink'.$is_selected)?>
    <?php endif?>
    <?=$this->wikiHelper->renderChildren($wikipages, 0, $project, $wiki_id, $not_editable)?>
    </li>
    </ul>
<?php else: ?>
    <ul>
        <li class="alert alert-info">
            <?=t('There are no Wiki pages.')?>
        </li>
    </ul>
<?php endif?>

</div>
<!--end sidebar-->

<?php if ($wiki_id == 0): ?>
</section>
<?php endif ?>
