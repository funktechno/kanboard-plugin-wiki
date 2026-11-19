<div class="page-header">
    <h2><?=t('Wiki settings')?></h2>
</div>
<form method="post" action="<?=$this->url->href('ConfigController', 'save', array('plugin' => 'wiki'))?>" autocomplete="off">

    <?=$this->form->csrf()?>

    <fieldset>
        <legend><?=t('Editions\' settings')?></legend>
        <h2><?=t('Saving Editions:')?> <?=$values['persistEditions'] == 1 ? t('true') : t('false') ?></h2>
        <?=$this->form->checkbox('persistEditions', t('Switch Edition Saving'), 1, $values['persistEditions'] == 1)?>
    </fieldset>

    <fieldset>
        <legend><?=t('Public access')?></legend>
        <?=$this->form->checkbox('wiki_public_access', t('Show the wiki of public projects through their public link'), 1, $values['wiki_public_access'] == 1)?>
        <?=$this->form->checkbox('wiki_public_attachments', t('Show attachments on public wiki pages'), 1, $values['wiki_public_attachments'] == 1)?>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?=t('Save')?></button>
    </div>
</form>

<form method="post" action="<?=$this->url->href('ConfigController', 'editions', array('plugin' => 'wiki'))?>">
    <?=$this->form->csrf()?>
    <fieldset>
        <legend><?=t('Edition history storage')?></legend>
        <p class="form-help"><?=t('New editions are stored as changes from the previous edition. Compact rewrites older editions the same way. Expand rewrites every edition as a full copy: do this before installing an older version of this plugin.')?></p>
        <button type="submit" class="btn" name="mode" value="compact"><?=t('Compact edition history')?></button>
        <button type="submit" class="btn" name="mode" value="expand"><?=t('Expand edition history')?></button>
    </fieldset>
</form>
