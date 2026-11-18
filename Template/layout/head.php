<?php if ($this->user->getId()): ?>
    <meta name="kanboard-wiki-suggest-url" content="<?= $this->url->href('WikiAjaxController', 'suggest', array('plugin' => 'wiki', 'search' => 'SEARCH_TERM')) ?>">
<?php endif ?>
