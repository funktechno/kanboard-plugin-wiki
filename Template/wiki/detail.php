<?php (isset($not_editable)) ?: $not_editable = false;
?>
<?php if (!$not_editable): ?>
    <?=$this->wikiHelper->js("plugins/Wiki/Asset/vendor/jquery-sortable/jquery-sortable.js")?>
    <?=$this->wikiHelper->js("plugins/Wiki/Asset/Javascript/wiki.js")?>
    <?= $this->projectHeader->render($project, 'TaskListController', 'show') ?>
<?php endif ?>
<div class="page-header">
    <h2>
    <?php if (!$not_editable): ?>
        <?= $this->url->link(t('Wiki overview'), 'WikiController', 'show', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?>
    <?php else: ?>
        <?= $this->url->link(t('Wiki overview'), 'WikiController', 'readonly', array('plugin' => 'wiki', 'token' => $project['token'])) ?>
    <?php endif ?>
    </h2>
</div>

<style>
    .clearfix::after {
        content: "";
        clear: both;
        display: table;
    }
    .column {
        float: left;
        min-width: 0;
    }
    .list {
    width: 25%;
}
    .content {
    width: 75%;
}

</style>
<div class="clearfix">
<div class="sidebar column list">
    <?php if (!empty($wikipages)): ?>
    <ul id="columns" <?php if (!$not_editable): ?>data-reorder-url="<?= $this->url->href('WikiAjaxController', 'reorder_by_index', array('plugin' => 'wiki', 'project_id' => $project['id'], 'csrf_token' => $this->app->getToken()->getReusableCSRFToken())) ?>"<?php endif ?>>

        <?php foreach ($wikipages as $page): ?>
            <li class="wikipage" data-project-id="<?=$project['id']?>" data-page-order="<?=$page['ordercolumn']?>" data-page-id="<?=$page['id']?>">
                <?php if (!$not_editable): ?>
                    <?=$this->url->link(t($page['title']), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $page['id']))?>

                    <?=$this->modal->confirm('trash-o', t(''), 'WikiController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $page['id']))?>
                <?php else: ?>
                    <?=$this->url->link(t($page['title']), 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $page['id']))?>
                <?php endif ?>
                 <?php if (count($page['children']) > 0): ?>
                    <?=$this->wikiHelper->renderChildren($page['children'], $page['id'], $project, $not_editable)?>
                <?php endif ?>
            </li>


        <?php endforeach?>
    </ul>
        <?php else: ?>
    <ul>
        <li class="alert alert-info">
            <?=t('There are no Wiki pages.')?>
        </li>
    </ul>
        <?php endif?>
        <?php if (!$not_editable): ?>
    <ul>
        <li>
            <?=$this->modal->medium('plus', t('New Wiki page'), 'WikiController', 'create', array('plugin' => 'wiki', 'project_id' => $project['id']))?>
        </li>
    </ul>
        <?php endif ?>

    </ul>
</div>

<div class="column content">
<div class="page-header">
    <h2><?=t($wikipage['title'])?></h2>
    <?php if(isset($wikipage['parent_id'])): ?>
        <?=$this->form->label(t('is a child of'), 'is a child of')?>
        <?php if (!$not_editable): ?>
            <?=$this->url->link($wikipage['parent_id'], 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['parent_id']))?>
        <?php else: ?>
            <?=$this->url->link($wikipage['parent_id'], 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $wikipage['parent_id']))?>
        <?php endif ?>
        <br>
        <br>
    <?php endif ?>
    <?php if (!$not_editable): ?>
        <?=$this->modal->large('edit', t('Edit page'), 'WikiController', 'edit', array('plugin' => 'wiki', 'wiki_id' => $wikipage['id']))?>
        <br>
        <?=$this->url->icon('window-restore', t('View Editions'), 'WikiController', 'editions', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id']))?>
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
