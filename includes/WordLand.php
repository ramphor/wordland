<?php
use WordLand\PostTypes;
use WordLand\Locations;
use WordLand\TemplateLoader;
use WordLand\Admin\Admin;
use WordLand\DataLoader;
use WordLand\AjaxRequestManager;
use WordLand\Scripts;
use WordLand\Compatibles;
use WordLand\Installer;
use WordLand\Cache;
use WordLand\ModuleManager;
use WordLand\Manager\CronManager;
use Jankx\Template\Template;
use Ramphor\User\Profile as UserProfile;
use Ramphor\FriendlyNumbers\Parser;
use Ramphor\FriendlyNumbers\Locale;
use Ramphor\Collection\CollectionManager;
use Ramphor\Collection\DB;
use Ramphor\PostViews\Counter as PostViewCounter;
use Ramphor\PostViews\Handlers\UserHandler;
use Ramphor\PostViews\Handlers\CookieHandler;

class WordLand
{
    protected static $instance;
    public static $version;

    public $location;

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
        $this->location = new Locations();

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
        CollectionManager::getInstance();
        CronManager::getInstance();

        $installer = Installer::getInstance();
        register_activation_hook(
            WORDLAND_PLUGIN_FILE,
            array($installer, 'install')
        );
        register_activation_hook(
            WORDLAND_PLUGIN_FILE,
            array(DB::class, 'setup')
        );

        if (class_exists(UserProfile::class)) {
            $userTemplatesDir = sprintf('%s/templates/user', WORDLAND_ABSPATH);
            $profileTemplateLoader = Template::getLoader(
                $userTemplatesDir,
                apply_filters('wordland_user_profile_template_directory', 'wordland/user'),
                'wordpress'
            );
            $userProfile = UserProfile::getInstance();
            $userProfile->registerTemplate(
                'wordland',
                $profileTemplateLoader
            );
        }
        add_action('init', array($this, 'init'));
    }

    public function init() {
        $userHandler = new UserHandler(true, true);
        $userHandler->setUserIP(wordland_get_real_ip_address());
        $userHandler->setExpireTime(1 * 24 * 60 * 60); // 1 day

        $counter = new PostViewCounter(PostTypes::get());
        $counter->addHandle($userHandler);
        $counter->count();
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
        $moduleManager->load_modules();
    }
}
