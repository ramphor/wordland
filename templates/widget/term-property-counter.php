<div class="wordland-property-counter" style="height: <?php echo $height; ?>px;">
    <h3 class="header">
        <a href="<?php echo get_term_link($term); ?>" title="<?php echo $header; ?>"><?php echo $header; ?></a>
    </h3>
    <?php if($description): ?><div class="desc"><?php echo $description; ?></div><?php endif; ?>
    <div class="counter"><?php echo $count; ?></div>
</div>
