<div class="page-header">
    <h2><?=t('Wiki overview')?></h2>
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
    <h2 style="background-color: white;">Title</h2>
    <ul>
        <?php if (!empty($wikipages)): ?>
        <?php foreach ($wikipages as $wikipage): ?>

        <li >
            <?=$wikipage['title']?> 
            <?= $this->url->link(t($wikipage['title']), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' =>$wikipage['id'])) ?>

            <?= $this->modal->confirm('trash-o', t(''), 'WikiController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id'])) ?>

        </li>


        <?php endforeach?>
        <?php else: ?>
        <li class="alert alert-info">
            <?=t('There are no wikipages.')?>
        </li>
        <?php endif?>
        <li>
            <?= $this->modal->medium('plus', t('New wikipage'), 'WikiController', 'create', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?>
        </li>

    </ul>
</div>

<div class="column content">
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
                <td><?=$wikipage['id']?></td>
                <td><?=$wikipage['editions']?></td>
                <td><?=$wikipage['current_edition']?></td>
                <td><?=t($wikipage['creator_name'])?></td>
                <td><?=$this->dt->date($wikipage['date_creation'])?></td>
                <td><?=t($wikipage['modifier_name'])?></td>
                <td><?=$this->dt->date($wikipage['date_modification'])?></td>
            </tr>
            <?php endforeach?>
        </table>
    </div>

<?php else: ?>
    <p class="alert"><?=t('There is not enough data to show something.')?></p>
<?php endif?>
</div>

<!-- $this->asset->js('plugins/Wiki/Asset/Javascript/WikiChart.js') -->
