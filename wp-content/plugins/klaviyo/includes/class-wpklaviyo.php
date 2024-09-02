<?php

/**
 * WPKlaviyo Helper Class
 */

class WPKlaviyo
{
    public static function is_connected($public_api_key = '')
    {
        if (trim($public_api_key) != '') {
            return true;
        } else {
            $klaviyo_settings = get_option('klaviyo_settings');
            if (
                isset($klaviyo_settings['klaviyo_public_api_key'])
                && trim($klaviyo_settings['klaviyo_public_api_key']) != ''
            ) {
                return true;
            }

            return false;
        }
    }

    function __construct()
    {
        global $klaviyowp_analytics;

        if (!is_admin()) {
            $klaviyowp_analytics = new WPKlaviyoAnalytics(WCK()->options->get_klaviyo_option('klaviyo_public_api_key'));
        }

        // Display config message.
        $klaviyowp_message = new WPKlaviyoNotification();
        add_action('admin_notices', array(&$klaviyowp_message, 'config_warning'));

        add_action('widgets_init', function () {
            register_widget("Klaviyo_EmailSignUp_Widget");
            // Only display Built-in Signup Form widget if klaviyo.js is checked in settings
            if (WCK()->options->get_klaviyo_option('klaviyo_popup')) {
                register_widget("Klaviyo_EmbedEmailSignUp_Widget");
            }
        });
    }

    function add_defaults()
    {
        $klaviyo_settings = get_option('klaviyo_settings');

        if (($klaviyo_settings['installed'] != 'true') || !is_array($klaviyo_settings)) {
            $klaviyo_settings = array(
                'installed' => 'true',
                'klaviyo_public_api_key' => '',
                'klaviyo_newsletter_list_id' => '',
                'klaviyo_newsletter_text' => '',
            );
            update_option('klaviyo_settings', $klaviyo_settings);
        }
    }

    function format_text($content, $br = true)
    {
        return $content;
    }
}
