<div class="page-header">
    <h2><?=t('Wiki overview')?></h2>
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
<div class="sidebar column list">
    <ul>
        <?php if (!empty($wikipages)): ?>
        <?php foreach ($wikipages as $page): ?>

        <li >
            <?=$this->url->link(t($page['title']), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $page['id']))?>

            <?=$this->modal->confirm('trash-o', t(''), 'WikiController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $page['id']))?>
        </li>


        <?php endforeach?>
        <?php else: ?>
        <li class="alert alert-info">
            <?=t('There are no wikipages.')?>
        </li>
        <?php endif?>
        <li>
            <?=$this->modal->medium('plus', t('New wikipage'), 'WikiController', 'create', array('plugin' => 'wiki', 'project_id' => $project['id']))?>
        </li>

    </ul>
</div>

<div class="column content">
<div class="page-header">
    <h2><?=t($wiki_id)?>   <?=t($wikipage['title'])?></h2>
</div>
<ul class="panel">
    <?php if ($wikipage['creator_id'] > 0): ?>
        <li><?=t('Creator: ')?><strong><?=$this->text->e($wikipage['creator_name'] ?: $wikipage['creator_username'])?></strong></li>
    <?php endif?>
    <?php if ($wikipage['modifier_id'] > 0): ?>
        <li><?=t('Creator: ')?><strong><?=$this->text->e($wikipage['modifier_username'] ?: $wikipage['modifier_username'])?></strong></li>
    <?php endif?>
    <li><?=t('Editions: ')?><strong><?=$wikipage['editions']?></strong> <?=t('Current Edition: ')?><strong> <?=$wikipage['current_edition']?></strong></li>
    <li>
        <?=$this->url->link(t('View Editions'), 'WikiController', 'editions', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id']))?>
    </li>
</ul>

<?php if (!empty($wikipage['content'])): ?>
    <div class="page-header">
        <h2><?=t('Content')?></h2>
    </div>

    <article class="markdown">
        <?=$this->text->markdown($wikipage['content'])?>
    </article>
<?php endif?>

<div class="page-header">
        <h2><?=t('Attachments')?></h2>
</div>
<ul>
    <?= $this->modal->medium('file', t('Attach a document'), 'WikiFileController', 'create', array('wiki_id' => $wikipage['id'], 'project_id' => $wikipage['project_id'])) ?>
</ul>


</div>

