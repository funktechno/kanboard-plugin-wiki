<div class="table-list-header">
    <div class="table-list-header-count">
        <?php if ($paginator->getTotal() > 1): ?>
            <?= t('%d Wiki pages', $paginator->getTotal()) ?>
        <?php else: ?>
            <?= t('%d Wiki page', $paginator->getTotal()) ?>
        <?php endif ?>
    </div>
    <div class="table-list-header-menu">
        <?= $this->render('Wiki:wiki_list/sort_menu', array('paginator' => $paginator)) ?>
    </div>
</div>
