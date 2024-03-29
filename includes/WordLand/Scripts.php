<?php
namespace WordLand;

use WordLand;

class Scripts
{
    const HANDLER_NAME = 'wordland';

    protected static $instance;

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'registerScripts'), 40);
        add_action('wp_enqueue_scripts', array($this, 'registerStyles'), 40);

        add_action('init', array($this, 'init'));
    }

    public function init()
    {
        add_action('ramphor_collection_global_variables', array($this, 'ramphor_collection_user_not_logged_in'));
        add_filter('ramphor_collection_script_is_dep', '__return_true');
    }

    public function ramphor_collection_user_not_logged_in($global_variables)
    {
        $global_variables['user_not_loggedin_callback'] = 'wordland_show_login_modal';

        return $global_variables;
    }

    protected function asset_url($path = '')
    {
        return sprintf(
            '%sassets/%s',
            plugin_dir_url(WORDLAND_PLUGIN_FILE),
            $path
        );
    }

    protected function localizeScriptL10n()
    {
        return array(
            'languages' => array(
                'listing_by_cat_error' => __('Load more properties listing occurs error', 'wordland')
            ),
            'ajax_url' => admin_url('admin-ajax.php'),
        );
    }

    public function registerScripts()
    {
        $deps = array(
            'ramphor-collection',
        );
        if (!get_theme_support('render_js_template')) {
            wp_register_script('blueimp-tmpl', $this->asset_url('vendor/JavaScript-Templates/tmpl.js'), array(), '3.19.0', false);
            array_push($deps, 'blueimp-tmpl');
        }
        wp_register_script(static::HANDLER_NAME, $this->asset_url('js/wordland.js'), $deps, WordLand::$version);
        wp_localize_script(static::HANDLER_NAME, 'wordland', $this->localizeScriptL10n());

        wp_enqueue_script(static::HANDLER_NAME);
    }

    public function registerStyles()
    {
        global $wp_styles;

        $deps = array( static::HANDLER_NAME . '-icons' );
        if (isset($wp_styles->registered['hover'])) {
            array_push($deps, 'hover');
        }
        wp_register_style(static::HANDLER_NAME . '-icons', $this->asset_url('css/icons.css'), array(), WordLand::ICON_VERSION);
        wp_register_style(static::HANDLER_NAME, $this->asset_url('css/wordland.css'), $deps, WordLand::$version);
        wp_enqueue_style(static::HANDLER_NAME);
    }
}
