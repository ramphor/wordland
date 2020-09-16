<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header( 'property' );
?>

    <?php do_action('wordland_before_main_content'); ?>

        <?php while (have_posts()): ?>

            <?php the_post(); ?>

            <?php wordland_template('content/single-property', array(
                'property' => apply_filters('wordland_setup_full_property', null, $GLOBALS['post'])
            )); ?>

        <?php endwhile; ?>

    <?php do_action('wordland_after_main_content'); ?>

    <?php do_action('wordland_sidebar'); ?>

<?php
get_footer( 'property' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */