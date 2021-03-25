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
        return array(
            'label' => __('My properties list', 'wordland'),
            'url' => '#',
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
