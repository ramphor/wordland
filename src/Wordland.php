<?php
class WordLand {
	protected static $instance;

	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}
}
