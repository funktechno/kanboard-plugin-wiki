<div class="page-header">
    <h2><?= $this->text->e($file['name']) ?></h2>
</div>
<div class="file-viewer">
    <?php if ($file['is_image']): ?>
        <img src="<?= $this->url->href('WikiFileViewController', 'image', $params, array('plugin' => 'wiki', 'project_id' => $wiki['project_id'], 'wikipage_id' => $wiki['id'], 'file_id' => $file['id'])) ?>" alt="<?= $this->text->e($file['name']) ?>">
    <?php elseif ($type === 'markdown'): ?>
        <article class="markdown">
            <?= $this->text->markdown($content) ?>
        </article>
    <?php elseif ($type === 'text'): ?>
        <pre><?= $content ?></pre>
    <?php endif ?>
</div>
