<div class="page-header">
    <ul>
        <?= $this->hook->render('template:project-list:menu:before') ?>

        <?= $this->hook->render('template:project-list:menu:after') ?>
    </ul>
</div>

<div class="margin-bottom">
    <form method="get" action="<?= $this->url->dir() ?>" class="search">
        <?= $this->form->hidden('controller', array('controller' => 'WikiController')) ?>
        <?= $this->form->hidden('action', array('action' => 'index')) ?>
		<?= $this->form->hidden('plugin', array('plugin' => 'Wiki')) ?>
        <?= $this->form->text('search', $values, array(), array('placeholder="'.t('Search by content').'"')) ?>
    </form>
</div>

<?php if ($paginator->isEmpty()): ?>
    <p class="alert"><?= t('There are no Wiki pages that you have access to.') ?></p>
<?php else: ?>
    <div class="table-list">
        <?= $this->render('Wiki:wiki_list/header', array('paginator' => $paginator)) ?>
        <?php foreach ($paginator->getCollection() as $wiki): ?>
            <div class="table-list-row table-border-left">
                <?= $this->render('Wiki:wiki_list/wiki_title', array(
                    'wiki' => $wiki,
                )) ?>

                <?= $this->render('Wiki:wiki_list/wiki_details', array(
                    'wiki' => $wiki,
                )) ?>
            </div>
        <?php endforeach ?>
    </div>

    <?= $paginator ?>
<?php endif ?>
