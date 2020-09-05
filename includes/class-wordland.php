<?php
class WordLand {
	protected static $instance;

	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	private function __construct() {
		$this->includes();
	}

	protected function includes() {
		require_once dirname( __FILE__ ) . '/class-wordland-post-types.php';
	}
}
