<?php
/**
 * WordLand uninstaller
 */
namespace WordLand;

class Uninstaller {
    public function __construct() {
        $this->cleanDatabase();
        $this->deleteOptions();
    }

    public function cleanDatabase() {
    }

    public function deleteOptions() {
    }
}

new Uninstaller();
