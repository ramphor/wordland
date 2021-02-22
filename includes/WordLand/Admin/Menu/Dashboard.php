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
        echo __('Currently, WordLand support customize via action hooks and filter hooks only.
        The options will be develop in the future', 'wordland');
    }
}
