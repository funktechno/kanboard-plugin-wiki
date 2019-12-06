<div class="page-header">
    <h2><?=t('Wiki settings')?></h2>
</div>
<form method="post" action="<?=$this->url->href('ConfigController', 'save', array('plugin' => 'Wiki'))?>" autocomplete="off">

    <?=$this->form->csrf()?>

    <fieldset>
        <legend><?=t('Editions\' settings')?></legend>
        <h2><?=t('Saving Editions:')?> <?=$values['persistEditions'] == 1 ? t('true') : t('false') ?></h2>
        <?=$this->form->checkbox('persistEditions', t('Switch Edition Saving'), $values['persistEditions'] == 1 ? 0 : 1, true)?>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?=t('Save')?></button>
    </div>
</form>
