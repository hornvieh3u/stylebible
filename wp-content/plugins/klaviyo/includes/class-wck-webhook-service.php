<?php

/**
 * WooCommerceKlaviyo Webhook Service
 *
 * Handles outgoing requests to Klaviyo's webhook endpoint.
 *
 * @author      Klaviyo
 * @category    Webhook
 * @package     WooCommerceKlaviyo/Webhook
 * @since       2.6
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class WCK_Webhook_Service
 *
 * Handle sending data to Klaviyo's webhook endpoint synchronously.
 */
class WCK_Webhook_Service
{
    const WEBHOOK_URL = 'https://a.klaviyo.com/api/webhook/integration/woocommerce?c=';

    const TOPIC_RESOURCE_CUSTOM = 'custom';
    const TOPIC_EVENT_OPTIONS = 'options';
    const TOPIC_EVENT_REMOVE = 'remove';
    const TOPIC_EVENT_VERSION = 'version';

    /**
     * Handle building args, sending request to Klaviyo's webhook url and lightweight error handling.
     *
     * @param string $topic_event Webhook topic event in the pattern 'resource/event'.
     * @param array $data Payload of outgoing request.
     * @return array|void
     */
    private function send_webhook($topic_event, $data)
    {
        $options = get_option('klaviyo_settings');
        if (! isset($options['klaviyo_public_api_key'])) {
            // TODO: It'd be nice to eventually log this failure or notify in the admin.
            return;
        }
        $url = self::WEBHOOK_URL . $options['klaviyo_public_api_key'];

        // Don't set 'blocking' = false, it short circuits response parsing and returns an empty Requests_Response
        // object. For more information see Requests::parse_response() in wordpress/wp-includes/class-requests.php
        $response = wp_remote_post(
            $url,
            array(
                'headers' => array(
                    'X-WC-Webhook-Topic' => self::TOPIC_RESOURCE_CUSTOM . '/' . $topic_event,
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode($data),
            )
        );

        // Klaviyo's webhook endpoints almost always return 200 with a body of "1"/"0" corresponding to success/failure.
        // It's possible to get a 503 response in the case of a larger issue unrelated to content, formatting, etc. or a
        // timeout if the request takes longer than 5 seconds.
        if (is_wp_error($response) || $response['body'] !== '1' || $response['response']['code'] !== 200) {
            // TODO: It'd be nice to eventually log this failure.
            return;
        }

        return $response;
    }

    /**
     * Send webhook with topic 'custom/options'. Data contains all options under 'klaviyo_settings',
     * the plugin version and if it's the most recent plugin version.
     *
     * Set email/sms list ID values to null if no ID set.
     */
    public function send_options_webhook($is_updating = false)
    {
        $data = array_merge(WCK_API::build_version_payload($is_updating), WCK()->options->get_all_options());

        if (! isset($data['klaviyo_sms_list_id']) || $data['klaviyo_sms_list_id'] === '') {
            $data['klaviyo_sms_list_id'] = null;
        }
        if (! isset($data['klaviyo_newsletter_list_id']) || $data['klaviyo_newsletter_list_id'] === '') {
            $data['klaviyo_newsletter_list_id'] = null;
        }

        return $this->send_webhook(self::TOPIC_EVENT_OPTIONS, $data);
    }
}
