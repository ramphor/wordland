<?php
use WordLand\Admin;
use WordLand\PostTypes;

class WordLand
{
    protected static $instance;

    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        $this->initFeatures();
    }

    public function initFeatures()
    {
        new PostTypes();
        if (is_admin()) {
            new Admin();
        }
    }
}
