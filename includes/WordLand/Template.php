<?php
namespace WordLand;

use Jankx\Template\Template as TemplateLib;

class Template
{
    protected static $loader;
    protected static $userProfileLoader;

    public static function getEngine()
    {
        if (is_null(static::$loader)) {
            $templateDir = sprintf('%s/templates', dirname(WORDLAND_PLUGIN_FILE));
            static::$loader = TemplateLib::createEngine(
                'wordland',
                apply_filters('wordland_template_directory_name', 'wordland'),
                $templateDir,
                apply_filters('wordland_template_engine', 'wordpress')
            );
        }

        return static::$loader;
    }

    public static function search()
    {
        $args = func_get_args();
        return call_user_func_array(
            array(static::getEngine(), 'searchTemplate'),
            $args
        );
    }

    public static function render()
    {
        $args = func_get_args();
        return call_user_func_array(
            array(static::getEngine(), 'render'),
            $args
        );
    }
}
