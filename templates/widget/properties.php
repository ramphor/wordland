<div class="wordland-property-listing">
    <?php echo $header; ?>

    <?php if ($wp_query->have_posts()) :
        if (!isset($columns)) {
            $columns = $style === 'horizontal-card' ? 2 : 4;
        }
        ?>
        <?php do_action('wordland_before_loop', array(
            'style' => $style,
            'columns' => $columns
        )); ?>

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
        <?php wp_reset_postdata(); ?>

        <?php do_action('wordland_end_loop'); ?>

    <?php else : ?>
        <?php $t::render('common/content_not_found'); ?>
    <?php endif; ?>
</div>
