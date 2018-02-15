<div class="page-header">
    <h2><?=t('Edit wikipage')?></h2>
</div>

<form method="post" action="<?=$this->url->href('WikiController', 'update', array('plugin' => 'wiki'))?>" autocomplete="off">
    <?=$this->form->csrf()?>
    
    <?=$this->form->hidden('id', $values)?>
    <?=$this->form->hidden('editions', $values)?>
    <?=$this->form->hidden('order', $values)?>
    <?=$values['id']?>
    <?=$values['editions']?>
    <?=$values['order']?>

    <?=$this->form->label(t('Title'), 'title')?>
    <?=$this->form->text('title', $values, $errors, array('required', 'maxlength="255"', 'autofocus', 'tabindex="1"'))?>

    <?=$this->form->label(t('Content'), 'content')?>
    <?=$this->form->textEditor('content', $values, $errors)?>

    <?=$this->modal->submitButtons()?>
</form>
