<?php
namespace WordLand\Modules\Ajax;

use geoPHP;
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

        add_action('wp_ajax_wordland_get_location', array($this, 'getLocation'));
        add_action('wp_ajax_nopriv_wordland_get_location', array($this, 'getLocation'));

        add_action('wp_ajax_wordland_find_geo_location', array($this, 'getLocationFromGeoLocation'));
        add_action('wp_ajax_nopriv_wordland_find_geo_location', array($this, 'getLocationFromGeoLocation'));

        add_filter('wordland_reactjs_global_data', array($this, 'registerNewEndpoints'));
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


    public function getLocation()
    {
        $request = json_decode(file_get_contents('php://input'), true); // Read from ajax request
        if (is_array($request)) {
            $request = array_merge($request, $_REQUEST);
        } else {
            $request = $_REQUEST;
        }
        static::$request = $request;

        $term_id = array_get(static::$request, 'term_id');

        $query = new LocationQuery();
        $location = $query->query_location($term_id);
        if ($location !== false) {
            $result = array(
                'name' => $location->location_name,
                'term_id' => $term_id
            );

            if (!empty($location->location)) {
                $geom = geoPHP::load($location->location, 'ewkb');

                if ($geom) {
                    $result['geojson_border'] = array(
                        'type' => 'Feature',
                        'geometry' => json_decode($geom->out('json'))
                    );
                    $centroid = $geom->getCentroid();
                    $result['center_location'] = array(
                        'lat' => $centroid->getY(),
                        'lng' => $centroid->getX(),
                    );
                }
            }

            return wp_send_json_success($result);
        }

        return wp_send_json_error(
            sprintf(__('The error is ocurr when query location', 'wordland'))
        );
    }

    public function getLocationFromGeoLocation()
    {
        $request = json_decode(file_get_contents('php://input'), true); // Read from ajax request
        if (is_array($request)) {
            $request = array_merge($request, $_REQUEST);
        } else {
            $request = $_REQUEST;
        }
        static::$request = $request;

        $lat = array_get(static::$request, 'lat');
        $lng = array_get(static::$request, 'lng');

        $location = wordland_get_term_from_geo_location(array(
            'lat' => $lat,
            'lng' => $lng,
        ));

        if ($location !== false) {
            $result = array(
                'name' => $location->location_name,
                'term_id' => $location->term_id
            );

            if (!empty($location->kml)) {
                $geom = geoPHP::load($location->kml, 'ewkb');

                if ($geom) {
                    $result['geojson_border'] = array(
                        'type' => 'Feature',
                        'geometry' => json_decode($geom->out('json'))
                    );
                    $centroid = $geom->getCentroid();
                    $result['center_location'] = array(
                        'lat' => $centroid->getY(),
                        'lng' => $centroid->getX(),
                    );
                }
            }

            return wp_send_json_success($result);
        }

        return wp_send_json_error(
            sprintf(__('The error is ocurr when query location', 'wordland'))
        );
    }

    public function registerNewEndpoints($globalData)
    {
        $globalData['endpoints']['get_geolocation_url'] = admin_url('admin-ajax.php?action=wordland_get_location');
        return $globalData;
    }
}
