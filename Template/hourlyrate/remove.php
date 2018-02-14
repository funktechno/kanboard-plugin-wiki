<div class="page-header">
    <h2><?= t('Remove hourly rate') ?></h2>
</div>

<div class="confirm">
    <p class="alert alert-info"><?= t('Do you really want to remove this hourly rate?') ?></p>
    <?= $this->modal->confirmButtons('HourlyRateController', 'remove', array('plugin' => 'budget', 'user_id' => $user['id'], 'rate_id' => $rate_id)) ?>
</div>
