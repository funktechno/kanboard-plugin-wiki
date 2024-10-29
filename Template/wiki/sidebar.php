<style>
.sidebar {
    height: 100%;
    resize: horizontal;
    overflow: auto;
}
.sidebar-container {
    clear: both;
}
</style>

<div class="sidebar">

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
    <h2>Wiki <?= t('Content') ?></h2>
</div>

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

</div>
<!--end sidebar-->
