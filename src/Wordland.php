<?php
use WordLand\PostTypes;
use WordLand\Locations;
use WordLand\TemplateLoader;
use WordLand\Admin\Admin;
use WordLand\DataLoader;
use WordLand\AjaxRequestManager;
use WordLand\Scripts;
use WordLand\Compatibles;

class WordLand
{
    protected static $instance;
    public static $version;

    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        $this->defineConstants();
        $this->includes();
        $this->initFeatures();
    }

    private function define($name, $value)
    {
        if (defined($name)) {
            return;
        }
        return define($name, $value);
    }

    private function defineConstants()
    {
        $data = get_file_data(WORDLAND_PLUGIN_FILE, array('version' => 'Version'));
        static::$version = isset($data['version']) ? $data['version'] : null;

        $this->define('WORDLAND_ABSPATH', dirname(WORDLAND_PLUGIN_FILE));
        $this->define('WORDLAND_TEMPLATE_DEBUG_MODE', false);
    }

    private function is_request($request)
    {
        switch ($request) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return ( ! is_admin() || defined('DOING_AJAX') ) && ! defined('DOING_CRON');
        }
    }

    public function includes()
    {
        if ($this->is_request('frontend')) {
            $this->include_frontend();
        }

        $helpers_file = realpath(dirname(__FILE__) . '/../vendor/jankx/helpers/src/helpers.php');
        if (!function_exists('array_get') && file_exists($helpers_file)) {
            require_once $helpers_file;
        }
    }

    public function include_frontend()
    {
        require_once dirname(__FILE__) . '/wordland-template-loader.php';
    }

    public function initFeatures()
    {
        new PostTypes();
        new Locations();

        if ($this->is_request('frontend')) {
            $templateLoader = new TemplateLoader();

            // Load template via init hook
            add_action('init', array($templateLoader, 'load'));
        }
        if (is_admin()) {
            new Admin();
        }

        // Setup WordLand data
        DataLoader::getInstance();
        AjaxRequestManager::getInstance();
        Scripts::getInstance();
        Compatibles::getInstance();
    }

    public function plugin_path()
    {
        return constant('WORDLAND_ABSPATH');
    }

    public function template_path()
    {
        return apply_filters('wordland_template_path', 'wordland/');
    }
}
