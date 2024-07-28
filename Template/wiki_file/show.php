<section class="accordion-section <?= empty($files) && empty($images) ? 'accordion-collapsed' : '' ?>">
    <div class="accordion-content">
        <?= $this->render('wiki:wiki_file/images', array('wiki' => $wiki, 'images' => $images)) ?>
        <?= $this->render('wiki:wiki_file/files', array('wiki' => $wiki, 'files' => $files)) ?>
    </div>
</section>
