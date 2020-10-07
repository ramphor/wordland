<?php
// Ensure visibility.
if ( empty( $property ) || ! $property->is_visible() ) {
	return;
}
?>
<li <?php post_class(array(
    'wordland-property',
    sprintf('%s-style', $style)
)); ?>>
    <?php do_action( 'wordland_before_loop_property' ); ?>

    <?php do_action( 'wordland_before_loop_property_name' ); ?>

    <?php do_action( 'wordland_loop_property_name' ); ?>

    <?php do_action( 'wordland_after_loop_property_name' ); ?>

    <?php do_action( 'wordland_after_loop_property' ); ?>
</li>
