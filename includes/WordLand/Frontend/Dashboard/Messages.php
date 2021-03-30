<?php
namespace WordLand\Frontend\Dashboard;

use WP_Query;
use Ramphor\User\Abstracts\MyProfileAbstract;
use WordLand\Template;
use WordLand\PostTypes;

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

    public static function joinMesageReferences($join)
    {
        return $join;
    }

    public static function filterAgentMessages($where)
    {
        return $where;
    }

    public static function get_messages($user_id, $page = 1)
    {
        add_filter('posts_join', array(__CLASS__, 'joinMesageReferences'));
        add_filter('posts_where', array(__CLASS__, 'filterAgentMessages'));

        $messages = new WP_Query(array(
            'post_type' => PostTypes::AGENT_MESSAGE_POST_TYPE,
            'posts_per_page' => 10,
            'paged' => $page
        ));

        remove_filter('posts_where', array(__CLASS__, 'filterAgentMessages'));
        remove_filter('posts_join', array(__CLASS__, 'joinMesageReferences'));

        return $messages;
    }

    public function render()
    {
        $currentUser = wp_get_current_user();
        $current_page = get_query_var('paged');
        if ($current_page <= 0) {
            $current_page = 1;
        }

        return Template::render(
            'agent/my-profile/features/messages',
            array(
                'messages' => static::get_messages($currentUser->ID, $current_page),
            ),
            null,
            false
        );
    }
}
