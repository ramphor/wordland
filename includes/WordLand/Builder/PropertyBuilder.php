<?php
namespace WordLand\Builder;

use WP_Post;
use WordLand;
use WordLand\Abstracts\PropertyBuilderAbstract;
use WordLand\PostTypes;
use WordLand\Agent;
use WordLand\Query\AgentQuery;

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
    }

    public function buildTags()
    {
    }

    public function buildLocations()
    {
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
