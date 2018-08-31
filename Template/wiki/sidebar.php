<!-- no longer used -->
<div class="sidebar">
    <ul>
        <li <?= $this->app->checkMenuSelection('WikiController', 'show') ?>>
            <?= $this->url->link(t('Wiki overview'), 'WikiController', 'show', array('plugin' => 'wiki', 'project_id' => $project['id'])) ?>
        </li>
        

        <?php if (! empty($wikipages)): ?>
        <?php foreach ($wikipages as $wikipage): ?>
        
        <li >
            <?= $wikipage['title'] ?>
        </li>

       
        <?php endforeach ?>
        <?php //else: ?>
        <!-- <li class="alert alert-info"> -->
            <!--  //t('There are no Wiki pages.')  -->
        <!-- </li> -->
        <?php endif ?>

    </ul>
</div>
