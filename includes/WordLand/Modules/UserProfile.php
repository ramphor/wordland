<?php
namespace WordLand\Modules;

use WordLand;
use WordLand\Abstracts\ModuleAbstract;
use WordLand\Frontend\UserDashboardHeader;
use WordLand\Frontend\Dashboard\CreateNewProperty;
use WordLand\Frontend\Dashboard\Messages;
use WordLand\Frontend\Dashboard\MyPropertiesList;
use WordLand\Frontend\Dashboard\SavedProperties;
use WordLand\Frontend\Dashboard\SavedSearches;

use WordLand\Frontend\Dashboard\Section\UserDetailsSection;

class UserProfile extends ModuleAbstract
{
    const MODULE_NAME = 'user_profile';

    protected $userDashboardHeader;

    public function get_name()
    {
        return static::MODULE_NAME;
    }

    public function bootstrap()
    {
        $this->userDashboardHeader = new UserDashboardHeader();
        add_action('init', array($this->userDashboardHeader, 'init'));
    }

    public function init()
    {
        add_filter('wordland_my_profile_features', array($this, 'registerNewFeatures'));
        add_filter('wordland_my_profile_dashboard_sections', array($this, 'addNewDashboardSections'));
    }

    public function registerNewFeatures($features)
    {
        $features = array_merge($features, array(
            MyPropertiesList::class,
            CreateNewProperty::class,
            SavedProperties::class,
            SavedSearches::class,
            Messages::class,
        ));

        return $features;
    }

    public function addNewDashboardSections($sections)
    {
        $userDetailSection = new UserDetailsSection("wordland-section_user-details", WordLand::TEMPLATE_LOADER_ID);
        $userDetailSection->setHeading(__('User Details', 'wordland'));
        $userDetailSection->setDescription(__('Add some information about yourself.', 'wordland'));

        array_push($sections, $userDetailSection);

        return $sections;
    }
}
