<?php
namespace WordLand\Admin;

use WordLand\PostTypes;

class Scripts {
    const HANDLE_NAME = 'wordland-admin';

    protected $adminAssetDirUrl;
    protected $version;

    public function __construct() {
        $this->adminAssetDirUrl = sprintf('%sadmin/assets', plugin_dir_url(WORDLAND_PLUGIN_FILE));
        $this->version = array_get(
            get_file_data(
                WORDLAND_PLUGIN_FILE,
                array('version' => 'Version')
            ),
            'version'
        );
    }

    public function asset_url($path = '') {
        return sprintf('%s/%s', $this->adminAssetDirUrl, $path);
    }

    public function register() {
        $current_screen = get_current_screen();
        if ($current_screen->base !== 'post' || !in_array($current_screen->id, PostTypes::get())) {
            return;
        }
        wp_register_script(
            static::HANDLE_NAME,
            $this->asset_url('js/wordland.js'),
            array(),
            $this->version,
            true
        );

        wp_enqueue_script(static::HANDLE_NAME);
    }
}
