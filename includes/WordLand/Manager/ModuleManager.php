<?php
namespace WordLand\Manager;

use WordLand\Constracts\Module;
use WordLand\Modules\Ajax\QueryLocation;
use WordLand\Modules\Ajax\SameLocationProperties;
use WordLand\Modules\FavoriteProperty;
use WordLand\Modules\SearchHistory;
use WordLand\Modules\GlobalProperty;
use WordLand\Modules\UserProfile;
use WordLand\Modules\SendAgentMessage;
use WordLand\Modules\AgentReviews;
use WordLand\Modules\SaveSearch;
use WordLand\Modules\ViewsCounter;
use WordLand\Modules\AdvancedSearch;
use WordLand\Modules\Ajax\LoadMoreProperties;

class ModuleManager
{
    protected static $modules_instances = array();

    protected static $hookCallables = array(
        'after_setup_theme' => 'bootstrap',
        'init' => 'init',
        'wp' => 'inited',
        'admin_init' => 'admin_init',
        'template_redirect' => 'load_template',
        'wp_enqueue_scripts' => 'load_scripts',
        'wp_footer' => 'wp_footer'
    );

    protected function get_active_modules()
    {
        $modules = array(
            FavoriteProperty::class,
            QueryLocation::class,
            SameLocationProperties::class,
            SearchHistory::class,
            GlobalProperty::class,
            UserProfile::class,
            SendAgentMessage::class,
            AgentReviews::class,
            SaveSearch::class,
            ViewsCounter::class,
            AdvancedSearch::class,
            ViewsCounter::class,
            LoadMoreProperties::class,
        );
        return apply_filters('wordland_active_modules', $modules);
    }

    public function load_modules()
    {
        $active_modules = $this->get_active_modules();
        foreach ($active_modules as $active_module) {
            if (!class_exists($active_module)) {
                continue;
            }
            $active_module = new $active_module();
            if (!is_a($active_module, Module::class)) {
                continue;
            }

            static::$modules_instances[$active_module->get_name()] = $active_module;
            do_action_ref_array(
                'wordland_init_module_' . $active_module->get_name(),
                array(
                    &$active_module
                )
            );

            foreach (static::$hookCallables as $hook => $callable) {
                $method = array($active_module, $callable);
                if (!is_callable($method)) {
                    continue;
                }
                add_action($hook, $method);
            }
        }
    }

    public static function get_module($name)
    {
        if (isset(static::$modules_instances[$name])) {
            return static::$modules_instances[$name];
        }
        return false;
    }
}
