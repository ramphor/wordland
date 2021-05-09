<?php
namespace WordLand\Builder;

use WP_Post;
use WordLand;
use WordLand\Abstracts\PropertyBuilderAbstract;
use WordLand\PostTypes;
use WordLand\Agent;
use WordLand\Query\AgentQuery;
use WordLand\Admin\Property\MetaBox\PropertyInformation;
use WordLand\Locations;

class PropertyBuilder extends PropertyBuilderAbstract
{
    protected $property;
    protected $originalPost;

    public function buildContent()
    {
        $this->property->content = apply_filters(
            'the_content',
            $this->originalPost->post_content
        );
    }

    public function loadImages($size = null)
    {
        if (is_null($size)) {
            $size = 'full';
        }
        $galleryImages = (array)get_post_meta(
            $this->property->ID,
            WordLand::PROPERTY_GALLERY_META_KEY,
            true
        );
        foreach ($galleryImages as $galleryImage) {
            $imageUrl = wp_get_attachment_image_src($galleryImage, $size);
            if ($imageUrl) {
                array_push($this->property->images, $imageUrl);
            }
        }
    }

    public function getCoordinate()
    {
    }

    public function buildTypes()
    {
    }

    public function buildCategories()
    {
        $terms = wp_get_post_terms($this->property->ID, PostTypes::PROPERTY_CATEGORY_TAX, array(
            'fields' => 'id=>name'
        ));

        $this->property->categories = apply_filters(
            'wordland_build_property_categories',
            $terms,
            $this->property
        );
    }

    public function buildTags()
    {
    }

    public function buildLocations()
    {
        $location_taxs = array(
            Locations::BOUNDARY_LEVEL_1,
            Locations::BOUNDARY_LEVEL_2,
            Locations::BOUNDARY_LEVEL_3,
        );

        if (apply_filters('wordland_enable_country_taxonomy', false)) {
            array_unshift($location_taxs, Locations::COUNTRY_LEVEL);
        }
        if (apply_filters('wordland_enable_area_level_4', false)) {
            array_push($location_taxs, Locations::BOUNDARY_LEVEL_4);
        }

        $terms = wp_get_post_terms($this->property->ID, $location_taxs);
        foreach ($terms as $term) {
            $this->property->setLocationByLevel(apply_filters(
                "wordland_set_location_{$term->taxonomy}",
                array(
                    'name' => $term->name,
                    'term_id'=> $term->term_id,
                    'url'=> get_term_link($term, $term->taxonomy),
                )
            ), $term->taxonomy);
        }
    }

    public function getVideo()
    {
        $video_url = get_post_meta($this->property->ID, PropertyInformation::VIDEO_META_KEY, true);
        if ($video_url) {
            $this->property->videoUrl = $video_url;
        }
    }

    public function getPrimaryAgent()
    {
        global $wpdb;

        $agentQuery = new AgentQuery(array(
            'user_id' => $this->originalPost->post_author
        ));
        $agentQuery->select(sprintf('%s.*, %s.*', $wpdb->users, $wpdb->prefix . 'wordland_agents'));
        $wp_query = $agentQuery->getWordPressQuery();
        $users = $wp_query->get_results();

        if (count($users) > 0) {
            $owner = array_shift($users);
            unset($owner->user_pass);

            $ownerId = intval($owner->ID);

            // Create primary agent
            $agent = new Agent($owner->display_name);
            $agent->setUserID($ownerId);
            $agent->getAvatarUrlFromUser();
            $agent->setPhoneNumber($owner->phone_number);

            do_action_ref_array('wordland_primary_agent', array(
                &$agent,
                $owner,
                $this->property
            ));

            $this->property->primaryAgent     = $ownerId;
            $this->property->agents[$ownerId] = $agent;
        }
    }

    public function getPropertyVisibilities()
    {
        $terms = wp_get_post_terms($this->property->ID, PostTypes::PROPERTY_VISIBILITY, array(
            'fields' => 'id=>name'
        ));

        $this->property->visibilities = apply_filters(
            'wordland_visibilities',
            $terms,
            $this->property
        );
    }
}
