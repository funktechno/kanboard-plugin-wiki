<?php if (! empty($images)): ?>
    <div class="file-thumbnails">
        <?php foreach ($images as $file): ?>
            <div class="file-thumbnail">
                <?= $this->app->component('image-slideshow', array(
                    'images' => $images,
                    'image' => $file,
                    'regex' => 'FILE_ID',
                    'url' => array(
                        'image'     => $this->url->to('WikiFileViewController', 'image', array('plugin' => 'wiki', 'file_id' => 'FILE_ID')),
                        'thumbnail' => $this->url->to('WikiFileViewController', 'thumbnail', array('plugin' => 'wiki', 'file_id' => 'FILE_ID')),
                        'download'  => $this->url->to('WikiFileViewController', 'download', array('plugin' => 'wiki', 'file_id' => 'FILE_ID')),
                    )
                )) ?>

                <div class="file-thumbnail-content">
                    <div class="file-thumbnail-title">
                        <div class="dropdown">
                            <a href="#" class="dropdown-menu dropdown-menu-link-text" title="<?= $this->text->e($file['name']) ?>"><?= $this->text->e($file['name']) ?> <i class="fa fa-caret-down"></i></a>
                            <ul>
                                <li>
                                    <?= $this->url->icon('download', t('Download'), 'WikiFileViewController', 'download', array('plugin' => 'wiki', 'file_id' => $file['id'], 'file_id' => $file['id'])) ?>
                                </li>
                                <?php if ($this->user->hasProjectAccess('WikiFileController', 'remove', $wiki['project_id'])): ?>
                                    <li>
                                        <?= $this->modal->confirm('trash-o', t('Remove'), 'WikiFileController', 'confirm', array('plugin' => 'wiki', 'wiki_id' => $wiki['id'], 'project_id' => $wiki['project_id'], 'file_id' => $file['id'])) ?>
                                    </li>
                                <?php endif ?>
                            </ul>
                        </div>
                    </div>
                    <div class="file-thumbnail-description">
                            <span class="tooltip" title='<?= t('Uploaded: %s', $this->dt->datetime($file['date'])).'&#013;'.t('Size: %s', $this->text->bytes($file['size'])) ?>'>
                                <i class="fa fa-info-circle"></i>
                            </span>
                        <?php if (! empty($file['user_id'])): ?>
                            <?= t('Uploaded by %s', $file['user_name'] ?: $file['username']) ?>
                        <?php else: ?>
                            <?= t('Uploaded: %s', $this->dt->datetime($file['date'])) ?>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>
