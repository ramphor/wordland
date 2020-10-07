<?php
// Ensure visibility.
if ( empty( $property ) || ! $property->is_visible() ) {
	return;
}
?>
<li <?php post_class('wordland-property'); ?>>

    <?php do_action( 'wordland_before_loop_property' ); ?>

    <?php do_action( 'wordland_before_loop_property_name' ); ?>

    <?php do_action( 'wordland_loop_property_name' ); ?>

    <?php do_action( 'wordland_after_loop_property_name' ); ?>

    <?php do_action( 'wordland_after_loop_property' ); ?>
</li>
