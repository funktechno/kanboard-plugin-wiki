<section class="accordion-section <?= empty($files) && empty($images) ? 'accordion-collapsed' : '' ?>">
    <div class="accordion-content">
        <?php $public_token = isset($public_token) ? $public_token : '' ?>
        <?= $this->render('wiki:wiki_file/images', array('wiki' => $wiki, 'images' => $images, 'public_token' => $public_token)) ?>
        <?= $this->render('wiki:wiki_file/files', array('wiki' => $wiki, 'files' => $files, 'public_token' => $public_token)) ?>
    </div>
</section>
