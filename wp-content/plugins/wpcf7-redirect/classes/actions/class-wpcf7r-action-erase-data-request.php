<?php

/**
 * Class WPCF7R_Action_erasedatarequest file.
 */
if (!defined('ABSPATH')) {
    exit;
}

register_wpcf7r_actions(
    'erasedatarequest',
    __('Erase/Export Data Request', 'wpcf7-redirect'),
    'WPCF7R_Action_erasedata',
    6
);

/**
 * Class WPCF7R_Action_erasedatarequest
 * A Class that handles redirect actions
 */
class WPCF7R_Action_erasedatarequest extends WPCF7R_Action
{

    /**
     * Init the parent action class
     *
     * @param $post
     */
    public function __construct($post)
    {
        parent::__construct($post);
    }

    /**
     * Get the action admin fields
     */
    public function get_action_fields()
    {

        $parent_fields = parent::get_default_fields();

        $tags = $this->get_mail_tags_array();

        return array_merge(
            array(
                array(
                    'name'        => 'request_type',
                    'type'        => 'select',
                    'label'       => __('Request type', 'wpcf7-redirect'),
                    'placeholder' => __('Request type', 'wpcf7-redirect'),
                    'value'       => $this->get('request_type'),
                    'class'       => '',
                    'required'    => true,
                    'options'     => array(
                        'remove_personal_data' => __('Remove personal data'),
                        'export_personal_data' => __('Export personal data'),
                    ),
                ),
                array(
                    'name'        => 'email_field',
                    'type'        => 'select',
                    'label'       => __('The field that is used for username ([username])', 'wpcf7-redirect'),
                    'placeholder' => __('Username field', 'wpcf7-redirect'),
                    'tooltip'     => __('Add a text field to your form and save it', 'wpcf-redirect'),
                    'footer'      => '<div>' . $this->get_formatted_mail_tags() . '</div>',
                    'value'       => $this->get('email_field'),
                    'class'       => '',
                    'options'     => $tags,
                ),
                array(
                    'name'        => 'email_does_not_exist_message',
                    'type'        => 'text',
                    'label'       => __('Email/Username does not exist error message', 'wpcf7-redirect'),
                    'placeholder' => __('Email/Username does not exist error message', 'wpcf7-redirect'),
                    'footer'      => '<div>' . $this->get_formatted_mail_tags() . '</div>',
                    'value'       => $this->get('email_does_not_exist_message'),
                    'class'       => '',
                    'options'     => $tags,
                ),
                
                array(
                    'name'          => 'send_confirmation_email',
                    'type'          => 'checkbox',
                    'label'         => __('Send confirmation email', 'wpcf7-redirect'),
                    'sub_title'     => '',
                    'placeholder'   => '',
                    'value'         => $this->get('send_confirmation_email'),
                ),
            ),
            $parent_fields
        );
    }

    /**
     * Get an HTML of the
     */
    public function get_action_settings()
    {
        $this->get_settings_template('html-action-redirect.php');
    }

    /**
     * Handle a simple redirect rule
     *
     * @param $submission
     */
    public function process($submission)
    {
        $response = array();

        $this->posted_data = $submission->get_posted_data();

        $action_type               = $this->get('request_type');
        $status                    = 'pending';

        if (!$this->get('send_confirmation_email')) {
            $status = 'confirmed';
        }

        $email_address = $this->get_user_email_address();

        $request_id = wp_create_user_request( $email_address , $action_type, array(), $status);
        $message    = '';

        if (is_wp_error($request_id)) {
            $message = $request_id->get_error_message();
        } elseif (!$request_id) {
            $message = __('Unable to initiate confirmation request.');
        }

        if ($message) {
            $response = new WP_Error('erase_data_request', $message);
        }

        if(!$response){
            if ('pending' === $status) {
                wp_send_user_request($request_id);
    
                $response = __('Confirmation request initiated successfully.');
            } elseif ('confirmed' === $status) {
                $response = __('Request added successfully.');
            }
        }

        return $response;
    }

    /**
     * Get the user email if the user exists
     *
     * @return void
     */
    private function get_user_email_address(){
        $username_or_email_field   = $this->get('email_field');

        $username_or_email_address = $this->get_submitted_value($username_or_email_field);

        if (!is_email($username_or_email_address)) {
            $user = get_user_by('login', $username_or_email_address);
            if ($user instanceof WP_User) {
                $email_address = $user->user_email;
            }
        } else {
            $email_address = $username_or_email_address;
        }

        return $email_address;
    }

    public function process_validation($submission)
    {

        $this->posted_data = $submission->get_posted_data();

        $username_or_email_field   = $this->get('email_field');

        $message                   = null;

        $email_address = $this->get_user_email_address();

        if (empty($email_address)) {
            $email_does_not_exists_message = $this->get('email_does_not_exist_message', __('Unable to add this request. A valid email address or username must be supplied'));

            $email_does_not_exists_message = $this->replace_tags( $email_does_not_exists_message );

            $message = new WP_Error('erase_data_request', $email_does_not_exists_message);

            /**
             * Get the tags that are used to send the username/email
             * @var [type]
             */
            $login_field_tag = $this->get_validation_mail_tags($username_or_email_field);

            $error = array(
                'tag'           => $login_field_tag,
                'error_message' => $message->get_error_message(),
            );

            $results['invalid_tags'][] = new WP_error('tag_invalid', $error);
        }

        return $results;
    }
}
