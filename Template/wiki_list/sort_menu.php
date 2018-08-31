<div class="dropdown">
    <a href="#" class="dropdown-menu dropdown-menu-link-icon"><strong><?= t('Sort') ?> <i class="fa fa-caret-down"></i></strong></a>
    <ul>
        <li>
            <?= $paginator->order(t('Project ID'), \Kanboard\Plugin\Wiki\Model\Wiki::WIKITABLE.'.id') ?>
        </li>
        <li>
            <?= $paginator->order(t('Wiki page Title'), \Kanboard\Plugin\Wiki\Model\Wiki::WIKITABLE.'.title') ?>
        </li>
        <li>
            <?= $paginator->order(t('Date Created'), \Kanboard\Plugin\Wiki\Model\Wiki::WIKITABLE.'.date_creation') ?>
        </li>
        <li>
            <?= $paginator->order(t('Date Modified'), \Kanboard\Plugin\Wiki\Model\Wiki::WIKITABLE.'.date_modification') ?>
        </li> 
    </ul>
</div>
