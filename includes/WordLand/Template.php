<?php
namespace WordLand;

use Jankx\PostLayout\PostLayoutManager;
use Jankx\Template\Template as TemplateLib;
use Jankx\TemplateEngine\Engines\WordPress;

class Template
{
    protected static $templateEngine;
    protected static $userProfiletemplateEngine;
    protected static $postLayoutManager;

    public static function getEngine()
    {
        if (is_null(static::$templateEngine)) {
            $templateDir = sprintf('%s/templates', dirname(WORDLAND_PLUGIN_FILE));
            static::$templateEngine = TemplateLib::createEngine(
                'wordland',
                apply_filters('wordland_template_directory_name', 'wordland'),
                $templateDir,
                apply_filters('wordland_template_engine', WordPress::ENGINE_NAME)
            );
        }

        return static::$templateEngine;
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

    public static function createPostLayout($name, $wp_query) {
        if (is_null(static::$postLayoutManager)) {
            static::$postLayoutManager = PostLayoutManager::createInstance(static::getEngine());
        }
        return static::$postLayoutManager->createLayout($name, $wp_query);
    }
}
