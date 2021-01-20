<?php
namespace WordLand\Modules\Ajax;

use WordLand\Abstracts\ModuleAbstract;
use WordLand\Query\LocationQuery;

/**
 * QueryLocation class
 *
 * This class use to query all locations in WordLand and suggest to end user
 */
class QueryLocation extends ModuleAbstract
{
    const MODULE_NAME = 'query_location';

    protected static $request;

    public function get_name()
    {
        return static::MODULE_NAME;
    }

    public function init()
    {
        add_action('wp_ajax_wordland_search_location', array($this, 'queryLocations'));
        add_action('wp_ajax_nopriv_wordland_search_location', array($this, 'queryLocations'));
    }

    public function queryLocations()
    {
        $request = json_decode(file_get_contents('php://input'), true); // Read from ajax request
        if (is_array($request)) {
            $request = array_merge($request, $_REQUEST);
        } else {
            $request = $_REQUEST;
        }
        static::$request = $request;

        if (empty($request['keyword'])) {
            return wp_send_json_success([]);
        }

        $query = new LocationQuery();
        $results = $query->query_location_by_keyword($request['keyword']);

        return false !== $results ? wp_send_json_success($results) : wp_send_json_error(
            sprintf(__('The error is ocurr when query location', 'wordland'))
        );
    }
}
