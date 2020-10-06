<?php
namespace WordLand;

use WordLand\Compatibles\LandPress;

class Compatibles {
    protected static $instance;

    public static function getInstance() {
        if (is_null(static::$instance)){
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct() {
        $current_theme = wp_get_theme();
        $check_themes  = array_unique(array($current_theme->stylesheet, $current_theme->template));
        if (in_array('landpress', $check_themes)) {
            $this->compatibleWithLandPress();
        }
    }


    public function compatibleWithLandPress() {
        add_theme_support('render_js_template');
    }
}
