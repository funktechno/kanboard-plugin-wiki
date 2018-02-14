<div class="page-header">
    <h2><?= t('Hourly rate') ?></h2>
    <ul>
        <li>
            <?= $this->modal->medium('plus', t('New hourly rate'), 'HourlyRateController', 'create', array('plugin' => 'budget', 'user_id' => $user['id'])) ?>
        </li>
    </ul>
</div>

<?php if (! empty($rates)): ?>
    <table>
        <tr>
            <th><?= t('Hourly rate') ?></th>
            <th><?= t('Currency') ?></th>
            <th><?= t('Effective date') ?></th>
            <th><?= t('Action') ?></th>
        </tr>
        <?php foreach ($rates as $rate): ?>
        <tr>
            <td><?= n($rate['rate']) ?></td>
            <td><?= $rate['currency'] ?></td>
            <td><?= $this->dt->date($rate['date_effective']) ?></td>
            <td>
                <?= $this->modal->confirm('trash-o', t('Remove'), 'HourlyRateController', 'confirm', array('plugin' => 'budget', 'user_id' => $user['id'], 'rate_id' => $rate['id'])) ?>
            </td>
        </tr>
        <?php endforeach ?>
    </table>
<?php else: ?>
    <p class="alert"><?= t('There is no hourly rate defined.') ?></p>
<?php endif ?>
