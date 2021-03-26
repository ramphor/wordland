<?php
namespace WordLand\Frontend\Dashboard;

use Ramphor\User\Abstracts\MyProfileAbstract;
use WordLand\Template;

class CreateNewProperty extends MyProfileAbstract
{
    const FEATURE_NAME = 'create-property';

    public function getName()
    {
        return static::FEATURE_NAME;
    }

    public function getMenuItem()
    {
        $createPropertyPage = wordland_get_option('create_property_page');
        return array(
            'label' => __('Create new property', 'wordland'),
            'url' => $createPropertyPage ? get_permalink($createPropertyPage) : '#',
        );
    }

    public function render()
    {
        return Template::render(
            'agent/my-profile/features/edit-property',
            array(),
            null,
            false
        );
    }
}
