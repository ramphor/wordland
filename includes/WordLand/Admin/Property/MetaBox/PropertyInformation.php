<?php
namespace WordLand\Admin\Property\MetaBox;

class PropertyInformation
{
    const VIDEO_META_KEY = '_video_url';

    public function registerMetaboxes()
    {
        add_meta_box(
            'property-information-box',
            __('Property Informations', 'wordland'),
            array($this, 'render'),
            'property',
            'advanced',
            'high'
        );

        add_meta_box(
            'property-video-box',
            __('Property Video', 'wordland'),
            array($this, 'renderVideoBox'),
            'property',
            'advanced',
            'high'
        );
    }

    // Render metabox content
    public function render()
    {
        ?>
        <div id="wordland-editting-infos-box"></div>
        <?php
    }

    public function renderVideoBox($post)
    {
        $video_url = get_post_meta($post->ID, static::VIDEO_META_KEY, true);
        ?>
        <p>
            <label>
                Video URL
            </label>
            <input class="widefat" name="video_url" value="<?php echo $video_url; ?>" />
        </p>
        <?php
    }
}
