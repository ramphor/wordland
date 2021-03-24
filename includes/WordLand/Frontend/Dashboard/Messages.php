<?php
namespace WordLand\Frontend\Dashboard;

use Ramphor\User\Abstracts\MyProfileAbstract;

class Messages extends MyProfileAbstract
{
    const FEATURE_NAME = 'messages';

    protected $priority = 40;

    public function getName()
    {
        return static::FEATURE_NAME;
    }

    public function getMenuItem()
    {
        return array(
            'label' => __('Inbox', 'wordland'),
            'url' => '#',
        );
    }

    public function render()
    {
    }
}
