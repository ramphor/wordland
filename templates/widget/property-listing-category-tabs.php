<div class="wordland-property-listing category-tabs">
    <?php echo $header; ?>
    <?php echo $tabs; ?>

    <div class="tab-content">
        <?php if ($wp_query->have_posts()) : ?>
            <?php do_action('wordland_before_loop'); ?>

            <?php while ($wp_query->have_posts()) : ?>
                <?php $wp_query->the_post(); ?>
                <?php
                $t::render(
                    'content/property',
                    array(
                        'property' => $GLOBALS['property'],
                        'style' => $style,
                    )
                );
                ?>
            <?php endwhile; ?>

            <?php do_action('wordland_end_loop'); ?>

            <?php $t::render('common/load-more'); ?>

        <?php else : ?>
            <?php $t::render('common/content_not_found'); ?>
        <?php endif; ?>
    </div>
</div>