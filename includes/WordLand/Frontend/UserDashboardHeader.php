<?php
namespace WordLand\Frontend;

use WordLand\Template;

class UserDashboardHeader
{
    public function init()
    {
        add_action('wordland_before_feature_content', array($this, 'renderHeader'));
    }

    public function renderListingCounter()
    {
    }

    public function renderImagesLimit()
    {
    }

    public function renderHeader()
    {
        $headerBlocks = apply_filters('wordland_user_profile_header_blocks', array(
            'listing_count' => array(
                'heading' => __('Listings Included', 'wordland'),
                'heading_position' => 'top',
                'render' => array($this, 'renderListingCounter'),
                'priority' => 10,
            ),
            'images_limit' => array(
                'heading' => __('Images/listing', 'wordland'),
                'heading_position' => 'bottom',
                'render' => array($this, 'renderImagesLimit'),
                'priority' => 50,
            ),
        ));

        usort($headerBlocks, function ($blockA, $blockB) {
            $blockA = wp_parse_args($blockA, array(
                'priority' => 20,
            ));
            $blockB = wp_parse_args($blockB, array(
                'priority' => 20,
            ));
            return $blockA['priority'] - $blockB['priority'];
        });
        ?>
        <div>User dashboard header</div>
        <?php
        foreach ($headerBlocks as $index => $headerBlock) {
            $headerBlocks[$index] = wp_parse_args($headerBlock, array(
                'heading' => __('Block heading', 'wordland'),
                'heading_position' => 'top',
                'render' => '__return_empty_string',
            ));
            if (!is_callable($headerBlocks[$index]['render'])) {
                unset($headerBlocks[$index]);
                continue;
            }
            $headerBlocks[$index]['render'] = call_user_func($headerBlocks[$index]['render']);
        }

        return Template::render('agent/my-profile/header', array(
            'blocks' => $headerBlocks,
        ));
    }
}
