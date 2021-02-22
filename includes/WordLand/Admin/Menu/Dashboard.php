<?php
namespace WordLand\Admin\Menu;

class Dashboard {
    public function registerDashboard() {
        add_menu_page(
            sprintf('WordLand %s', __('Options')),
            'WordLand',
            'manage_options',
            'wordland',
            array( $this, 'renderDashboard' ),
            '',
            15
        );
    }

    public function renderDashboard() {
    }
}
