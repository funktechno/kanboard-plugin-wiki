<div class="sidebar wikisidebar">

<?php if (!$not_editable): ?>
    <?=$this->wikiHelper->js("plugins/Wiki/Asset/vendor/jquery-sortable/jquery-sortable.js")?>
    <?=$this->wikiHelper->js("plugins/Wiki/Asset/Javascript/wiki.js")?>
<?php endif ?>

<?php if (!$not_editable): ?>
    <?= $this->url->icon('book', t('Wiki overview'), 'WikiController', 'show', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?>
    <br>
    <?=$this->modal->medium('plus', t('New Wiki page'), 'WikiController', 'create', array('plugin' => 'wiki', 'project_id' => $project['id']))?>
<?php else: ?>
    <?= $this->url->icon('book', t('Wiki overview'), 'WikiController', 'readonly', array('plugin' => 'wiki', 'token' => $project['token'])) ?>
<?php endif ?>

<div class="page-header">
    <br>
    <h2><?= t('Wiki') ?> <?= t('Content') ?></h2>
    <div style="float: right">
        <button class="gotoSelected actionBigger" title="<?= t('Go to Selected Wiki Page') ?>"><a><i class="fa fa-share-square"></i></a></button>
        <button class="expandAll actionBigger"title="<?= t('Expand All Wiki Subpages') ?>"><a><i class="fa fa-plus-square"></i></a></button>
        <button class="collapseAll actionBigger"title="<?= t('Collapse All Wiki Subpages') ?>"><a><i class="fa fa-minus-square"></i></a></button>
    </div>
</div>
<br>

<?php if (!empty($wikipages)): ?>
<ul id="wikitree" <?php if (!$not_editable): ?>data-reorder-url="<?= $this->url->href('WikiAjaxController', 'reorder_by_index', array('plugin' => 'wiki', 'project_id' => $project['id'], 'csrf_token' => $this->app->getToken()->getReusableCSRFToken())) ?>"<?php endif ?>>
<li class="wikipage" data-project-id="<?= $project['id'] ?>" data-page-id="0" data-page-order="0">
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
