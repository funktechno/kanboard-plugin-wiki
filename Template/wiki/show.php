<?= $this->projectHeader->render($project, 'TaskListController', 'show') ?>
<div class="page-header">
    <h2><?=t('Wiki overview')?></h2>
    <?=$this->modal->medium('plus', t('New Wiki page'), 'WikiController', 'create', array('plugin' => 'wiki', 'project_id' => $project['id']))?>

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

        <table class="table-fixed table-stripped">
            <tr>
                <th><?=t('Title')?></th>
                <th><?=t('Id')?></th>
                <th><?=t('Editions')?></th>
                <th><?=t('Current Edition')?></th>
                <th><?=t('Creator')?></th>
                <th><?=t('Created')?></th>
                <th><?=t('Last modifier')?></th>
                <th><?=t('Modified')?></th>
            </tr>
            <?php foreach ($wikipages as $wikipage): ?>
            <tr>
                <td>
                <?=$this->url->link(t($wikipage['title']), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id']))?>

                <?=$this->modal->confirm('trash-o', t(''), 'WikiController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id']))?>
                </td>
                <td><?=$wikipage['id']?></td>
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
