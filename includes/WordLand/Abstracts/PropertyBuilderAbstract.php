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
        $this->property->listingTypeId = intval($this->originalPost->listing_type);

        $this->property->description = get_the_excerpt($this->originalPost);
        $this->property->createdAt = strtotime($this->originalPost->post_date);
        $this->property->url = get_permalink($this->originalPost);

        $listing_type = get_term($this->property->listingTypeId);
        if (!is_wp_error($listing_type)) {
            $this->property->listingType = array(
                'id' => $listing_type->term_id,
                'name' => $listing_type->name
            );
            unset($listing_type);
        }
        $this->createCodeID();
        $this->createPropertyThumbnail();
    }

    protected function createCodeID()
    {
        if (wordland_get_option('use_sku_as_code_id', true)) {
            return $this->property->codeID = $this->sku;
        }
        $prefix = get_option('wordland_property_code_id_prefix');
        $this->property->codeID = sprintf(
            '%s%010s',
            $prefix ? strtoupper($prefix . '_') : '#',
            $this->originalPost->ID
        );
    }

    protected function createPropertyThumbnail()
    {
        $thumbnail_id = get_post_thumbnail_id($this->property->ID);
        if ($thumbnail_id) {
            $this->property->thumbnail = array(
                'id' => $thumbnail_id,
                'url' => get_the_post_thumbnail($thumbnail_id, 'medium')
            );
        } else {
            $this->property->thumbnail = false;
        }
    }

    public function buildWordLandData()
    {
        if (isset($this->originalPost->property_id)) {
            $this->property->price     = floatval($this->originalPost->price);
            $this->property->unitPrice = floatval($this->originalPost->unit_price);


            $this->property->bedrooms    = intval($this->originalPost->bedrooms);
            $this->property->bathrooms   = intval($this->originalPost->bathrooms);
            $this->property->total_views = intval($this->originalPost->total_views);

            $this->property->frontWidth  = floatval($this->originalPost->front_width);
            $this->property->roadWidth   = floatval($this->originalPost->road_width);
            $this->property->acreage     = floatval($this->originalPost->acreage);

            $this->property->address      = $this->originalPost->address;
            $this->property->fullAddress  = $this->originalPost->full_address;

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

        do_action_ref_array("wordland_before_get_{$scope}_property", array(
            &$this->property,
            $this->originalPost,
            $scope
        ));

        if ($scope == 'single') {
            $this->loadImages(apply_filters(
                'wordland_single_property_image_sizes',
                'large',
                $this->property,
                $this
            ));
            $this->buildCategories();
            $this->buildLocations();
            $this->getVideo();
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
