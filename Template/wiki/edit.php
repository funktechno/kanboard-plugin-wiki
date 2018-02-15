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
        <?php foreach ($wikipages as $wikipage): ?>

        <li >
            <?=$this->url->link(t($wikipage['title']), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id']))?>

            <?=$this->modal->confirm('trash-o', t(''), 'WikiController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $wikipage['id']))?>
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

<!-- TODO: new feature -->
<?php if (!empty($files) || !empty($images)): ?>
    <?=$this->hook->render('template:wiki:show:before-attachments', array('wiki' => $wiki, 'project' => $project))?>
    <?=$this->render('wiki_file/show', array(
    'wiki' => $wiki,
    'files' => $files,
    'images' => $images,
))?>
<?php endif?>

<!-- pending decision/need -->
<?php if (!empty($comments)): ?>
    <?=$this->hook->render('template:wiki:show:before-comments', array('wiki' => $wiki, 'project' => $project))?>
    <?=$this->render('wiki_comments/show', array(
    'wiki' => $wiki,
    'comments' => $comments,
    'project' => $project,
    'editable' => $this->user->hasProjectAccess('CommentController', 'edit', $project['id']),
))?>
<?php endif?>


</div>

