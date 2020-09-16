<?php
use WordLand\PostTypes;

use WordLand\Admin\Property\MetaBox\PropertyInformation;

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
            $propertyMetabox = new PropertyInformation();
            add_action('add_meta_boxes', array($propertyMetabox, 'registerMetaboxes'));
        }
    }
}
