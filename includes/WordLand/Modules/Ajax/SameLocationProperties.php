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
class SameLocationProperties extends ModuleAbstract
{
    const MODULE_NAME = 'same_location_properties';

    public function get_name()
    {
        return static::MODULE_NAME;
    }

    public function init()
    {
        add_action('wp_ajax_wordland_get_same_location_properties', array($this, 'getSameLocationProperties'));
        add_action('wp_ajax_nopriv_wordland_get_same_location_properties', array($this, 'getSameLocationProperties'));

        add_filter('wordland_reactjs_global_data', array($this, 'registerNewEndpoint'));
    }

    public function registerNewEndpoint($endpoints)
    {
        $endpoints['endpoints']['get_same_location_properties'] = admin_url('admin-ajax.php?action=wordland_get_same_location_properties');
        return $endpoints;
    }

    public function getSameLocationProperties()
    {
        $request = json_decode(file_get_contents('php://input'), true); // Read from ajax request
        if (is_array($request)) {
            $request = array_merge($request, $_REQUEST);
        } else {
            $request = $_REQUEST;
        }
        if (!array_get($request, 'property_id', false)) {
            return wp_send_json_error(__('Property ID is invalid', 'wordland'));
        }
        $listing_types = array_get($request, 'listing_type', false);
        $properties = wordland_get_same_location_properties_by_property_id(
            array_get($request, 'property_id'),
            array(
                'listing_types' => $listing_types
            )
        );
        return wp_send_json_success($properties);
    }
}
