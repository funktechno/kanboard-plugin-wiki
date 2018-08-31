<div class="page-header">
    <h2><?=t('Wiki page Editions:')?></h2>
    <br>
    
    <?=$this->url->icon('long-arrow-alt-left', t('Back to details'), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wiki_id))?>


</div>

<style>
    .clearfix::after {
        content: "";
        clear: both;
        display: table;
    }
    .column {
        float: left;
        min-width: 0;
    }
    .list {
    width: 25%;
}
    .content {
    width: 75%;
}

</style>
<div class="clearfix">

<div class="column ">
<?php if (!empty($editions)): ?>

<?php foreach ($editions as $edition): ?>
<div class="page-header">
    <h2>
        <?=t('Title') . ': ' . t($edition['title'])?>
        <?=$this->modal->confirm('undo', t(''), 'WikiController', 'confirm_restore', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wiki_id, 'edition' => $edition['edition']))?>
        <br>
        <?=t('Edition') . ': ' . t($edition['edition'])?>
    </h2>

</div>
<div>
    <?=t('Date Creation') . ': ' . $this->dt->date($edition['date_creation'])?>
</div>
<div class="page-header">
        <h2><?=t('Content')?></h2>
</div>

<article class="markdown">
    <?=$this->text->markdown($edition['content'])?>
</article>


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

<!-- $this->asset->js('plugins/Wiki/Asset/Javascript/WikiChart.js') -->
