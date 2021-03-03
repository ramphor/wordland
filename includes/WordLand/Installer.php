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
            'wordland_properties' => '`ID` BIGINT NOT NULL AUTO_INCREMENT,
                `property_id` BIGINT NOT NULL,
                `location` POINT NULL,
                `address` TEXT NULL,
                `price` BIGINT NOT NULL DEFAULT 0,
                `unit_price` BIGINT NOT NULL DEFAULT 0,
                `size` BIGINT NOT NULL DEFAULT 0,
                `bedrooms` BIGINT NOT NULL DEFAULT 0,
                `bathrooms` BIGINT NOT NULL DEFAULT 0,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `created_at` TIMESTAMP NOT NULL,
                PRIMARY KEY (`ID`)',
            'wordland_agents' => '`ID` BIGINT NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT NOT NULL,
                `phone_number` VARCHAR(100) NULL,
                `address` VARCHAR(255) NOT NULL,
                `area_level_1` BIGINT NOT NULL,
                `area_level_2` BIGINT NOT NULL,
                `area_level_3` BIGINT NULL,
                `area_level_4` BIGINT NULL,
                PRIMARY KEY (`ID`)',
            'wordland_locations' => '`ID` BIGINT NOT NULL AUTO_INCREMENT,
                `term_id` BIGINT NOT NULL,
                `location_name` VARCHAR(255) DEFAULT \'\' COMMENT \'Location name with prefix is parent location\',
                `ascii_name` VARCHAR(255) DEFAULT \'\' COMMENT \'The clean location name use for multi purpose\',
                `location` GEOMETRY NULL,
                `center_point` POINT NULL,
                `geo_eng_name` VARCHAR(255) NULL COMMENT \'Use for Brower Location API\',
                `clean_name` VARCHAR(255) NULL COMMENT \'Use to improve query from location name\'
                `zip_code` VARCHAR(10) NULL,
                `created_at` TIMESTAMP NOT NULL,
                PRIMARY KEY (`ID`)',
            'wordland_search_histories' => '`ID` BIGINT NOT NULL AUTO_INCREMENT,
                `keyword_text` VARCHAR(255),
                `history_type` VARCHAR(255) DEFAULT \'general\',
                `reference_object` BIGINT NOT NULL DEFAULT 0,
                `reference_type` VARCHAR(255) NULL,
                `user_id`  BIGINT NOT NULL DEFAULT 0,
                `ip` VARCHAR(255) DEFAULT \'\' COMMENT \'IP of the user searching property\',
                `created_at` TIMESTAMP NOT NULL,
                PRIMARY KEY (`ID`)'
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

        // Disable ONLY_FULL_GROUP_BY to group property by locations
        $wpdb->query("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    }
}
