<div class="table-list-header">
    <div class="table-list-header-count">
        <h2 style="margin:0"><strong><?= t('Wiki pages') . ' : ' . $paginator->getTotal() ?></strong></h2>
    </div>
    <div class="table-list-header-menu">
        <?= $this->render('Wiki:wiki_list/sort_menu', array('paginator' => $paginator)) ?>
    </div>
</div>
