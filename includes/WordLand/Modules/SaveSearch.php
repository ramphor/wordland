<?php
namespace WordLand\Modules;

use WordLand\Abstracts\ModuleAbstract;

class SaveSearch extends ModuleAbstract
{
    const MODULE_NAME = 'save_search';

    public function get_name()
    {
        return static::MODULE_NAME;
    }

    public function init()
    {
        add_action('wp_ajax_wordland_save_search', array($this, 'saveSearch'));
        if (apply_filters('wordland_enable_guest_save_search', false)) {
            add_action('wp_ajax_nopriv_wordland_save_search', array($this, 'saveSearch'));
        } else {
            add_action('wp_ajax_nopriv_wordland_save_search', array($this, 'noPermission'));
        }
    }

    public function saveSearch()
    {
        $request = json_decode(file_get_contents('php://input'), true); // Read from ajax request
        if (is_array($request)) {
            $request = array_merge($request, $_REQUEST);
        } else {
            $request = $_REQUEST;
        }

        global $wpdb;

        $status = $wpdb->insert($wpdb->prefix . 'wordland_saved_searches', array(
            'search_name' => __('My saved search', 'wordland'),
            'user_id' => get_current_user_id(),
            'guest_ip' => wordland_get_real_ip_address(),
            'search_content' => json_encode($request, JSON_UNESCAPED_UNICODE),
            'created_at' => current_time('mysql'),
        ));

        if (!is_wp_error($status) && $status) {
            wp_send_json_success(__('Save search is successful', 'wordland'));
        }
        wp_send_json_error(__('Save search is error', 'wordland'));
    }

    public function noPermission()
    {
        wp_send_json_error(__('Sorry, you don\'t have permission to save your search', 'wordland'));
    }
}
