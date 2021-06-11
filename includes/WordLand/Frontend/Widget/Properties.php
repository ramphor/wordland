<?php
namespace WordLand\Frontend\Widget;

use WP_Widget;
use Jankx;
use WordLand\Query\PropertyQuery;
use WordLand\Frontend\Renderer\Properties as PropertiesRenderer;

class Properties extends WP_Widget
{
    public function __construct()
    {
        $options = array(
            'classname' => 'wl-properties',
            'description' => __('Show properties to your sidebars', 'wordland'),
        );

        parent::__construct(
            'wordland_properties',
            sprintf(__('%s Properties', 'wordland'), Jankx::templateName()),
            $options
        );
    }


    protected function getDisplayTypes()
    {
        return array(
            'latest' => __('Lastest Propeties', 'wordland'),
            'most_views' => __('Most Views', 'wordland'),
        );
    }

    public function form($instance)
    {
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <?php echo esc_html(__('Title')); ?>
            </label>
            <input
                type="text"
                id="<?php echo $this->get_field_id('title'); ?>"
                class="widefat"
                name="<?php echo $this->get_field_name('title'); ?>"
                value="<?php echo array_get($instance, 'title'); ?>"
            />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('display_type'); ?>">
                <?php echo esc_html(_e('Display Type', 'wordland')); ?>
            </label>
            <select
                name="<?php echo $this->get_field_name('display_type'); ?>"
                id="<?php echo $this->get_field_id('display_type'); ?>"
                class="widefat"
            >
                <?php foreach ($this->getDisplayTypes() as $display_type => $label) : ?>
                    <option value="<?php echo $display_type; ?>"
                        <?php selected(array_get($instance, 'display_type', 'latest'), $display_type); ?>
                    >
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('posts_per_page'); ?>">
                <?php echo esc_html(__('Number of properties', 'wordland')); ?>
            </label>
            <input
                type="number"
                name="<?php echo $this->get_field_name('posts_per_page'); ?>"
                id="<?php echo $this->get_field_id('posts_per_page'); ?>"
                class="widefat"
                value="<?php echo array_get($instance, 'posts_per_page', 3); ?>"
            />
        </p>
        <?php
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        if (isset($instance['title'])) {
            echo $args['before_title'];
                echo $instance['title'];
            echo $args['after_title'];
        }
            $propertyQuery = new PropertyQuery(array(
                'posts_per_page' => array_get($instance, 'posts_per_page', 3),
            ));
            $renderer = new PropertiesRenderer($propertyQuery);

            $renderer->addProp('is_widget_content', true);

            echo $renderer->get_content();
        echo $args['after_widget'];
    }
}
