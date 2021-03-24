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
        return array(
            'label' => __('Create new property', 'wordland'),
            'url' => '#',
        );
    }

    public function render()
    {
        return Template::render(
            'agent/my-profile/feature/edit-property',
            array(),
            null,
            false
        );
    }
}
