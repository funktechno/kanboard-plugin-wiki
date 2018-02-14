<div class="page-header">
    <h2><?= t('New budget line') ?></h2>
</div>

<form method="post" action="<?= $this->url->href('BudgetLineController', 'save', array('plugin' => 'budget', 'project_id' => $project['id'])) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>

    <?= $this->form->hidden('id', $values) ?>
    <?= $this->form->hidden('project_id', $values) ?>

    <?= $this->form->label(t('Amount'), 'amount') ?>
    <?= $this->form->text('amount', $values, $errors, array('required', 'autofocus'), 'form-numeric') ?>

    <?= $this->form->label(t('Date'), 'date') ?>
    <?= $this->form->text('date', $values, $errors, array('required'), 'form-date') ?>

    <?= $this->form->label(t('Comment'), 'comment') ?>
    <?= $this->form->text('comment', $values, $errors) ?>

    <?= $this->modal->submitButtons() ?>
</form>
