<div class="page-header">
    <h2><?= t('Wiki lines') ?></h2>
    <ul>
        <li>
            <?= $this->modal->medium('plus', t('New wiki line'), 'WikiLineController', 'create', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?>
        </li>
    </ul>
</div>

<?php if (! empty($lines)): ?>
    <table class="table-fixed table-stripped">
        <tr>
            <th class="column-20"><?= t('Wiki line') ?></th>
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
                <?= $this->modal->confirm('trash-o', t('Remove'), 'WikiLineController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $line['id'])) ?>
            </td>
        </tr>
        <?php endforeach ?>
    </table>
<?php else: ?>
    <p class="alert alert-info"><?= t('There is no wiki line.') ?></p>
<?php endif ?>
