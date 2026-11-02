<h1>
    <span class="title">
        <?php if (! empty($wiki)): ?>
            <?= $this->url->icon('file-word-o', $this->text->e($wiki['title']), 'WikiController', 'detail', array('plugin' => 'Wiki', 'project_id' => $wiki['project_id'], 'wiki_id' => $wiki['id'])) ?>
            <?php else: ?>
            <?= $this->text->e($title) ?>
        <?php endif ?>
    </span>
</h1>
