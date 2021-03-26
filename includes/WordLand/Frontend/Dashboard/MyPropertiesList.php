<?php
namespace WordLand\Frontend\Dashboard;

use Ramphor\User\Abstracts\MyProfileAbstract;
use WordLand\Template;

class MyPropertiesList extends MyProfileAbstract
{
    const FEATURE_NAME = 'my-properties';

    protected $priority = 15;

    public function getName()
    {
        return static::FEATURE_NAME;
    }

    public function getMenuItem()
    {
        $myProfileUrl = wordland_get_option('my_properties_page');
        return array(
            'label' => __('My properties list', 'wordland'),
            'url' => $myProfileUrl ? get_permalink($myProfileUrl) : '#',
        );
    }

    public function render()
    {
        return Template::render(
            'agent/my-profile/features/my-property-listing',
            array(),
            null,
            false
        );
    }
}
