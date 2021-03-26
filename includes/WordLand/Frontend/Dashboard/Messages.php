<?php
namespace WordLand\Frontend\Dashboard;

use Ramphor\User\Abstracts\MyProfileAbstract;
use WordLand\Template;

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
        $messagePage = wordland_get_option('messages_page');
        return array(
            'label' => __('Inbox', 'wordland'),
            'url' => $messagePage ? get_permalink($messagePage) : '#',
        );
    }

    public function render()
    {
        return Template::render(
            'agent/my-profile/features/messages',
            array(),
            null,
            false
        );
    }
}
