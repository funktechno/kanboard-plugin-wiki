<div class="page-header">
    <h2><?=t('Wiki settings')?></h2>
</div>
<form method="post" action="<?=$this->url->href('ConfigController', 'save', array('plugin' => 'Wiki'))?>" autocomplete="off">

    <?=$this->form->csrf()?>

    <fieldset>
        <legend><?=t('Editions\' settings')?></legend>
        <h2><?=t('Saving Editions:')?> <?=$values['persistEditions'] == 1 ? t('true') : t('false') ?></h2>
        <?=$this->form->checkbox('persistEditions', t('Switch Edition Saving'), 1, $values['persistEditions'] == 1)?>
    </fieldset>

    <fieldset>
        <legend><?=t('Public access')?></legend>
        <h2><?=t('Enable Wiki public access:')?> <?=$values['enable_wiki_public_access'] == 1 ? t('true') : t('false') ?></h2>
        <?=$this->form->checkbox('enable_wiki_public_access', t('Enable public access to Wiki pages (requires project public access)'), 1, $values['enable_wiki_public_access'] == 1)?>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?=t('Save')?></button>
    </div>
</form>
