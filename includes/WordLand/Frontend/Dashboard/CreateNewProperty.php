<?php
namespace WordLand\Frontend\Dashboard;

use Ramphor\User\Abstracts\MyProfileAbstract;

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
            'label' => __('Create New Property', 'wordland'),
            'url' => '#',
        );
    }

    public function render()
    {
    }
}
