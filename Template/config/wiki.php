<div class="page-header">
    <h2><?=t('Wiki settings')?></h2>
</div>
<form method="post" action="<?=$this->url->href('ConfigController', 'save', array('plugin' => 'Wiki'))?>" autocomplete="off">

    <?=$this->form->csrf()?>

    <fieldset>
        <legend><?=t('Editions\' settings')?></legend>
        <?=json_encode($values)?>
        <?=$values['persistEditions'] == 1?>


         <!-- $this->form->checkbox('persistEditions','Save Editions', 1, true)  -->

        
        <!-- // $this->form->checkbox('persistEditions', 'Save Editions', 1, $values['persistEditions'] == 1) -->
        
        <!-- <input type="checkbox" name="persistEditions" class="" value="1" checked="checked"> -->
        <?=`<input name="persistEditions" type="checkbox" disabled="disabled"  value=` . ($values['persistEditions'] == 1 ? "0" : "1") . ($values['persistEditions'] == 1 ? " checked>" : ">")?>
         <!-- $this->form->checkbox('password_reset', t('Enable "Forget Password"'), 1, $values['password_reset'] == 1) -->


    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?=t('Switch')?></button>
    </div>
</form>
