<div class="page-header">
    <h2><?= t('Wiki settings') ?></h2>
</div>
<form method="post" action="<?= $this->url->href('ConfigController', 'save', array('plugin' => 'Wiki')) ?>" autocomplete="off">

    <?= $this->form->csrf() ?>

    <fieldset>
        <legend><?= t('Editions\' settings') ?></legend>
        <?= json_encode($values) ?>

         <!-- $this->form->checkbox('persistEditions','Save Editions', 1, true)  -->

        <?= $this->form->checkbox('persistEditions','Save Editions', 1, $values['persistEditions']) ?>

         <!-- $this->form->checkbox('password_reset', t('Enable "Forget Password"'), 1, $values['password_reset'] == 1) -->

        
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?= t('Save') ?></button>
    </div>
</form>
