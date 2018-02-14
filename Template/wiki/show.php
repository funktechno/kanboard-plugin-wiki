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
            <?=$wikipage['title']?>
        </li>


        <?php endforeach?>
        <?php else: ?>
        <li class="alert alert-info">
         <?=t('There are no wikipages.')?>
        </li>
        <?php endif?>

    </ul>
</div>

<?php if (!empty($daily_wiki)): ?>

<!-- <hr/> -->
    <div class="column content">
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
</div>
<?php else: ?>
    <p class="alert"><?=t('There is not enough data to show something.')?></p>
<?php endif?>

<?=$this->asset->js('plugins/Wiki/Asset/Javascript/WikiChart.js')?>
