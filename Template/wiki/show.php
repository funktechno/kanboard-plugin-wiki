<?php (isset($not_editable)) ?: $not_editable = false;
?>
<?php if (!$not_editable): ?>
<?= $this->projectHeader->render($project, 'TaskListController', 'show') ?>
<?php endif ?>
<div class="page-header">
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

<div class="column ">
<?php if (!empty($wikipages)): ?>

<!-- <hr/> -->
<!-- Title
Editions
Creator
Created
Last modifier
Modified -->

        <table class="table-fixed table-stripped" style="width:100%">
            <tr>
                <th style="width:10%"><?=t('Title')?></th>
                <th style="width:5%"><?=t('Id')?></th>
                <th style="width:5%"><?=t('is a child of')?></th>
                <th style="width:5%"><?=t('Editions')?></th>
                <th style="width:5%"><?=t('Current Edition')?></th>
                <th style="width:9%"><?=t('Creator')?></th>
                <th style="width:9%"><?=t('Created')?></th>
                <th style="width:12%"><?=t('Last modifier')?></th>
                <th style="width:9%"><?=t('Modified')?></th>
            </tr>
            <?php foreach ($wikipages as $wikipage): ?>
            <tr>
                <td>
                <?php if (!$not_editable): ?>
                    <?=$this->url->link(t($wikipage['title']), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id']))?>
                    <?=$this->modal->confirm('trash-o', t(''), 'WikiController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id']))?>
                <?php else: ?>
                    <?=$this->url->link(t($wikipage['title']), 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $wikipage['id']))?>
                <?php endif ?>
                </td>
                <td>
                    <?=$wikipage['id']?>
                </td>
                <td>
                    <?php if (!$not_editable): ?>
                    <?=$this->url->link($wikipage['parent_id'], 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['parent_id']))?>
                    <?php else: ?>
                        <?=$this->url->link($wikipage['parent_id'], 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $wikipage['parent_id']))?>
                    <?php endif ?>
                </td>
                <td><?=$wikipage['editions']?></td>
                <td><?=$wikipage['current_edition']?></td>
                <td><?=$this->text->e($wikipage['creator_name'] ?: $wikipage['creator_username'])?></td>
                <td><?=$this->dt->date($wikipage['date_creation'])?></td>
                <td><?=$this->text->e($wikipage['modifier_name'] ?: $wikipage['modifier_username'])?></td>
                <td><?=$this->dt->date($wikipage['date_modification'])?></td>
            </tr>
            <?php endforeach?>
        </table>
    </div>

<?php else: ?>
    <p class="alert"><?=t('There are no Wiki pages for this project.')?></p>
<?php endif?>
</div>

<!-- $this->asset->js('plugins/Wiki/Asset/Javascript/WikiChart.js') -->
