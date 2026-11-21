<?php
(isset($not_editable)) ?: $not_editable = false;
?>

<?php if (!$not_editable): ?>
    <?= $this->projectHeader->render($project, 'TaskListController', 'show') ?>
<?php endif ?>

<section class="sidebar-container wikicontent">

<?= $this->render('wiki:wiki/sidebar', array(
    'project' => $project,
    'wiki_id' => $wiki_id,
    'wikipages' => $wikipages,
    'not_editable' => $not_editable,
)) ?>

<div class="sidebar-content">
<div class="page-header">
    <h2><?=$this->text->e($wikipage['title'])?></h2>
    <?=$this->url->icon('long-arrow-left', t('Back to details'), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wiki_id))?>
    <br><br>
    <h2><?=t('Editions')?>: <?= (int) $pagination['total'] ?></h2>
    <?=t('Current Edition')?>: <strong> <?=$wikipage['current_edition']?></strong>
</div>

<?php if (!empty($editions)): ?>

<?= $this->wikiHelper->js('plugins/Wiki/Asset/Javascript/wiki-diff.js') ?>
<script type="application/json" id="wiki-edition-texts"><?= json_encode(array_column(array_merge($editions, $base_editions), 'content', 'edition'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<div class="wiki-diff-options" hidden
    data-label-lines="<?= t('Show %s unchanged lines', '%d') ?>"
    data-label-blocks="<?= t('Show %s unchanged blocks', '%d') ?>"
    data-label-same="<?= t('The content did not change.') ?>">
    <fieldset>
        <legend class="sr-only"><?= t('Show changes as') ?></legend>
        <button type="button" class="btn" data-wiki-diff-view="markdown" aria-pressed="true"><?= t('Markdown') ?></button>
        <button type="button" class="btn" data-wiki-diff-view="rendered" aria-pressed="false"><?= t('Rendered') ?></button>
    </fieldset>
    <fieldset>
        <legend class="sr-only"><?= t('Layout') ?></legend>
        <button type="button" class="btn" data-wiki-diff-layout="unified" aria-pressed="true"><?= t('Unified') ?></button>
        <button type="button" class="btn" data-wiki-diff-layout="split" aria-pressed="false"><?= t('Side by side') ?></button>
    </fieldset>
</div>
<?php if (count($editions) > 2): ?>
<form class="wiki-diff-compare" hidden>
    <label><?= t('Compare edition') ?>
        <select data-wiki-diff-from>
            <?php foreach ($editions as $index => $option): ?>
                <option value="<?= (int) $option['edition'] ?>"<?= $index === 1 ? ' selected' : '' ?>><?= (int) $option['edition'] ?></option>
            <?php endforeach ?>
        </select>
    </label>
    <label><?= t('with') ?>
        <select data-wiki-diff-to>
            <?php foreach ($editions as $index => $option): ?>
                <option value="<?= (int) $option['edition'] ?>"<?= $index === 0 ? ' selected' : '' ?>><?= (int) $option['edition'] ?></option>
            <?php endforeach ?>
        </select>
    </label>
    <button type="submit" class="btn"><?= t('Compare') ?></button>
    <div class="wiki-diff" hidden></div>
</form>
<?php endif ?>
<?= $this->render('wiki:wiki/editions_pagination', array('project' => $project, 'wiki_id' => $wiki_id, 'pagination' => $pagination)) ?>

<?php foreach ($editions as $index => $edition): ?>
<div class="wikiedition" id="edition-<?= (int) $edition['edition'] ?>" data-edition="<?= (int) $edition['edition'] ?>">
<hr style="border-top: 1px solid;border-bottom: 1px solid;">
<div class="page-header">
    <h3>
        <?=t('Title') . ': ' . $this->text->e($edition['title'])?>
        <div style="float: right">
            <span class="action" title="<?= t('Restore Wiki page edition') ?>">
                <?=$this->modal->confirm('history', '<span class="sr-only">' . t('Restore Wiki page edition') . '</span>', 'WikiController', 'confirm_restore', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wiki_id, 'edition' => $edition['edition']))?>
            </span>
            <span class="action" title="<?= t('Delete Wiki page edition') ?>">
                <?=$this->modal->confirm('trash', '<span class="sr-only">' . t('Delete Wiki page edition') . '</span>', 'WikiController', 'confirm_purge', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wiki_id, 'edition' => $edition['edition']))?>
            </span>
        </div>
    </h3>
    <h4>
        <?=t('Edition') . ': ' . $this->text->e($edition['edition'])?>
    </h4>
    <?=t('Date Creation') . ': ' . (ctype_digit((string) $edition['date_creation']) ? $this->dt->datetime((int) $edition['date_creation']) : $this->dt->date($edition['date_creation']))?>
    <?php if (! empty($authors[$edition['creator_id']])): ?>
        &middot; <?= $this->text->e($authors[$edition['creator_id']]) ?>
    <?php endif ?>
    <?php if (! empty($edition['unrecorded'])): ?>
        &middot; <em><?= t('Changes made while edition saving was off') ?></em>
    <?php endif ?>
    <?php if ($edition['base'] !== null && isset($titles[$edition['base']]) && $titles[$edition['base']] !== $edition['title']): ?>
        <div class="wiki-title-change">
            <?= t('Title changed') ?>:
            <del class="wiki-diff-removed"><?= $this->text->e($titles[$edition['base']]) ?></del>
            &rarr; <ins class="wiki-diff-added"><?= $this->text->e($edition['title']) ?></ins>
        </div>
    <?php endif ?>
</div>
<div class="page-header">
    <details<?= $index === 0 ? ' open' : '' ?>>
    <summary>
        <h4 class="wiki-summary"><?= $edition['base'] === null ? t('Content') : t('Changes since edition %d', $edition['base']) ?></h4>
        <?php if ($edition['added'] !== null): ?>
            <span class="wiki-diff-stats"><span class="wiki-diff-added">+<?= (int) $edition['added'] ?></span> <span class="wiki-diff-removed">&minus;<?= (int) $edition['removed'] ?></span></span>
        <?php endif ?>
    </summary>
        <?php if ($edition['base'] !== null): ?>
            <div class="wiki-diff" data-old="<?= (int) $edition['base'] ?>" data-new="<?= (int) $edition['edition'] ?>"></div>
        <?php endif ?>
        <article class="markdown">
            <?=$this->text->markdown($edition['content'])?>
        </article>
    </details>
</div>
</div>

<?php endforeach ?>

<?php foreach ($base_editions as $edition): ?>
<div class="wikiedition" data-edition="<?= (int) $edition['edition'] ?>" hidden>
    <article class="markdown"><?=$this->text->markdown($edition['content'])?></article>
</div>
<?php endforeach ?>

<?= $this->render('wiki:wiki/editions_pagination', array('project' => $project, 'wiki_id' => $wiki_id, 'pagination' => $pagination)) ?>

<?php else: ?>
    <p class="alert"><?=t('There are no editions for this Wiki page saved to restore.')?></p>
<?php endif?>

</div>
<!-- end sidebar-content-->

</section>
<!--end sidebar-container-->
