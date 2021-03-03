<?php
namespace WordLand\Abstracts;

use WordLand\Constracts\PropertyBuilder;
use WordLand\Property;
use WordLand\Coordinate;
use WordLand\PostTypes;

abstract class PropertyBuilderAbstract implements PropertyBuilder
{
    protected $property;
    protected $originalPost;

    public function __construct($post = null)
    {
        $this->reset();
        if ($post) {
            $this->setPost($post);
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    public function reset()
    {
        $this->property = new Property();
    }

    public function setPost($post)
    {
        if (!is_a($post, \WP_Post::class)) {
            return;
        }

        $this->originalPost = $post;
    }


    public function buildBaseData()
    {
        if (is_null($this->originalPost)) {
            return;
        }
        $this->property->ID   = (int)$this->originalPost->ID;
        $this->property->name = apply_filters(
            'the_title',
            $this->originalPost->post_title
        );

        $this->property->description = get_the_excerpt($this->originalPost);
        $this->property->createdAt = strtotime($this->originalPost->post_date);

        $listing_type = wp_get_post_terms($this->originalPost->ID, PostTypes::PROPERTY_LISTING_TYPE, array(
            'number' => 1
        ));

        if (count($listing_type)) {
            $this->property->listingType = array(
                'id' => $listing_type[0]->term_id,
                'name' => $listing_type[0]->name
            );
            unset($listing_type);
        }
        $this->createCodeID();
        $this->createPropertyThumbnail();
    }

    protected function createCodeID()
    {
        $prefix = get_option('wordland_property_code_id_prefix');
        $this->property->codeID = sprintf(
            '%s%010s',
            $prefix ? strtoupper($prefix . '_') : '#',
            $this->originalPost->ID
        );
    }

    protected function createPropertyThumbnail()
    {
        $thumbnail_id = get_post_thumbnail_id($this->property->codeID);
        if ($thumbnail_id) {
            $this->property->thumbnail = array(
                'id' => $thumbnail_id,
                'url' => wordland_post_thumbnail($thumbnail_id)
            );
        } else {
            $this->property->thumbnail = false;
        }
    }

    public function buildWordLandData()
    {
        if (isset($this->originalPost->property_id)) {
            $this->property->price      = floatval($this->originalPost->price);
            $this->property->unit_price = floatval($this->originalPost->unit_price);
            $this->property->acreage       = intval($this->originalPost->acreage);
            $this->property->bedrooms   = intval($this->originalPost->bedrooms);
            $this->property->bathrooms  = intval($this->originalPost->bathrooms);

            if (isset($this->originalPost->latitude) && $this->originalPost->latitude) {
                $this->property->geolocation = new Coordinate(
                    floatval($this->originalPost->latitude),
                    floatval($this->originalPost->longitude)
                );
            }
        }
    }

    // The alias of buildBaseData
    public function build()
    {
        $this->buildBaseData();
        $this->buildWordLandData();
        $this->getPrimaryAgent();
    }

    public function getProperty($scope = 'global')
    {
        if (!$this->property->ID) {
            return;
        }

        return apply_filters_ref_array(
            'wordland_builder_get_property',
            array(
                &$this->property,
                $this,
                $scope
            )
        );
    }
}
