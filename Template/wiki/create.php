<div class="page-header">
    <h2><?= t('New Wiki page') ?></h2>
</div>

<form method="post" action="<?= $this->url->href('WikiController', 'save', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>

    <?= $this->form->hidden('id', $values) ?>
    <?= $this->form->hidden('project_id', $values) ?>

    <?= $this->form->label(t('Title'), 'title') ?>
    <?= $this->form->text('title', $values, $errors, array('required', 'maxlength="255"', 'autofocus', 'tabindex="1"')) ?>

    <?= $this->form->label(t('Content'), 'content') ?>
    <?= $this->form->textEditor('content', $values, $errors) ?>

    <?= $this->modal->submitButtons() ?>
</form>
