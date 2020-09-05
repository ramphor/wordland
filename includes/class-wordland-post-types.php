<?php

class WordLand_Post_Types {
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_statuses' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
    }

    public function register_post_statuses() {
    }

	public function register_post_types() {
		$labels = array(
			'name'         => __( 'Properties', 'wordland' ),
			'plural_name'  => __( 'Property', 'wordland' ),
			'add_new_item' => __( 'Add New Property', 'wordland' ),
		);

		register_post_type(
			'property',
			apply_filters(
				'wordland_post_type_property_args',
				array(
					'labels'   => $labels,
					'public'   => true,
					'supports' => array( 'title', 'editor' ),
				)
			)
		);
	}
}

new WordLand_Post_Types();
