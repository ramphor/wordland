<?php
namespace WordLand\Frontend\Dashboard;

use Ramphor\User\Abstracts\MyProfileAbstract;

class SavedSearches extends MyProfileAbstract
{
    const FEATURE_NAME = 'saved_searches';

    protected $priority = 15;

    public function getName()
    {
        return static::FEATURE_NAME;
    }

    public function getMenuItem()
    {
        return array(
            'label' => __('Saved Searches', 'wordland'),
            'url' => '#',
        );
    }

    public function render()
    {
    }
}
