<?php

/**
 * WooCommerceKlaviyo Order Functions
 *
 * Functions for order specific things.
 *
 * @author    Klaviyo
 * @category  Core
 * @package   WooCommerceKlaviyo/Functions
 * @version   2.0.0
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function add_composite_products_cart($composite_products)
{
    foreach ($composite_products as $product) {
        $container = array();
        foreach ($product as $i => $v) {
            $item = $v['item'];
            $container_id = $item['container_id'];
            if (isset($item['attributes'])) {
                $container[$container_id] = array(
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'variation_id' => $item['variation_id'],
                    'attributes' => $item['attributes'],
                );
            } else {
                $container[$container_id] = array(
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                );
            }
        }
        $added = WC_CP()->cart->add_composite_to_cart($v['composite_id'], $v['composite_quantity'], $container);
    }
}

function get_email($current_user)
{
    $email = '';
    if ($current_user->user_email) {
        $email = $current_user->user_email;
    } else {
        // See if current user is a commenter
        $commenter = wp_get_current_commenter();
        if ($commenter['comment_author_email']) {
            $email = $commenter['comment_author_email'];
        }
    }
    return $email;
}

function wck_rebuild_cart()
{

    // Exit if in back-end
    if (is_admin()) {
        return;
    }
    global $woocommerce;

    // Exit if not on cart page or no wck_rebuild_cart parameter
    $current_url = build_current_url();
    $utm_wck_rebuild_cart = isset($_GET['wck_rebuild_cart']) ? $_GET['wck_rebuild_cart'] : '';
    if ($current_url[0] !== wc_get_cart_url() || $utm_wck_rebuild_cart === '') {
        return;
    }

    // Rebuild cart
    $woocommerce->cart->empty_cart(true);
    $woocommerce->cart->get_cart();

    $kl_cart = json_decode(base64_decode($utm_wck_rebuild_cart), true);
    $composite_products = $kl_cart['composite'];
    $normal_products = $kl_cart['normal_products'];

    foreach ($normal_products as $product) {
        $cart_key = $woocommerce->cart->add_to_cart($product['product_id'], $product['quantity'], $product['variation_id'], $product['variation']);
    }

    if (class_exists('WC_Composite_Products')) {
        add_composite_products_cart($composite_products);
    }

    $carturl = wc_get_cart_url();
    if ($current_url[0] == wc_get_cart_url()) {
        header("Refresh:0; url=" . $carturl);
    }
}

function build_current_url()
{
    $server_protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
    $server_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $server_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

    return explode('?', $server_protocol . '://' . $server_host . $server_uri);
}

/**
 * Insert tracking code code for tracking started checkout.
 *
 * @access public
 * @return void
 */
function wck_started_checkout_tracking()
{
    global $current_user;
    wp_reset_query();
    wp_get_current_user();
    $cart = WC()->cart;
    $event_data = wck_build_cart_data($cart);
    if (empty($event_data['$extra']['Items'])) {
        return;
    }
    $event_data['$service'] = 'woocommerce';
    // Remove top level properties to maintain consistent Started Checkout event data in 2.5.0
    unset($event_data['Tags']);
    unset($event_data['Quantity']);
    $email = get_email($current_user);
    $started_checkout_data = array(
        'email' => $email,
        'event_data' => $event_data
    );
    // Pass Started Checkout event data to javascript attaching to 'wck_started_checkout' handle
    wp_localize_script('wck_started_checkout', 'kl_checkout', $started_checkout_data);
}

// Load javascript file for Started Checkout events
add_action('wp_enqueue_scripts', 'load_started_checkout');


/**
 *  Check if page is a checkout page, if so load the Started Checkout javascript file.
 *
 */
function load_started_checkout()
{
    if (is_checkout()) {
        $token = WCK()->options->get_klaviyo_option('klaviyo_public_api_key');

        wp_enqueue_script('wck_started_checkout', plugins_url('/js/wck-started-checkout.js', __FILE__), null, null, true);
        wp_localize_script('wck_started_checkout', 'public_key', array( 'token' => $token ));

        // Build started checkout event data and add inline script to html.
        wck_started_checkout_tracking();
    }
}

