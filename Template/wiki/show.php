<div class="page-header">
    <h2><?= t('Wiki overview') ?></h2>
</div>

<?php if (! empty($daily_wiki)): ?>
<div id="wiki-chart">
    <div id="chart"
         data-date-format="<?= e('%%Y-%%m-%%d') ?>"
         data-metrics='<?= json_encode($daily_wiki, JSON_HEX_APOS) ?>'
         data-labels='<?= json_encode(array('in' => t('Wiki line'), 'out' => t('Expenses'), 'left' => t('Remaining'), 'value' => t('Amount'), 'date' => t('Date'), 'type' => t('Type')), JSON_HEX_APOS) ?>'></div>
</div>
<hr/>
<table class="table-fixed table-stripped">
    <tr>
        <th><?= t('Date') ?></th>
        <th><?= t('Wiki line') ?></th>
        <th><?= t('Expenses') ?></th>
        <th><?= t('Remaining') ?></th>
    </tr>
    <?php foreach ($daily_wiki as $line): ?>
    <tr>
        <td><?= $this->dt->date($line['date']) ?></td>
        <td><?= n($line['in']) ?></td>
        <td><?= n($line['out']) ?></td>
        <td><?= n($line['left']) ?></td>
    </tr>
    <?php endforeach ?>
    <?php if (! empty($wikipages)): ?>
    <?php foreach ($wikipages as $wikipage): ?>
    <tr>
        <td><?= t($wikipage['title']) ?></td>
        <td><?= t($wikipage['content']) ?></td>
        <td><?= n($wikipage['id']) ?></td>
        <td><?= n($wikipage['order']) ?></td>
    </tr>
    <?php endforeach ?>
    <?php else: ?>
    <tr>
        <span class="alert"><?= t('There are no wikipages.') ?></span>
    </tr>
    <?php endif ?>
</table>
<?php else: ?>
    <p class="alert"><?= t('There is not enough data to show something.') ?></p>
<?php endif ?>

<?= $this->asset->js('plugins/Wiki/Asset/Javascript/WikiChart.js') ?>
