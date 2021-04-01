<?php
namespace WordLand;

use Ramphor\User\ProfileManager;
use Ramphor\PostViews\Setup;

class Installer
{
    protected static $instance;

    protected static $myisamTables = array(
        'wordland_properties',
        'wordland_locations'
    );

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
        $userProfile = ProfileManager::getInstance();
        $userProfile->db->create_table();

        $postview = new Setup();
        $postview->createTables();

        flush_rewrite_rules(true);
    }

    public function setupDatabase()
    {
        global $wpdb;

        $tables = array(
            'wordland_properties' => '`ID` BIGINT NOT NULL AUTO_INCREMENT,
                `property_id` BIGINT NOT NULL,
                `coordinate` POINT NULL,
                `address` VARCHAR(255) NULL,
                `full_address` VARCHAR(255) NULL,
                `price` DECIMAL(20, 2) NOT NULL DEFAULT 0,
                `unit_price` DECIMAL(10, 2) NOT NULL DEFAULT 0,
                `acreage` FLOAT(4) NOT NULL DEFAULT 0,
                `front_width` FLOAT(5) NOT NULL DEFAULT 0,
                `road_width` FLOAT(5) NOT NULL DEFAULT 0,
                `bedrooms` TINYINT(5) NOT NULL DEFAULT 0,
                `bathrooms` TINYINT(5) NOT NULL DEFAULT 0,
                `listing_type` BIGINT NULL,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `created_at` TIMESTAMP NOT NULL,
                PRIMARY KEY (`ID`) UNIQUE(`property_id`)',
            'wordland_agents' => '`wordland_agent_id` BIGINT NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT NOT NULL,
                `phone_number` VARCHAR(100) NULL,
                `address` VARCHAR(255) NOT NULL,
                `area_level_1` BIGINT NOT NULL,
                `area_level_2` BIGINT NOT NULL,
                `area_level_3` BIGINT NULL,
                `area_level_4` BIGINT NULL,
                `country_id` BIGINT NULL,
                PRIMARY KEY (`wordland_agent_id`)',
            'wordland_locations' => '`ID` BIGINT NOT NULL AUTO_INCREMENT,
                `term_id` BIGINT NOT NULL,
                `location_name` VARCHAR(255) DEFAULT \'\' COMMENT \'Location name with prefix is parent location\',
                `ascii_name` VARCHAR(255) DEFAULT \'\' COMMENT \'The clean location name use for multi purpose\',
                `location` GEOMETRY NULL,
                `center_point` POINT NULL,
                `geo_eng_name` VARCHAR(255) NULL COMMENT \'Use for Brower Location API\',
                `clean_name` VARCHAR(255) NULL COMMENT \'Use to improve query from location name\',
                `zip_code` VARCHAR(10) NULL,
                `created_at` TIMESTAMP NOT NULL,
                PRIMARY KEY (`ID`)',
            'wordland_search_histories' => '`ID` BIGINT NOT NULL AUTO_INCREMENT,
                `keyword_text` VARCHAR(255),
                `history_type` VARCHAR(255) DEFAULT \'general\',
                `reference_object` BIGINT NOT NULL DEFAULT 0,
                `reference_type` VARCHAR(255) NULL,
                `user_id` BIGINT NOT NULL DEFAULT 0,
                `ip` VARCHAR(255) DEFAULT \'\' COMMENT \'IP of the user searching property\',
                `created_at` TIMESTAMP NOT NULL,
                PRIMARY KEY (`ID`)',
            'wordland_message_references' => '`ID` BIGINT NOT NULL AUTO_INCREMENT,
                `message_id` BIGINT NOT NULL DEFAULT 0,
                `from_email` VARCHAR(255) NOT NULL,
                `from_name` VARCHAR(255) NOT NULL,
                `from_phone` VARCHAR(255) NULL,
                `to_user` BIGINT NOT NULL DEFAULT 0,
                `user_type` VARCHAR(255) NULL DEFAULT \'agent\',
                `attachment_post_id` BIGINT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NOT NULL,
                PRIMARY KEY (`ID`)',
            'wordland_agent_references' => '`ID` BIGINT NOT NULL AUTO_INCREMENT,
                `agent_id` BIGINT NOT NULL DEFAULT 0,
                `post_id` BIGINT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NOT NULL,
                PRIMARY KEY (`ID`)',
            'wordland_saved_searches' => '`ID` BIGINT NOT NULL AUTO_INCREMENT,
                `search_name` VARCHAR(255) DEFAULT \'My searched search\',
                `user_id` BIGINT NOT NULL DEFAULT 0,
                `guest_ip` VARCHAR(255) NULL,
                `search_content` LONGTEXT NOT NULL,
                `created_at` TIMESTAMP NOT NULL,
                PRIMARY KEY (`ID`)',
        );

        foreach ($tables as $table_name => $sql_syntax) {
            $sql = sprintf(
                'CREATE TABLE IF NOT EXISTS %s%s (%s) ENGINE = %s CHARSET=%s COLLATE=%s',
                $wpdb->prefix,
                $table_name,
                $sql_syntax,
                in_array($table_name, static::$myisamTables) ? 'MyISAM' : 'InnoDB',
                $wpdb->charset,
                $wpdb->collate
            );
            $wpdb->query($sql);
        }

        // Disable ONLY_FULL_GROUP_BY to group property by locations
        $wpdb->query("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    }
}
