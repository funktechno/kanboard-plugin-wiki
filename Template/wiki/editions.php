<?php
(isset($not_editable)) ?: $not_editable = false;
?>

<?php if (!$not_editable): ?>
    <?= $this->projectHeader->render($project, 'TaskListController', 'show') ?>
<?php endif ?>

<section class="sidebar-container">

<?= $this->render('wiki:wiki/sidebar', array(
    'project' => $project,
    'wiki_id' => $wiki_id,
    'wikipages' => $wikipages,
    'not_editable' => $not_editable,
)) ?>

<div class="sidebar-content">
<div class="page-header">
    <h2><?=t($wikipage['title'])?></h2>
    <?=$this->url->icon('long-arrow-left', t('Back to details'), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wiki_id))?>
    <br><br>
    <h2><?=t('Editions')?>:</h2>
</div>

<?php if (!empty($editions)): ?>

<?php foreach ($editions as $edition): ?>
<hr style="border-top: 1px solid;border-bottom: 1px solid;">
<div class="page-header">
    <h3>
        <?=t('Title') . ': ' . t($edition['title'])?>
        <?=$this->modal->confirm('undo', t(''), 'WikiController', 'confirm_restore', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wiki_id, 'edition' => $edition['edition']))?>
    </h3>
    <h4>
        <?=t('Edition') . ': ' . t($edition['edition'])?>
    </h4>
    <?=t('Date Creation') . ': ' . $this->dt->date($edition['date_creation'])?>
</div>
<div class="page-header">
    <details>
    <summary><h4 style="display:inline-block"><?=t('Content')?></h4></summary>
        <article class="markdown">
            <?=$this->text->markdown($edition['content'])?>
        </article>
    </details>
</div>

<?php endforeach?>
<!-- `edition` INT NOT NULL,
        `title` varchar(255) NOT NULL,
        `content` TEXT,
        `creator_id` int(11) DEFAULT 0,
        `date_creation` VARCHAR(10) DEFAULT NULL, -->

<!-- <hr/> -->
<!-- Title
Editions
Creator
Created
Last modifier
Modified -->

<?php else: ?>
    <p class="alert"><?=t('There are no editions for this Wiki page saved to restore.')?></p>
<?php endif?>

</div>
<!-- end sidebar-content-->

</section>
<!--end sidebar-container-->
