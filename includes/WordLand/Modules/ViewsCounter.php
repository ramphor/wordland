<?php
namespace WordLand\Modules;

use WordLand\Abstracts\ModuleAbstract;
use WordLand\PostTypes;
use WordLand\Query\PropertyQuery;

class ViewsCounter extends ModuleAbstract
{
    const MODULE_NAME = 'views_counter';

    public function get_name()
    {
        return static::MODULE_NAME;
    }

    public function init()
    {
        $postTypes = PostTypes::get();
        foreach ($postTypes as $postType) {
            add_action("update_{$postType}_total_views", array($this, 'updateTotalViews'), 10, 2);
        }
    }

    public function updateTotalViews($totalViews, $post_id)
    {
        global $wpdb;

        $property_metas = array(
            'property_id' => $post_id,
            'total_views' => $totalViews,
        );

        if (($meta_id = PropertyQuery::check_wordland_data_is_exists($post_id)) > 0) {
            return $wpdb->update("{$wpdb->prefix}wordland_properties", $property_metas, array(
                'ID' => $meta_id,
            ));
        }

        $meta_id = $wpdb->insert("{$wpdb->prefix}wordland_properties", $property_metas);

        if (!is_wp_error($meta_id) && $meta_id > 0) {
            $sql = $wpdb->prepare(
                "UPDATE {$wpdb->prefix}wordland_properties SET coordinate=ST_GeomFromText('POINT(0 0)') WHERE ID=%d",
                $meta_id
            );
            $wpdb->query($sql);
        }
    }
}
