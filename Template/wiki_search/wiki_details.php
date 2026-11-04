<div class="table-list-details">
    <ul>
        <li><?=t('Project')?>: <strong><?=$this->model->projectModel->getById($wiki['project_id'])['name']?></strong></li>

        <?php if ($wiki['creator_id'] > 0): ?>
            <li><?=t('Creator')?>: <strong><?=$this->text->e($wiki['creator_name'] ?: $wiki['creator_username'])?></strong></li>
        <?php endif?>
        <?php if ($wiki['modifier_id'] > 0): ?>
            <li><?=t('Last modifier')?>: <strong><?=$this->text->e($wiki['modifier_name'] ?: $wiki['modifier_username'])?></strong></li>
        <?php endif?>

        <li><?=t('Editions')?>: <strong><?=$wiki['editions']?></strong> <?=t('Current Edition')?>: <strong> <?=$wiki['current_edition']?></strong></li>

        <li><?=t('Date Created')?>: <strong><?=$this->dt->date($wiki['date_creation'])?></strong></li>

        <li><?=t('Date Modified')?>: <strong><?=$this->dt->date($wiki['date_modification'])?></strong></li>

        <?php if ($wiki['content']): ?>
            <li>
                <details>
                <summary><h4 style="display:inline-block"><?=t('Content')?></h4></summary>
                    <article class="markdown">
                        <?=$this->text->markdown($wiki['content'])?>
                    </article>
                </details>
            </li>
        <?php endif?>

    </ul>
</div>