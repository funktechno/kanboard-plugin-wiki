<?=$this->wikiHelper->js("plugins/Wiki/Asset/Javascript/wiki.js")?>

<?php (isset($not_editable)) ?: $not_editable = false;
?>
<?php if (!$not_editable): ?>
<?= $this->projectHeader->render($project, 'TaskListController', 'show') ?>
<?php endif ?>

<div class="page-header wikicontent">
    <h2><?=t('Wiki overview')?></h2>
    <?php if (!$not_editable): ?>
        <?=$this->modal->medium('plus', t('New Wiki page'), 'WikiController', 'create', array('plugin' => 'wiki', 'project_id' => $project['id']))?>
        <?php if ($project['is_public']): ?>
            <br>
            <?= $this->url->icon('share-alt', t('Public link'), 'WikiController', 'readonly', array('plugin' => 'wiki', 'token' => $project['token']), false, '', '', true) ?>
        <?php endif ?>
    <?php else: ?>
        <?= $this->url->link(t('Board'), 'BoardViewController', 'readonly', array('token' => $project['token']), false, '', '', true) ?>
    <?php endif ?>
</div>

<?php if (!empty($wikipages)): ?>

<!-- Title
Editions
Creator
Created
Last modifier
Modified -->

<table id="wikilist" class="table-stripped" style="width:100%">
    <tr style="vertical-align:top">
        <th></th>
        <th><?=t('Title')?></th>
        <th><?=t('Id')?></th>
        <th><?=t('Parent Page')?></th>
        <th><?=t('Editions')?></th>
        <th><?=t('Current Edition')?></th>
        <th><?=t('Creator')?></th>
        <th><?=t('Created')?></th>
        <th><?=t('Last modifier')?></th>
        <th><?=t('Modified')?></th>
    </tr>
    <?php foreach ($wikipages as $wikipage_id => $wikipage): ?>
    <?php if ($wikipage_id != ''): ?>
    <tr class="table-list-row">
        <td style="white-space:nowrap">
        <?php if (!$not_editable): ?>
            <button class="action">
            <?=$this->modal->medium('edit', '', 'WikiController', 'edit', array('plugin' => 'wiki', 'wiki_id' => $wikipage_id))?>
            </button>
            <button class="action">
            <?=$this->modal->confirm('trash-o', '', 'WikiController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage_id))?>
            </button>
        <?php endif ?>
        </td>
        <td class="sidebar" style="padding:0">
        <?php if (!$not_editable): ?>
            <ul><li class="wikipage">
            <?=$this->url->link(t($wikipage['title']), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage_id), false, 'wikilink')?>
            </li></ul>
        <?php else: ?>
            <ul><li class="wikipage">
            <?=$this->url->link(t($wikipage['title']), 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $wikipage_id), false, 'wikilink')?>
            </li></ul>
        <?php endif ?>
        </td>
        <td>
            <?=$wikipage_id?>
        </td>
        <td>
            <?php if (isset($wikipage['parent_id'])): ?>
                <?php if (!$not_editable): ?>
                    <?=$this->url->link($wikipage['parent_id'], 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['parent_id']))?>
                <?php else: ?>
                    <?=$this->url->link($wikipage['parent_id'], 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $wikipage['parent_id']))?>
                <?php endif ?>
            <?php else: ?>
                <?=t('(root)')?>
            <?php endif ?>
        </td>
        <td><?=$wikipage['editions']?></td>
        <td><?=$wikipage['current_edition']?></td>
        <td><?=$this->text->e($wikipage['creator_name'] ?: $wikipage['creator_username'])?></td>
        <td><?=$this->dt->date($wikipage['date_creation'])?></td>
        <td><?=$this->text->e($wikipage['modifier_name'] ?: $wikipage['modifier_username'])?></td>
        <td><?=$this->dt->date($wikipage['date_modification'])?></td>
    </tr>
    <?php endif ?>
    <?php endforeach ?>
</table>

<?php else: ?>
    <p class="alert"><?=t('There are no Wiki pages for this project.')?></p>
<?php endif?>
