<div class="table-list-header">
    <div class="table-list-header-count">
        <?php if ($paginator->getTotal() > 1): ?>
            <?= t('%d Wikipages', $paginator->getTotal()) ?>
        <?php else: ?>
            <?= t('%d Wikipage', $paginator->getTotal()) ?>
        <?php endif ?>
    </div>
    <div class="table-list-header-menu">
        <?= $this->render('Wiki:wiki_list/sort_menu', array('paginator' => $paginator)) ?>
    </div>
</div>
