<?php
namespace WordLand\Manager;

use WordLand;
use WordLand\Locations;
use WordLand\Abstracts\ManagerAbstract;
use WordLand\Integrations\MaxMind\GeoLocation;

class CronManager extends ManagerAbstract
{
    const CRON_UPDATE_MAXMIND_DATABASE = 'cron_update_maxmind_database';

    protected static $instance;

    protected $location;

    protected function __construct()
    {
        add_action('init', array($this, 'setup_cron_update_maxmind_database'));
    }

    public function setup_cron_update_maxmind_database()
    {
        $this->location = WordLand::instance()->location;
        if (!is_a($this->location, Locations::class)) {
            return;
        }

        if ($this->location->get_source_name() === GeoLocation::SOURCE_NAME) {
            add_action(static::CRON_UPDATE_MAXMIND_DATABASE, array($this, 'execute_update_maxmind_database'));
            if (isset($_SERVER['argv']) && in_array('--download-maxmind-database', $_SERVER['argv'])) {
                do_action(static::CRON_UPDATE_MAXMIND_DATABASE);
            } else {
                if (! wp_next_scheduled(static::CRON_UPDATE_MAXMIND_DATABASE)) {
                    wp_schedule_event(time(), 'weekly', static::CRON_UPDATE_MAXMIND_DATABASE);
                }
            }
        }
    }


    public function execute_update_maxmind_database()
    {
        $maxmind_location = $this->location->get_maxmind_location();
        $database_service = $maxmind_location->get_database_service();
        $db = $database_service->download_database(wordland_get_maxmind_license_key());
        if ($db) {
            $db_path = $database_service->get_database_path();
            $db_dir_path = dirname($db_path);
            if (!file_exists($db_dir_path)) {
                mkdir($db_dir_path, 0755, true);
            }
            if (file_exists($db)) {
                if (file_exists($db_path)) {
                    @unlink($db_path);
                }
                @rename($db, $db_path);
            }
        }
        return false;
    }
}
