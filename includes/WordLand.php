<?php
use WordLand\PostTypes;
use WordLand\Locations;
use WordLand\TemplateLoader;
use WordLand\Admin\Admin;
use WordLand\AjaxRequestManager;
use WordLand\Scripts;
use WordLand\Compatibles;
use WordLand\Installer;
use WordLand\Cache;
use WordLand\Agent;
use WordLand\Manager\ModuleManager;
use WordLand\Manager\CronManager;
use WordLand\Manager\QueryManager;
use WordLand\Manager\DataManager;
use Jankx\Template\Template;
use Ramphor\User\ProfileManager;
use Ramphor\Collection\CollectionManager;
use Ramphor\Collection\DB;
use Ramphor\PostViews\Counter as PostViewCounter;
use Ramphor\PostViews\Handlers\UserHandler;
use Ramphor\PostViews\Handlers\CookieHandler;

class WordLand
{
    const ICON_VERSION              = '1.0.0';
    const TEMPLATE_LOADER_ID        = 'wordland';
    const PROPERTY_GALLERY_META_KEY = 'wordland_property_gallery_images';

    protected static $instance;
    public static $version;

    public $location;
    public $viewCounter;

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
        $this->loadModules();
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
        $this->location    = new Locations();
        $this->viewCounter = new PostViewCounter(PostTypes::get());

        if ($this->is_request('frontend')) {
            $templateLoader = new TemplateLoader();

            // Load template via init hook
            add_action('init', array($templateLoader, 'load'));
        }
        if (is_admin()) {
            new Admin();
        }

        // Setup WordLand data
        AjaxRequestManager::getInstance();
        Scripts::getInstance();
        Compatibles::getInstance();
        QueryManager::getInstance();
        CollectionManager::getInstance();
        CronManager::getInstance();
        DataManager::getInstance();

        $installer = Installer::getInstance();
        register_activation_hook(
            WORDLAND_PLUGIN_FILE,
            array($installer, 'install')
        );
        register_activation_hook(
            WORDLAND_PLUGIN_FILE,
            array(DB::class, 'setup')
        );

        if (class_exists(ProfileManager::class)) {
            $userTemplatesDir = sprintf('%s/templates/agent', WORDLAND_ABSPATH);
            $profileTemplateLoader = Template::getLoader(
                $userTemplatesDir,
                apply_filters('wordland_user_profile_template_directory', 'wordland/agent'),
                'wordpress'
            );
            $profileManager = ProfileManager::getInstance();
            $profileManager->registerTemplate(
                static::TEMPLATE_LOADER_ID,
                $profileTemplateLoader
            );
            $profileManager->registerUserProfile(Agent::DEFAULT_AGENT_TYPE);
            $profileManager->registerMyProfile('wordland_get_option');
        }
        add_action('init', array($this, 'init'));
    }

    public function init()
    {
        add_action(
            'ramphor_post_views_view_the_post',
            array($this, 'create_cache_view_post'),
            10,
            2
        );

        $userHandler = new UserHandler(true);
        $userHandler->setRemoteIP(wordland_get_real_ip_address());
        $userHandler->setUserId(get_current_user_id());
        $userHandler->setExpireTime(7 * 24 * 60 * 60); // 7 days

        $this->viewCounter->addHandle($userHandler);

        add_action('wordland_before_get_single_property', function ($property) {
            if (did_action('wp')) {
                $this->viewCounter->count($property->ID);
            } else {
                $callback = array($this->viewCounter, 'count');
                call_user_func($callback, $property->ID);
            }
        });

        if (! current_user_can('manage_options') || apply_filters('wordland_force_hide_admin_bar', true)) {
            show_admin_bar(false);
        }
    }

    public function create_cache_view_post($post_id, $post_types)
    {
        $post = get_post($post_id);
        if (is_null($post) || !in_array($post->post_type, $post_types)) {
            return;
        }
        Cache::addViewed($post_id, true);
    }

    public function plugin_path()
    {
        return constant('WORDLAND_ABSPATH');
    }

    public function template_path()
    {
        return apply_filters(
            'wordland_template_path',
            'wordland/'
        );
    }

    public function loadModules()
    {
        $moduleManager = new ModuleManager();

        // Load module via action hook `plugins_loaded`
        add_action('plugins_loaded', array($moduleManager, 'load_modules' ), 20);
    }
}
