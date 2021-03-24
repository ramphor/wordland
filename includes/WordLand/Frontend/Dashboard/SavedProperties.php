<?php
namespace WordLand\Frontend\Dashboard;

use Ramphor\User\Abstracts\MyProfileAbstract;

class SavedProperties extends MyProfileAbstract
{
    const FEATURE_NAME = 'saved_properties';

    protected $priority = 15;

    public function getName()
    {
        return static::FEATURE_NAME;
    }

    public function getMenuItem()
    {
        return array(
            'label' => __('Favorite Properties', 'wordland'),
            'url' => '#',
        );
    }

    public function render()
    {
    }
}