add_action('wp_loaded', 'wck_rebuild_cart');

/**
 * Add checkbox to subscribe profiles to list during checkout.
 *
 * @param $fields
 * @return array $fields
 */
function kl_checkbox_custom_checkout_field($fields)
{
    $klaviyo_settings = get_option('klaviyo_settings');
    $fields['billing']['kl_newsletter_checkbox'] = array(
        'type' => 'checkbox',
        'class' => array('kl_newsletter_checkbox_field'),
        'label' => $klaviyo_settings['klaviyo_newsletter_text'],
        'value'  => true,
        'default' => 0,
        'required'  => false,
    );

    return $fields;
}

function kl_sms_consent_checkout_field($fields)
{
    $klaviyo_settings = get_option('klaviyo_settings');
    $fields['billing']['kl_sms_consent_checkbox'] = array(
        'type' => 'checkbox',
        'class' => array( 'kl_sms_consent_checkbox_field' ),
        'label' => $klaviyo_settings['klaviyo_sms_consent_text'],
        'value'  => true,
        'default' => 0,
        'required'  => false,
    );

    return $fields;
}

function kl_sms_compliance_text()
{
    $klaviyo_settings = get_option('klaviyo_settings');
    echo $klaviyo_settings['klaviyo_sms_consent_disclosure_text'];
}

function kl_add_to_list()
{
    $klaviyo_settings = get_option('klaviyo_settings');
    $email = $_POST['billing_email'];
    $phone = $_POST['billing_phone'];
    $country = $_POST['billing_country'];
    $url = 'https://a.klaviyo.com/api/webhook/integration/woocommerce?c=' . $klaviyo_settings['klaviyo_public_api_key'];
    $body = array(
        'data' => array(),
    );

    if (isset($_POST['kl_sms_consent_checkbox']) && $_POST['kl_sms_consent_checkbox']) {
        array_push($body['data'], array(
            'customer' => array(
                'email' => $email,
                'country' => $country,
                'phone' => $phone,
            ),
            'consent' => true,
            'updated_at' => gmdate(DATE_ATOM, date_timestamp_get(date_create())),
            'consent_type' => 'sms',
            'group_id' => $klaviyo_settings['klaviyo_sms_list_id'],
        ));
    }

    if (isset($_POST['kl_newsletter_checkbox']) && $_POST['kl_newsletter_checkbox']) {
        array_push($body['data'], array(
            'customer' => array(
                'email' => $email,
                'phone' => $phone,
            ),
            'consent' => true,
            'updated_at' => gmdate(DATE_ATOM, date_timestamp_get(date_create())),
            'consent_type' => 'email',
            'group_id' => $klaviyo_settings['klaviyo_newsletter_list_id'],
        ));
    }

    wp_remote_post($url, array(
            'method' => 'POST',
            'httpversion' => '1.0',
            'blocking' => false,
            'headers' => array(
                'X-WC-Webhook-Topic' => 'custom/consent',
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($body),
            'data_format' => 'body',
        ));
}

$klaviyo_settings = get_option('klaviyo_settings');
if (
    isset($klaviyo_settings['klaviyo_subscribe_checkbox'])
    && $klaviyo_settings['klaviyo_subscribe_checkbox']
    && !empty($klaviyo_settings['klaviyo_newsletter_list_id'])
) {
    // Add the checkbox field
    add_filter('woocommerce_checkout_fields', 'kl_checkbox_custom_checkout_field', 11);

    // Post list request to Klaviyo
    add_action('woocommerce_checkout_update_order_meta', 'kl_add_to_list');
}

if (
    isset($klaviyo_settings['klaviyo_sms_subscribe_checkbox'])
    && $klaviyo_settings['klaviyo_sms_subscribe_checkbox']
    && !empty($klaviyo_settings['klaviyo_sms_list_id'])
) {
    // Add the checkbox field
    add_filter('woocommerce_checkout_fields', 'kl_sms_consent_checkout_field', 11);

    // Add data compliance messaging to checkout page
    add_filter('woocommerce_after_checkout_billing_form', 'kl_sms_compliance_text');

    // Post SMS request to Klaviyo
    add_action('woocommerce_checkout_update_order_meta', 'kl_add_to_list');
}
