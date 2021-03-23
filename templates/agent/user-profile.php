<?php
    $user_data = get_queried_object();
    if (!$user_data) {
        $user_data = get_query_var('user_login');
    }
    $agent_data  = wordland_parse_agent_data($user_data);
    $user_type   = array_get($agent_data, 'user_type', 'agent');
?>

<?php
get_header();

do_action("wordland_before_agent_{$user_type}_content", $agent_data);
do_action('wordland_before_agent_content', $agent_data);

wordland_template(
    "agent/content/{$user_type}",
    array_merge($agent_data, array(
        'agent_data' => &$agent_data,
    )),
    "wordland_agent_{$user_type}_profile"
);

do_action('wordland_after_agent_content', $agent_data);
do_action("wordland_after_agent_{$user_type}_content", $agent_data);

get_footer();
