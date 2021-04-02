<?php
namespace WordLand\Query;

use WP_User_Query;
use Jankx\Specs\WP_User_Query as WP_User_Query_Specs;

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

    public function searchPhoneNumbers($phoneNumbers)
    {
        $phoneFilter = function ($pre, $query) use ($phoneNumbers) {
            global $wpdb;
            $query->query_where .= $wpdb->prepare(
                " AND {$wpdb->prefix}wordland_agents.phone_number IN (%s)",
                implode(',', $phoneNumbers)
            );

            return $pre;
        };

        add_filter('users_pre_query', $phoneFilter, 10, 2);
        $this->createCustomFilterLog($phoneFilter, 10);
    }

    public function searchByUserId($userId)
    {
        $userIdFilter = function ($pre, $query) use ($userId) {
            global $wpdb;
            $query->query_where .= $wpdb->prepare(
                " AND {$wpdb->users}.ID = %d",
                intval($userId)
            );

            return $pre;
        };

        add_filter('users_pre_query', $userIdFilter, 10, 2);
        $this->createCustomFilterLog($userIdFilter, 10);
    }

    public function getSameLocationAgents($location_args = array())
    {
        global $wpdb;

        $filterLocationSql = '';
        $location_args = wp_parse_args($location_args, array(
            'country_id'   => 0,
            'area_level_1' => 0,
            'area_level_2' => 0,
            'area_level_3' => 0,
            'area_level_4' => 0,
        ));
        foreach ($location_args as $location_type => $location_id) {
            if ($location_id <= 0) {
                continue;
            }
            $filterLocationSql .= $wpdb->prepare(" AND {$wpdb->prefix}wordland_agents.{$location_type}=%d", $location_id);
        }
        if (empty($filterLocationSql)) {
            return;
        }

        $sameLocationFilter = function ($pre, $query) use ($filterLocationSql) {
            $query->query_where .= $filterLocationSql;
            return $pre;
        };
        add_filter('users_pre_query', $sameLocationFilter, 10, 2);
        $this->createCustomFilterLog($sameLocationFilter, 10);
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
            if (is_array($this->raw_args['phone'])) {
                $this->searchPhoneNumbers($this->raw_args['phone']);
            } else {
                $this->searchPhoneNumber($this->raw_args['phone']);
            }
        }
        if (!empty($this->raw_args['user_id'])) {
            $this->searchByUserId($this->raw_args['user_id']);
        }
        $parameters = WP_User_Query_Specs::getAllParameters(true);
        foreach ($this->raw_args as $key => $value) {
            if (in_array($key, $parameters)) {
                $this->args[$key] = $value;
            }
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
        do_action_ref_array('wordland_query_before_agent_query', array(
            &$this,
            $this->raw_args
        ));

        $wp_users_args = array_merge(
            $this->args,
            array(
                'search' => $this->search,
                'search_columns' => $this->searchColumns,
            )
        );
        $user_query = new WP_User_Query($wp_users_args);

        $this->removeCustomFilters();

        do_action_ref_array('wordland_agent_get_wordpress_user_query', array(
            &$user_query,
            $this
        ));

        return $user_query;
    }
}
