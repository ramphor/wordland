<?php
namespace WordLand\Integrations\MaxMind\GeoLocation;

use PharData;
use WP_Error;
use Exception;

class DatabaseService
{
    const MAXMIND_APP_URL = 'https://download.maxmind.com/app/geoip_download';
    const DATABASE_NAME = 'GeoLite2-City';
    const DATABASE_EXTENSION = '.mmdb';
    const DATABASE_SUFFIX = 'tar.gz';

    protected $database_prefix;

    public function __construct($database_prefix)
    {
        $this->database_prefix = $database_prefix;
    }

    public function download_database($license_key)
    {
        $download_uri = add_query_arg(
            array(
                'edition_id'  => static::DATABASE_NAME,
                'license_key' => urlencode(sanitize_text_field($license_key)),
                'suffix'      => static::DATABASE_SUFFIX,
            ),
            static::MAXMIND_APP_URL
        );

        // Needed for the download_url call right below.
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $tmp_archive_path = download_url(esc_url_raw($download_uri));
        if (is_wp_error($tmp_archive_path)) {
            // Transform the error into something more informative.
            $error_data = $tmp_archive_path->get_error_data();
            if (isset($error_data['code'])) {
                switch ($error_data['code']) {
                    case 401:
                        return new WP_Error(
                            'woocommerce_maxmind_geolocation_database_license_key',
                            __('The MaxMind license key is invalid. If you have recently created this key, you may need to wait for it to become active.', 'woocommerce')
                        );
                }
            }

            return new WP_Error('woocommerce_maxmind_geolocation_database_download', __('Failed to download the MaxMind database.', 'woocommerce'));
        }

        // Extract the database from the archive.
        try {
            $file = new PharData($tmp_archive_path);
            $tmp_database_path = trailingslashit(dirname($tmp_archive_path)) . trailingslashit($file->current()->getFilename()) . self::DATABASE_NAME . self::DATABASE_EXTENSION;
            $file->extractTo(
                dirname($tmp_archive_path),
                trailingslashit($file->current()->getFilename()) . self::DATABASE_NAME . self::DATABASE_EXTENSION,
                true
            );
        } catch (Exception $exception) {
            return new WP_Error('woocommerce_maxmind_geolocation_database_archive', $exception->getMessage());
        } finally {
            // Remove the archive since we only care about a single file in it.
            unlink($tmp_archive_path);
        }

        return $tmp_database_path;
    }

    public function get_database_path()
    {
        $db_path = sprintf(
            '%s/uploads/wordland/%s-%s%s',
            constant('WP_CONTENT_DIR'),
            $this->database_prefix,
            static::DATABASE_NAME,
            static::DATABASE_EXTENSION
        );
        return apply_filters('wordland_integration_maxmind_database_path', $db_path, $this);
    }
}
