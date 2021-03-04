<?php
namespace WordLand\Query;

use WP_User_Query;

class AgentQuery
{
    protected $search = '';
    protected $searchColumns = array();

    protected $parsed_args = false;
    protected $raw_args = array();
    protected $args = array();

    protected $customFilters = array();

    public function __construct($args = array())
    {
        do_action_ref_array(
            'wordland_agent_query_init',
            array(
                $args,
                &$this,
            )
        );
        $this->raw_args = $args;
    }

    public function createCustomFilterLog($callable, $priority = 10)
    {
        if (!isset($this->customFilters[$priority])) {
            $this->customFilters[$priority] = array();
        }
        array_push($this->customFilters[$priority], $callable);
    }

    public function searchExactlyName()
    {
        $this->searchColumns = array('display_name');
    }

    public function searchPhoneNumber($phoneNumber)
    {
        $phoneFilter = function ($pre, $query) use ($phoneNumber) {
            global $wpdb;
            $query->query_where .= $wpdb->prepare(
                " AND {$wpdb->prefix}wordland_agents.phone_number=%s",
                $phoneNumber
            );

            return $pre;
        };

        add_filter('users_pre_query', $phoneFilter, 10, 2);
        $this->createCustomFilterLog($phoneFilter, 10);
    }

    public function select($fields = "*")
    {
        $selectFilter = function ($pre, $query) use ($fields) {
            $query->query_fields = sprintf('SQL_CALC_FOUND_ROWS %s', $fields);
            return $pre;
        };

        add_filter('users_pre_query', $selectFilter, 10, 2);
        $this->createCustomFilterLog($selectFilter, 10);
    }

    public function parseArgs()
    {
        $this->parsed_args = true;
        if (!empty($this->raw_args['phone'])) {
            $this->searchPhoneNumber($this->raw_args['phone']);
        }
    }

    protected function removeCustomFilters()
    {
        if (empty($this->customFilters)) {
            return;
        }
        foreach ($this->customFilters as $priority => $callables) {
            foreach ($callables as $index => $callable) {
                remove_filter('users_pre_query', $callable, $priority);
                unset($this->customFilters[$priority][$index]);
            }
            unset($this->customFilters[$priority]);
        }
    }

    public function getWordPressQuery()
    {
        if (!$this->parsed_args) {
            $this->parseArgs();
        }

        $wp_users_args = array_merge(
            $this->args,
            array(
                'search' => $this->search,
                'search_columns' => $this->searchColumns,
            )
        );
        $user_query = new WP_User_Query($wp_users_args);

        $this->removeCustomFilters();

        do_action('wordland_agent_get_wordpress_user_query', $user_query, $this);

        return $user_query;
    }
}
