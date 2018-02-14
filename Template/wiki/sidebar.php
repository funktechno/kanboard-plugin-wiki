<div class="sidebar">
    <ul>
        <li <?= $this->app->checkMenuSelection('WikiController', 'show') ?>>
            <?= $this->url->link(t('Wiki overview'), 'WikiController', 'show', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?>
        </li>
        <li <?= $this->app->checkMenuSelection('WikiLineController', 'show') ?>>
            <?= $this->url->link(t('Wiki lines'), 'WikiLineController', 'show', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?>
        </li>
        <?php 
        $wikipages = $this->wikiHelper->getWikipages($project['id']) 
        ?>

        <?php if (! empty($wikipages)): ?>
        <?php foreach ($wikipages as $wikipage): ?>
        
        <li >
            <?= $wikipage['title'] ?>
        </li>

       
        <?php endforeach ?>
        <?php else: ?>
        <li class="alert alert-info">
            <?= t('There are no wikipages.') ?>
        </li>
        <?php endif ?>

    </ul>
</div>
