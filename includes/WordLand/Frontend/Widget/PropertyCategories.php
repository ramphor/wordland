<?php
namespace WordLand\Frontend\Widget;

use Jankx;
use WP_Widget;
use WordLand\Frontend\Renderer\PropertyCategories as PropertyCategoriesRenderer;

class PropertyCategories extends WP_Widget
{
    public function __construct()
    {
        $options = array(
            'classname' => 'wordland-property-categories',
        );
        parent::__construct(
            'wordland_property_categories',
            sprintf(
                '%s %s',
                Jankx::templateName(),
                __('Property Categories', 'wordland')
            ),
            $options
        );
    }

    public function form($instance)
    {
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo esc_html(__('Title')); ?></label>
            <input
                type="text"
                id="<?php echo $this->get_field_id('title'); ?>"
                class="widefat"
                name="<?php echo $this->get_field_name('title'); ?>"
                value="<?php echo array_get($instance, 'title'); ?>"
            />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('hide_empty'); ?>">
                <input
                    type="checkbox"
                    id="<?php echo $this->get_field_id('hide_empty') ?>"
                    name="<?php echo $this->get_field_name('hide_empty'); ?>"
                    value="yes"
                    <?php checked('yes', array_get($instance, 'hide_empty')); ?>
                />
                Hide Empty Categories
            </label>
        </p>
        <?php
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        if (isset($instance['title'])) {
            echo $args['before_title'];
                echo array_get($instance, 'title');
            echo $args['after_title'];
        }

        $renderer = new PropertyCategoriesRenderer();
        $renderer->setProps(array(
            'hide_empty' => array_get($instance, 'hide_empty') === 'yes'
        ));

        echo $renderer->get_content();

        echo $args['after_widget'];
    }
}
