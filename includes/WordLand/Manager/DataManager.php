<?php
namespace WordLand\Manager;

use WordLand\Abstracts\ManagerAbstract;
use WordLand\PostTypes;
use WordLand\Query\PropertyQuery;

class DataManager extends ManagerAbstract
{
    protected static $instance;

    protected function __construct()
    {
        add_action('init', array('manage'));
    }

    public function manage()
    {
        add_action('set_object_terms', array($this, 'autoSetListingType'), 10, 4);
    }

    public function autoSetListingType($object_id, $terms, $tt_ids, $taxonomy)
    {
        // Only allow taxonomy listing type
        if (PostTypes::PROPERTY_LISTING_TYPE !== $taxonomy && !PropertyQuery::check_wordland_data_is_exists($object_id)) {
            return;
        }
        global $wpdb;

        $firstListingType = array_shift($terms);
        $listingTypeId    = $firstListingType->term_id;

        return $wpdb->update($wpdb->prefix . 'wordland_properties', array(
            'listing_type' => $listingTypeId
        ), array(
            'property_id' => $object_id
        ));
    }
}
