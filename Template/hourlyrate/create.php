<div class="page-header">
    <h2><?= t('Add new rate') ?></h2>
</div>

<form method="post" action="<?= $this->url->href('HourlyRateController', 'save', array('plugin' => 'budget', 'user_id' => $user['id'])) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>
    <?= $this->form->hidden('user_id', $values) ?>

    <?= $this->form->label(t('Hourly rate'), 'rate') ?>
    <?= $this->form->text('rate', $values, $errors, array('required'), 'form-numeric') ?>

    <?= $this->form->label(t('Currency'), 'currency') ?>
    <?= $this->form->select('currency', $currencies_list, $values, $errors, array('required')) ?>

    <?= $this->form->label(t('Effective date'), 'date_effective') ?>
    <?= $this->form->text('date_effective', $values, $errors, array('required'), 'form-date') ?>

    <?= $this->modal->submitButtons() ?>
</form>
