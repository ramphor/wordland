<?php
namespace WordLand;

use Ramphor\User\Profile as UserProfile;
use Ramphor\PostViews\Setup;

class Installer
{
    protected static $instance;

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function install()
    {
        $this->setupDatabase();

        // Create user profile data table
        $userProfile = UserProfile::getInstance();
        $userProfile->db->create_table();

        $postview = new Setup();
        $postview->createTables();
    }

    public function setupDatabase()
    {
        global $wpdb;
        $engine = 'MyISAM';

        $tables = array(
            'wordland_properties' => '`ID` BIGINT NOT NULL AUTO_INCREMENT ,
                `property_id` BIGINT NOT NULL ,
                `location` POINT NOT NULL ,
                `price` BIGINT NOT NULL DEFAULT 0,
                `unit_price` BIGINT NOT NULL DEFAULT 0,
                `size` BIGINT NOT NULL DEFAULT 0,
                `bedrooms` BIGINT NOT NULL DEFAULT 0,
                `bathrooms` BIGINT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NOT NULL ,
                PRIMARY KEY (`ID`)',
            'wordland_locations' => '`ID` BIGINT NOT NULL AUTO_INCREMENT ,
                `term_id` BIGINT NOT NULL ,
                `location` MULTIPOLYGON NOT NULL ,
                `created_at` TIMESTAMP NOT NULL ,
            PRIMARY KEY (`ID`)',
        );

        foreach ($tables as $table_name => $sql_syntax) {
            $sql = sprintf(
                'CREATE TABLE IF NOT EXISTS %s%s (%s) ENGINE = %s CHARSET=%s COLLATE=%s',
                $wpdb->prefix,
                $table_name,
                $sql_syntax,
                $engine,
                $wpdb->charset,
                $wpdb->collate
            );
            $wpdb->query($sql);
        }
    }
}
