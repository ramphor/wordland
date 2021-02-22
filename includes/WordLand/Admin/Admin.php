<?php
namespace WordLand\Admin;

use WordLand\Admin\Property\MetaBox\PropertyInformation;
use WordLand\Admin\Menu\Dashboard;

class Admin
{
    public function __construct()
    {
        $this->initHooks();
    }

    protected function initHooks()
    {
        add_action('admin_init', array($this, 'initProperty'));

        $dashboard = new Dashboard();
        add_action('admin_menu', array($dashboard, 'registerDashboard'));
    }

    public function initProperty()
    {
        $propertyMetabox = new PropertyInformation();
        add_action('add_meta_boxes', array($propertyMetabox, 'registerMetaboxes'));
    }
}
