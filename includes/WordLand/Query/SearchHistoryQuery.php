<?php
namespace WordLand\Query;

class SearchHistoryQuery
{
    public function get_search_histories($user_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT DISTINCT  keyword_text, history_type, reference_object, reference_type
            FROM {$wpdb->prefix}wordland_search_histories
            WHERE user_id=%d
                OR ip=%s
            ORDER BY
                created_at DESC,
                user_id DESC
            LIMIT 5",
            $user_id,
            wordland_get_real_ip_address()
        );
        return $wpdb->get_results($sql);
    }
}
