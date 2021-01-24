<?php
namespace WordLand\Integrations\MaxMind;

use WP_Error;
use GeoIp2\Database\Reader;
use WordLand\Integrations\MaxMind\GeoLocation\DatabaseService;

class GeoLocation
{
    const SOURCE_NAME = 'maxmind_geolocated';

    protected $database_service;

    public function __construct()
    {
        $this->database_service = new DatabaseService($this->get_database_prefix());
    }


    /**
     * Fetches the prefix for the MaxMind database file.
     *
     * @return string
     */
    private function get_database_prefix()
    {
        $prefix = get_option('wordland_database_prefix');
        if (empty($prefix)) {
            $prefix = wp_generate_password(32, false);
            update_option('wordland_database_prefix', $prefix);
        }

        return $prefix;
    }

    public function get_current_location()
    {
        $db_path = $this->database_service->get_database_path();
        if (!file_exists($db_path)) {
            return array(
                'has_coordinates' => false,
            );
        }

        $reader = new Reader($db_path);
        $ip = wordland_get_real_ip_address();
        // $ip = '58.186.51.52'; // IPv4
        // $ip = '2402:800:61ae:fb0f:dd43:cf99:4a7c:5abf'; // IPv6

        // Check IP is localhost
        $parts = array_map('intval', explode('.', $ip));
        if (127 === $parts[0] || 10 === $parts[0] || 0 === $parts[0]
            || ( 172 === $parts[0] && 16 <= $parts[1] && 31 >= $parts[1] )
            || ( 192 === $parts[0] && 168 === $parts[1] )
        ) {
            return array();
        }
        $record = $reader->city($ip);
        if (is_null($record->city->name)) {
            return array(
                'has_coordinates' => false,
            );
        }

        return array(
            'city' => $record->city->name,
            'has_coordinates' => true,
            'coordinates' => array(
                'lat' => $record->location->latitude,
                'lng' => $record->location->longitude
            )
        );
    }

    public function get_database_service()
    {
        return $this->database_service;
    }
}
