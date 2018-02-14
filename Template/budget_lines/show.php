<div class="page-header">
    <h2><?= t('Budget lines') ?></h2>
    <ul>
        <li>
            <?= $this->modal->medium('plus', t('New budget line'), 'BudgetLineController', 'create', array('plugin' => 'budget', 'project_id' => $project['id'])) ?>
        </li>
    </ul>
</div>

<?php if (! empty($lines)): ?>
    <table class="table-fixed table-stripped">
        <tr>
            <th class="column-20"><?= t('Budget line') ?></th>
            <th class="column-20"><?= t('Date') ?></th>
            <th><?= t('Comment') ?></th>
            <th><?= t('Action') ?></th>
        </tr>
        <?php foreach ($lines as $line): ?>
        <tr>
            <td><?= n($line['amount']) ?></td>
            <td><?= $this->dt->date($line['date']) ?></td>
            <td><?= $this->helper->text->e($line['comment']) ?></td>
            <td>
                <?= $this->modal->confirm('trash-o', t('Remove'), 'BudgetLineController', 'confirm', array('plugin' => 'budget', 'project_id' => $project['id'], 'budget_id' => $line['id'])) ?>
            </td>
        </tr>
        <?php endforeach ?>
    </table>
<?php else: ?>
    <p class="alert alert-info"><?= t('There is no budget line.') ?></p>
<?php endif ?>
