<ul class="wordland-tabs">
    <?php foreach ($tabs as $tab) : ?>
    <li class="tab<?php echo isset($tab['class']) ? ' ' . $tab['class'] : ''; ?>"
        data-tab_type="<?php echo $tab['type']; ?>"
        data-tab_data="<?php echo $tab['object_type']; ?>"
        data-tab_id="<?php echo $tab['object_id']; ?>"
    >
        <?php if (isset($tab['url'])) : ?>
        <a href="<?php echo $tab['url']; ?>" title="<?php echo $tab['text']; ?>"><?php echo $tab['text']; ?></a>
        <?php else : ?>
        <span><?php echo $tab['text']; ?></span>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ul>
