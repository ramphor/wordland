<div class="wordland-property-listing">
    <?php echo $header; ?>

    <?php if ($wp_query->have_posts()): ?>

        <?php do_action('wordland_before_loop'); ?>

        <?php while($wp_query->have_posts()): ?>
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

    <?php else: ?>
        <?php $t::render('common/content_not_found'); ?>
    <?php endif; ?>
</div>
