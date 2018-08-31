<?php if ($this->user->hasAccess('WikiController', 'show')): ?>
    <li><?= $this->url->icon('book', t('Wiki overview'), 'WikiController', 'index', array('plugin' => 'Wiki')) ?></li>
<?php endif ?>