<div class="page-header">
    <h2><?= t('Budget overview') ?></h2>
</div>

<?php if (! empty($daily_budget)): ?>
<div id="budget-chart">
    <div id="chart"
         data-date-format="<?= e('%%Y-%%m-%%d') ?>"
         data-metrics='<?= json_encode($daily_budget, JSON_HEX_APOS) ?>'
         data-labels='<?= json_encode(array('in' => t('Budget line'), 'out' => t('Expenses'), 'left' => t('Remaining'), 'value' => t('Amount'), 'date' => t('Date'), 'type' => t('Type')), JSON_HEX_APOS) ?>'></div>
</div>
<hr/>
<table class="table-fixed table-stripped">
    <tr>
        <th><?= t('Date') ?></th>
        <th><?= t('Budget line') ?></th>
        <th><?= t('Expenses') ?></th>
        <th><?= t('Remaining') ?></th>
    </tr>
    <?php foreach ($daily_budget as $line): ?>
    <tr>
        <td><?= $this->dt->date($line['date']) ?></td>
        <td><?= n($line['in']) ?></td>
        <td><?= n($line['out']) ?></td>
        <td><?= n($line['left']) ?></td>
    </tr>
    <?php endforeach ?>
</table>
<?php else: ?>
    <p class="alert"><?= t('There is not enough data to show something.') ?></p>
<?php endif ?>

<?= $this->asset->js('plugins/Budget/Asset/Javascript/BudgetChart.js') ?>
