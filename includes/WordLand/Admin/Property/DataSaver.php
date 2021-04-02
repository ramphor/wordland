<?php
namespace WordLand\Admin\Property;

use WordLand\PostTypes;
use WordLand\Admin\Property\MetaBox\PropertyInformation;

class DataSaver
{
    public function save($post_id, $post)
    {
        if (!in_array($post->post_type, PostTypes::get())) {
            return;
        }

        if (isset($_POST['video_url'])) {
            update_post_meta($post_id, PropertyInformation::VIDEO_META_KEY, $_POST['video_url']);
        }
    }
}
