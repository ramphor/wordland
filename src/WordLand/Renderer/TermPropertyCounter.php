<?php
namespace WordLand\Renderer;

use WordLand\Abstracts\Renderer;
use WordLand\Template;

class TermPropertyCounter extends Renderer
{
    public function get_content()
    {
        $termId = array_get($this->props, 'term_id', false);
        if (!$termId) {
            return;
        }
        $term = get_term($termId);
        if (!$term) {
            return _e('The term is invalid', 'wordland');
        }
        $description = array_get($this->props, 'description');
        if (!$description) {
            $description = $term->description;
        }

        Template::render('widget/term-property-counter', array(
            'header' => !$this->title ? $term->name : $this->title,
            'description' => $description,
            'image_bg' => 'https://wpresidence.b-cdn.net/wp-content/uploads/2020/08/city9.jpg',
            'count' => sprintf(__('%d listing', 'wordland'), $term->count),
            'term' => $term,
        ));
    }
}
