<?php
namespace WordLand\Modules;

use WordLand\Abstracts\ModuleAbstract;
use WordLand\Frontend\Dashboard\MyPropertiesList;

class UserProfile extends ModuleAbstract
{
    const MODULE_NAME = 'user_profile';

    public function get_name()
    {
        return static::MODULE_NAME;
    }

    public function init()
    {
        add_filter('wordland_my_profile_features', array($this, 'registerNewFeatures'));
    }

    public function registerNewFeatures($features)
    {
        $features = array_merge($features, array(
            MyPropertiesList::class,
        ));

        return $features;
    }
}
