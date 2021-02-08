<?php
namespace WordLand\Modules;

use WordLand\Abstracts\ModuleAbstract;

class SearchHistory extends ModuleAbstract
{
    const MODULE_NAME = 'search_history';
    public function get_name()
    {
        return static::MODULE_NAME;
    }

    public function init()
    {
        add_action('wp_ajax_wordland_history_search', array($this, 'createNewSearchHistory'));
        add_action('wp_ajax_nopriv_wordland_history_search', array($this, 'createNewSearchHistory'));
    }

    public function createNewSearchHistory()
    {
        $request = json_decode(file_get_contents('php://input'), true); // Read from ajax request
        if (is_array($request)) {
            $request = array_merge($request, $_REQUEST);
        } else {
            $request = $_REQUEST;
        }

        global $wpdb;
        $history_arr = array(
            'keyword_text' => array_get($request, 'name'),
            'history_type' => 'taxonomy',
            'reference_object' => array_get($request, 'term_id'),
            'reference_type' => array_get($request, 'taxonomy', 'location'),
            'user_id' => get_current_user_id(),
            'ip' => wordland_get_real_ip_address(),
            'created_at' => current_time('mysql'),
        );

        return $wpdb->insert(
            $wpdb->prefix . 'wordland_search_histories',
            $history_arr
        );
    }
}
