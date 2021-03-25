<?php
namespace WordLand\Frontend\Dashboard;

use Ramphor\User\Abstracts\MyProfileAbstract;
use WordLand\Template;

class SavedSearches extends MyProfileAbstract
{
    const FEATURE_NAME = 'saved-searches';

    protected $priority = 15;

    public function getName()
    {
        return static::FEATURE_NAME;
    }

    public function getMenuItem()
    {
        return array(
            'label' => __('Saved searches', 'wordland'),
            'url' => '#',
        );
    }

    public function render()
    {
        return Template::render(
            'agent/my-profile/features/saved-searches',
            array(),
            null,
            false
        );
    }
}
