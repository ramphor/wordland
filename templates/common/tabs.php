<ul class="wordland-tabs">
    <?php foreach($tabs as $tab): ?>
    <li class="tab<?php echo isset($tab['class']) ? ' ' . $tab['class'] : ''; ?>">
        <?php if (isset($tab['url'])): ?>
        <a href="<?php echo $tab['url']; ?>" title="<?php echo $tab['text']; ?>"><?php echo $tab['text']; ?></a>
        <?php else: ?>
        <span><?php echo $tab['text']; ?></span>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ul>