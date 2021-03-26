<?php
namespace WordLand\Modules;

use WordLand\Abstracts\ModuleAbstract;
use WordLand\Template;
use WordLand\PostTypes;

session_start();

class SendAgentMessage extends ModuleAbstract
{
    const MODULE_NAME    = 'send-message';
    const MODULE_VERSION = '1.0.0.5';

    protected $messages = array();

    public function get_name()
    {
        return static::MODULE_NAME;
    }

    public function init()
    {
        add_action('wp_ajax_wordland_send_message', array($this, 'send_message'));
        add_action('wp_ajax_nopriv_wordland_send_message', array($this, 'send_message'));
    }

    public function inited()
    {
        if (!get_query_var('ramphor_user_profile')) {
            return;
        }
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action("wordland_after_agent_agent_content", array($this, 'render_send_message_ui'));

        add_action('wordland_before_message_sender_content', array($this, 'renderSendMessages'));
    }

    public function enqueue_scripts()
    {
        wp_register_script(
            'wordland-message-sender',
            wordland_get_asset_url('js/wordland-message-sender.js'),
            array(),
            static::MODULE_VERSION,
            true
        );

        // wp_enqueue_script('wordland-message-sender');
    }

    protected function open_send_message_form()
    {
        $formAttributes = array(
            'method' => 'POST',
            'id' => 'wordland-agent-message-sender',
            'action' => admin_url('admin-ajax.php?action=wordland_send_message'),
        );

        return sprintf(
            '<form %s>',
            jankx_generate_html_attributes($formAttributes)
        );
    }

    protected function close_send_message_form()
    {
        return '</form>';
    }

    public function render_send_message_ui($agent_data)
    {
        $current_agent = get_queried_object();
        $field_values = array();
        $fields = array_keys($this->validate_rules());

        foreach ($fields as $field) {
            if (isset($_SESSION['temp_data'][$field])) {
                $field_values[$field] = $_SESSION['temp_data'][$field];
            } else {
                $field_values[$field] = '';
            }
        }

        Template::render('agent/send-message', array_merge($field_values, array(
            'current_agent' => $current_agent,
            'user_type' => get_query_var('ramphor_user_profile'),
            'open_form' => $this->open_send_message_form(),
            'close_form' => $this->close_send_message_form(),
        )));
        unset($_SESSION['temp_data']);
    }

    public function renderSendMessages()
    {
        echo '<div class="all-send-messages">';
        $messages = array_get($_SESSION, 'agent_messages', array());
        foreach ($messages as $message) {
            $message = wp_parse_args($message, array(
                'success' => false,
                'message' => '',
            ));
            printf('<div class="alert message-%s">%s</div>', $message['success'] ? 'sucess' : 'error', $message['message']);
        }
        echo '</div>';

        unset($_SESSION['agent_messages']);
    }

    protected function create_message_reference($reference_data = array())
    {
        global $wpdb;
        return $wpdb->insert(
            "{$wpdb->prefix}wordland_message_references",
            $reference_data
        );
    }

    protected function validate_rules()
    {
        $field_rules = array(
            'from_name' => array(
                'label' => __('Your name', 'wordland'),
                'required' => true,
                'validate' => array('min_length' => 5, 'max_length' => 50),
            ),
            'from_email' => array(
                'label' => __('Your email', 'wordland'),
                'required' => true,
                'validate' => array('format' => 'email')
            ),
            'from_phone' => array(
                'label' => __('Your phone', 'wordland'),
                'required' => apply_filters('wordland_agent_send_message_require_phone', false),
                'validate' => array('format' => 'phone')
            ),
            'object_id' => array(
                'label' => __('Agent ID', 'wordland'),
                'required' => true,
                'validate' => array('format' => 'number')
            ),
            'object_type' => array(
                'label' => __('User type', 'wordland'),
                'required' => true,
            ),
            'message_body' => array(
                'label' => __('Your message', 'wordland'),
                'required' => true,
            )
        );

        return $field_rules;
    }

    protected function validate_data_before_send()
    {
        $field_rules = $this->validate_rules();

        foreach ($field_rules as $id => $rules) {
            $rules = wp_parse_args($rules, array(
                'label' => $id,
                'required' => false,
                'validate' => array(),
            ));
            $value = array_get($_POST, $id);
            $_SESSION['temp_data'][$id] = $value;
            if ($rules['required'] && empty($value)) {
                $this->messages[$id] = sprintf(__('Field %s must has a value', 'wordland'), strtolower($rules['label']));
                continue;
            }
        }

        return empty($this->messages);
    }

    protected function check_request_via_js()
    {
        return array_get($_SERVER, 'HTTP_X_REQUESTED_WITH', false) === 'XMLHttpRequest';
    }

    public function filter_email_from_sender()
    {
        return array_get($_POST, 'from_email');
    }

    public function filter_email_name_sender()
    {
        return array_get($_POST, 'from_name');
    }

    protected function send_email_message()
    {
        add_filter('wp_mail_from', array($this, 'filter_email_from_sender'));
        add_filter('wp_mail_from_name', array($this, 'filter_email_name_sender'));

        $emailOk = wp_mail($to, $subject, $message, $headers, $attachments);

        remove_filter('wp_mail_from', array($this, 'filter_email_from_sender'));
        remove_filter('wp_mail_from_name', array($this, 'filter_email_name_sender'));

        if ($emailOk) {
            return array(
                'status' => true,
                'message' => __('The message was sent!', 'wordland'),
            );
        }
        return array(
            'status' => false,
            'message' => __('error', 'wordland')
        );
    }

    protected function send_prive_message()
    {
        $messageSubjet = sprintf(__('Message from %s'), array_get($_POST, 'from_name'));
        $post_data = array(
            'post_type'    => PostTypes::AGENT_MESSAGE_POST_TYPE,
            'post_content' => array_get($_POST, 'message_body'),
            'post_author'  => get_current_user_id(),
            'post_status'  => 'publish',
        );

        $message_id = wp_insert_post($post_data);

        if (!is_wp_error($message_id) && $message_id > 0) {
            $referenceOk = $this->create_message_reference(array(
                'message_id' => $message_id,
                'from_email' => array_get($_POST, 'from_email'),
                'from_name' => array_get($_POST, 'from_name'),
                'from_phone' => array_get($_POST, 'from_phone'),
                'to_user' => array_get($_POST, 'object_id'),
                'user_type' => array_get($_POST, 'object_type'),
                'created_at' => current_time('mysql'),
            ));

            if ($referenceOk) {
                return array(
                    'status' => true,
                    'message' => __('The message was sent. You will be able to see any reply in your inbox page!', 'wordland'),
                );
            }
        }
    }

    public function send_message()
    {
        $request_via_js = $this->check_request_via_js();
        $response = array(
            'status' => false,
            'message' => __('We could not understand your action', 'wordland'),
        );
        if ($this->validate_data_before_send()) {
            if (boolval(array_get($_POST, 'send-email'))) {
                $response = $this->send_email_message();
            }
            if (boolval(array_get($_POST, 'send-dm'))) {
                $response = $this->send_prive_message();
            }
        } else {
            $response = array(
                'status' => false,
                'message' => array_get(array_values($this->messages), 0),
            );
        }
        if ($request_via_js) {
            wp_send_json($response);
            exit();
        }

        $_SESSION['agent_messages'] = array();
        foreach ($this->messages as $id => $message) {
            $_SESSION['agent_messages'][$id] = array(
                'status' => false,
                'message' => $message,
            );
        }

        wp_safe_redirect(array_get($_SERVER, 'HTTP_REFERER', site_url()));
    }
}
