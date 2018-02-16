<section class="accordion-section <?= empty($files) && empty($images) ? 'accordion-collapsed' : '' ?>">
    <div class="accordion-title">
        <h3><a href="#" class="fa accordion-toggle"></a> <?= t('Attachments') ?></h3>
    </div>
    <div class="accordion-content">
        <?= $this->render('wiki:wiki_file/images', array('wiki' => $wiki, 'images' => $images)) ?>
        <?= $this->render('wiki:wiki_file/files', array('wiki' => $wiki, 'files' => $files)) ?>
    </div>
</section>
