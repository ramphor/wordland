<?php
namespace WordLand\Modules\Ajax;

use WordLand\Abstracts\ModuleAbstract;
use WordLand\Template;
use WordLand\Query\PropertyQuery;

class LoadMoreProperties extends ModuleAbstract {
    const MODULE_NAME = 'load_more_properties';

    public function get_name() {
        return static::MODULE_NAME;
    }

    public function init() {
        add_action('wp_ajax_wordland_load_more_listing', array($this, 'ajaxRequest'));
        add_action('wp_ajax_nopriv_wordland_load_more_listing', array($this, 'ajaxRequest'));
    }

    protected function buildQuery($request) {
        $propertyQuery = new PropertyQuery(array(
            'paged' => $request['current_page'] + 1,
        ));

        $this->wp_query = $propertyQuery->getWordPressQuery();
    }

    public function renderMoreItems() {
        if (!$this->wp_query) {
            return false;
        }
        $content = '';
        ?>
        <?php if ($this->wp_query->have_posts()) : ?>
            <?php while ($this->wp_query->have_posts()) : ?>
                <?php $this->wp_query->the_post(); ?>
                <?php
                $content .= Template::render(
                    'content/property',
                    array(
                        'property' => $GLOBALS['property'],
                        'style' => $style,
                    ), false
                );
                ?>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <?php
        endif;

        return $content;
    }

    public function ajaxRequest()
    {
        $this->buildQuery($_REQUEST);

        if (!$this->wp_query) {
            $data = array();
            wp_send_json_error($data);
        }

        $data = array(
            'list_items_html' => $this->renderMoreItems(),
            'current_page' => array_get($this->wp_query->query_vars, 'paged', 1),
        );
        wp_send_json_success($data);
    }
}
