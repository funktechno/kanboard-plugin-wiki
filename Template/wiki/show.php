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
    <ul>
        <?php if (!empty($wikipages)): ?>
        <?php foreach ($wikipages as $wikipage): ?>

        <li >
            <?=$wikipage['title']?> <?= $this->modal->confirm('trash-o', t(''), 'WikiController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id'])) ?>
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
                <th><?=t('Title')?></th>
                <th><?=t('Content')?></th>
                <th><?=t('Id')?></th>
                <th><?=t('Order')?></th>
                <th><?=t('Editions')?></th>
                <th><?=t('Creator')?></th>
                <th><?=t('Created')?></th>
                <th><?=t('Last modifier')?></th>
                <th><?=t('Modified')?></th>
            </tr>
            <?php foreach ($wikipages as $wikipage): ?>
            <tr>
                <td><?=t($wikipage['title'])?></td>
                <td><?=t($wikipage['content'])?></td>
                <td><?=$wikipage['id']?></td>
                <td><?=$wikipage['order']?></td>
                <td><?=$wikipage['editions']?></td>
                <td><?=n($wikipage['creator_id'])?></td>
                <td><?=$this->dt->date($wikipage['date_creation'])?></td>
                <td><?=n($wikipage['modifier_id'])?></td>
                <td><?=$this->dt->date($wikipage['date_modified'])?></td>
            </tr>
            <?php endforeach?>
        </table>
    </div>

<?php else: ?>
    <p class="alert"><?=t('There is not enough data to show something.')?></p>
<?php endif?>
<?php if (!empty($daily_wiki)): ?>

<!-- <hr/> -->
   
        <table class="table-fixed table-stripped">
            <tr>
                <th><?=t('Date')?></th>
                <th><?=t('Wiki line')?></th>
                <th><?=t('Expenses')?></th>
                <th><?=t('Remaining')?></th>
            </tr>
            <?php foreach ($daily_wiki as $line): ?>
            <tr>
                <td><?=$this->dt->date($line['date'])?></td>
                <td><?=n($line['in'])?></td>
                <td><?=n($line['out'])?></td>
                <td><?=n($line['left'])?></td>
            </tr>
            <?php endforeach?>
            <?php if (!empty($wikipages)): ?>
            <?php foreach ($wikipages as $wikipage): ?>
            <tr>
                <td><?=t($wikipage['title'])?></td>
                <td><?=t($wikipage['content'])?></td>
                <td><?=n($wikipage['id'])?></td>
                <td><?=n($wikipage['order'])?></td>
            </tr>
            <?php endforeach?>
            <?php else: ?>
            <tr>
                <span class="alert"><?=t('There are no wikipages.')?></span>
            </tr>
            <?php endif?>
        </table>
    </div>

<?php else: ?>
    <p class="alert"><?=t('There is not enough data to show something.')?></p>
<?php endif?>
</div>

<?=$this->asset->js('plugins/Wiki/Asset/Javascript/WikiChart.js')?>
