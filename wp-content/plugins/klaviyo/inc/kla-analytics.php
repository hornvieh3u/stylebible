<?php

class WPKlaviyoAnalytics
{
    const HIGHEST_FILTER_PRIORITY = 0;
    const DEFAULT_FILTER_PRIORITY = 10;
    const FILTER_THREE_ARGUMENTS = 3;
    const KLAVIYO_JS_HANDLE = 'klaviyojs';

    function __construct($klaviyo_public_key)
    {
        $this->klaviyo_public_key = $klaviyo_public_key;

        // Add analytics
        add_action(
            'wp_enqueue_scripts',
            array( $this, 'insert_analytics' ),
            self::HIGHEST_FILTER_PRIORITY
        );
        // Add js to identify user if commenter or logged-in. Priority 11 to add before Viewed Product.
        add_action(
            'wp_enqueue_scripts',
            array( $this, 'identify_browser' ),
            11
        );
    }

    /**
     * Check WooCommerce plugin status, and run is_checkout() function
     */
    function is_woocommerce_checkout_page()
    {
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            return is_checkout();
        }
    }

    /**
     * Add klaviyo.js as external resource if public API key is set.
     */
    function insert_analytics()
    {
        if ($this->klaviyo_public_key == '' || $this->is_woocommerce_checkout_page()) {
            return;
        }

        $klaviyo_js_source = "//static.klaviyo.com/onsite/js/klaviyo.js?company_id=" . $this->klaviyo_public_key;
        wp_enqueue_script(self::KLAVIYO_JS_HANDLE, $klaviyo_js_source, null, null, true);
        add_filter(
            'script_loader_tag',
            array( &$this, 'kl_add_async' ),
            self::DEFAULT_FILTER_PRIORITY,
            self::FILTER_THREE_ARGUMENTS
        );
    }

    /**
     * Filter to add async attribute to script tag.
     * Currently only used on enqueue script handle "klaviyojs".
     *
     * @param string $tag     filter hook
     * @param string $handle  tag of enqueue script
     * @param string $src     source of script to be enqueued
     *
     * @return string         if script handle is for installing klaviyo.js return script element
     *                        with source set to $src and async attribute else return filter hook
     */
    function kl_add_async($tag, $handle, $src)
    {
        if ($handle !== self::KLAVIYO_JS_HANDLE) {
            return $tag;
        }

        return "<script async src='" . $src . "'></script>";
    }

    /**
     * Get logged in user and commenter and make available to kl-identify-browser.js
     */
    function identify_browser()
    {
        global $current_user;

        $commenter = wp_get_current_commenter();
        $commenter_email = ! empty($commenter['comment_author_email']) ? $commenter['comment_author_email'] : '';

        wp_enqueue_script(
            'kl-identify-browser',
            plugins_url('/js/kl-identify-browser.js', __FILE__),
            array( self::KLAVIYO_JS_HANDLE ),
            null,
            true
        );

        $kl_user = array(
            'current_user_email' => $current_user->user_email,
            'commenter_email' => $commenter_email,
        );

        wp_localize_script('kl-identify-browser', 'klUser', $kl_user);
    }
}
