<?php
namespace WordLand\Manager;

use WordLand\Constracts\Module;
use WordLand\Modules\FavoriteProperty;
use WordLand\Modules\Ajax\QueryLocation;
use WordLand\Modules\Ajax\SameLocationProperties;

class ModuleManager
{
    protected static $hookCallables = array(
        'after_setup_theme' => 'bootstrap',
        'init' => 'init',
        'admin_init' => 'admin_init',
        'template_redirect' => 'load_template',
        'wp_enqueue_scripts' => 'load_scripts',
    );

    protected function get_active_modules()
    {
        $modules = array(
            FavoriteProperty::class,
            QueryLocation::class,
            SameLocationProperties::class,
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
}
