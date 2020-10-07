<div class="wordland-property-listing-category-tabs">
    <?php echo $header; ?>
    <?php echo $tabs; ?>

    <div class="tab-content">
        <?php if ($wp_query->have_posts()): ?>
            <?php $t::render('common/loop_start'); ?>
            <?php $t::render('common/loop_end'); ?>
        <?php else: ?>
            <?php $t::render('common/content_not_found'); ?>
        <?php endif; ?>
    </div>
</div>