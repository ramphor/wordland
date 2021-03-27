<?php
namespace WordLand\Modules;

use WordLand\Abstracts\ModuleAbstract;
use WordLand\Template;
use Embrati\Embrati;

class AgentReviews extends ModuleAbstract
{
    const MODULE_NAME = 'agent-reviews';
    const MODULE_VERSION = '1.0.0.0';

    protected $embrati;
    protected $messages = array();

    public function get_name()
    {
        return static::MODULE_NAME;
    }

    public function __construct()
    {
        $this->embrati  = Embrati::getInstance('wordland_agent_review');
    }

    public function noPermission()
    {
    }

    public function init()
    {
        add_action('wp_ajax_wordland-review-agent', array($this, 'writeReviews'));
        add_action('wp_ajax_nopriv_wordland-review-agent', array($this, 'noPermission'));
    }

    public function inited()
    {
        add_action("wordland_after_agent_agent_content", array($this, 'renderReviewUI'));

        if (is_user_logged_in()) {
            $this->embrati->registerScripts();
        }
    }

    public function load_scripts()
    {
        wp_register_script(
            'wordland-review-agent',
            wordland_get_asset_url('js/wordland-review-agent.js'),
            array('wordland'),
            static::MODULE_VERSION,
            true
        );
        wp_enqueue_script('wordland-review-agent');
    }

    public function renderReviewUI($agent_data)
    {
        $current_agent = get_queried_object();

        return Template::render('agent/reviews', array(
            'embrati' => $this->embrati->create('agent_reviews', array(
                'echo' => false,
                'rateCallback' => 'wordland_agent_set_rateing_value',
            )),
            'agent_id' => $current_agent->ID,
            'open_form' => $this->open_review_form(),
            'close_form' => $this->close_review_form(),
            'login_url' => wp_login_url(get_author_posts_url($current_agent->ID)),
        ));
    }

    protected function open_review_form()
    {
        $attributes = array(
            'method' => 'POST',
            'id' => 'wordland-review-agent',
            'action' => admin_url('admin-ajax.php?action=wordland-review-agent')
        );
        return sprintf('<form %s>', jankx_generate_html_attributes($attributes));
    }

    protected function close_review_form()
    {
        return '</form>';
    }

    protected function field_rules()
    {
        return array(
            'agent_rating' => array(
                'label' => __('Review rating', 'wordland'),
                'required' => false,
                'validate' => array('format' => 'number')
            ),
            'agent_id' => array(
                'label' => __('Agent', 'wordland'),
                'required' => true,
            ),
            'review_title' => array(
                'label' => __('Review title', 'wordland'),
                'required' => true,
                'validate' => array()
            ),
            'review_body' => array(
                'label' => __('Review body', 'wordland'),
                'required' => true,
                'validate' => array()
            )
        );
    }

    public function validate_data_before_send()
    {
        $field_rules = $this->field_rules();

        foreach ($field_rules as $id => $rules) {
            $rules = wp_parse_args($rules, array(
                'label' => $id,
                'required' => false,
                'validate' => array(),
            ));
            $value = array_get($_POST, $id);
            $_SESSION['review_temp_data'][$id] = $value;
            if ($rules['required'] && empty($value)) {
                $this->messages[$id] = sprintf(__('Field %s must has a value', 'wordland'), strtolower($rules['label']));
                continue;
            }
        }

        return empty($this->messages);
    }

    public static function get_agent_post_reference($agent_user_id)
    {
        global $wpdb;

        $anget_post_id = 0;
        $sql = $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}wordland_agent_references WHERE agent_id=%d LIMIT 1",
            $agent_user_id
        );

        $anget_post_id = intval($wpdb->get_var($sql));

        return $anget_post_id;
    }

    public function writeReviews()
    {
        if ($this->validate_data_before_send()) {
            $current_user = wp_get_current_user();
            $agent_post_id = static::get_agent_post_reference(array_get($_POST, 'agent_id'));

            $comment_data = array(
                'comment_approved' => true,
                'comment_post_ID' => $agent_post_id,
                'comment_content' => array_get($_POST, 'review_body'),
                'user_id' => $current_user->ID,
            );
            $comment_id = wp_insert_comment($comment_data);

            if (!is_wp_error($comment_id) && $comment_id > 0) {
                $agent_rating = array_get($_POST, 'agent_rating');
                if ($agent_rating > 0) {
                    update_comment_meta($comment_id, 'agent_rating', $agent_rating);
                }
                if (isset($_POST['review_title'])) {
                    update_comment_meta($comment_id, 'review_title', array_get($_POST, 'review_title'));
                }
            }
        }

        wp_safe_redirect(array_get($_SERVER, 'HTTP_REFERER', site_url()));
    }
}
