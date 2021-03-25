<?php
namespace WordLand\Frontend\Dashboard;

use Ramphor\User\Abstracts\MyProfileAbstract;
use WordLand\Template;

class SavedProperties extends MyProfileAbstract
{
    const FEATURE_NAME = 'saved-properties';

    protected $priority = 15;

    public function getName()
    {
        return static::FEATURE_NAME;
    }

    public function getMenuItem()
    {
        return array(
            'label' => __('Favorite properties', 'wordland'),
            'url' => '#',
        );
    }

    public function render()
    {
        return Template::render(
            'agent/my-profile/features/saved-properties',
            array(),
            null,
            false
        );
    }
}
