<?php
namespace WordLand\Renderer;

use WordLand\Abstracts\Renderer;
use WordLand\Constracts\Query;

class Properties extends Renderer {
    protected $query;

    public function __construct($query) {
        if (is_a($query, Query::class)) {
            $this->query = $query;
        }
    }

    public function get_content() {
        return 'listing ne';
    }
}
