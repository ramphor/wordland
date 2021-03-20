<?php
namespace WordLand;

use WordLand\Abstracts\Data;

class Agent extends Data
{
    protected $userID = 0;
    /**
     * @var string This is userLogin or user name can use to login in WordPress
     */
    protected $userLogin;

    /**
     * @var WP_User
     */
    protected $user;

    protected $name;
    protected $email;

    protected $phoneNumber;
    protected $address;
    protected $areaLevel1;
    protected $areaLevel2;
    protected $areaLevel3;
    protected $areaLevel4;
    protected $countryId;

    protected $avatarUrl;

    public function __construct($name = null, $phoneNumber = null, $email = null)
    {
        if (!is_null($name)) {
            $this->setName($name);
        }
        if (!is_null($phoneNumber)) {
            $this->setPhoneNumber($phoneNumber);
        }
        if (!is_null($email)) {
            $this->setEmail($email);
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    public function setUserID($userID)
    {
        $this->userID = $userID;
    }

    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setUserLogin($userLogin)
    {
        $this->userLogin = $userLogin;
    }

    protected function get_data_format($val)
    {
        switch (gettype($val)) {
            case 'boolean':
            case 'integer':
                return 'd';
            case 'double':
                return 'f';
            default:
                return 's';
        }
    }

    protected function check_wordland_data_is_exists($data)
    {
        global $wpdb;
        $rawSql = "SELECT wordland_agent_id FROM {$wpdb->prefix}wordland_agents WHERE";
        foreach ($data as $key => $val) {
            switch ($key) {
                case 'address':
                    if (empty($val)) {
                        break;
                    }
                    $rawSql .= $wpdb->prepare(" `{$key}`=%{$this->get_data_format($val)} AND", $val);
                    break;
                default:
                    $rawSql .= $wpdb->prepare(" `{$key}`=%{$this->get_data_format($val)} AND", $val);
                    break;
            }
        }
        $rawSql  = rtrim($rawSql, ' AND');
        $rawSql .= " LIMIT 1";

        return intval($wpdb->get_var($rawSql));
    }

    public function setAvatarUrl($avatarUrl)
    {
        if (filter_var($avatarUrl, FILTER_VALIDATE_URL)) {
            $this->avatarUrl = $avatarUrl;
        }
    }

    public function save($create_address_only = false)
    {
        $userData = array();

        if (!$create_address_only) {
            if ($this->userID > 0) {
                $userData['ID'] = $this->userID;
                $this->userID = wp_update_user($userData);
            } else {
                $userData = array_merge($userData, array(
                    'user_login' => $this->userLogin,
                    'display_name' => $this->name,
                    'user_pass' => null,
                ));

                $userData['show_admin_bar_front'] = apply_filters(
                    'wordland_agent_with_show_admin_bar_front',
                    false,
                    $userData,
                    $this
                );

                $role = apply_filters(
                    'wordland_agent_default_role',
                    null,
                    $userData,
                );
                $this->userID = wp_insert_user($userData);
            }
        }

        if (is_wp_error($this->userID)) {
            error_log($this->userID->get_error_message());
            return false;
        }
        global $wpdb;

        $agent_data = array(
            'user_id' => $this->userID,
            'phone_number' => (string)$this->phoneNumber,
            'address' => (string)$this->address,
            'area_level_1' => intval($this->areaLevel1),
            'area_level_2' => intval($this->areaLevel2),
            'area_level_3' => intval($this->areaLevel3),
            'area_level_4' => intval($this->areaLevel4),
            'country_id' => intval($this->countryId),
        );
        $agent_table_name = sprintf('%swordland_agents', $wpdb->prefix);

        if ($this->check_wordland_data_is_exists($agent_data)) {
            $wpdb->update($agent_table_name, $agent_data, array(
                'user_id' => $this->userID
            ));
        } else {
            $wpdb->insert($agent_table_name, $agent_data);
        }
        return $this->userID;
    }
}
