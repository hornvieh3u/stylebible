<?php

/**
* The last woocommerce_add_to_cart hook has priority of 20 so we need to make sure we
* we fire after it. The higher the number, the later the function executes.
*/

add_action('woocommerce_add_to_cart', 'kl_added_to_cart_event', 25, 3);

/**
 * If the param is an instance of a WP_Error, returns
 * an empty array. If the param is not a WP_Error then
 * runs strip_tags and explode to return an array of strings.
 *
 * @param string $list String of product terms.
 * @return array
 */
function kl_strip_explode($list)
{
    if ($list instanceof WP_Error) {
        return [];
    }
    return explode(', ', strip_tags($list));
}

/**
 * Creates a Request argument that can be used within
 * wp_remote_post method for added to cart event
 *
 * @param array $payload of the added to cart event.
 * @return array
 */
function kl_added_to_cart_options($payload)
{
    return array(
        'blocking' => false,
        'headers' => array(
            'Content-Type' => 'application/json'
        ),
        'body' => wp_json_encode($payload)
    );
}

/**
 * Set wck_cart data then build the Added Item and return the array
 * of the full cart data.
 *
 * @param object $added_product Added product data.
 * @param object $cart Cart data.
 * @return array
 */
function kl_build_add_to_cart_data($added_product, $quantity, $cart)
{
    $wck_cart = wck_build_cart_data($cart);
    $added_product_id = $added_product->get_id();

    return array(
        '$value' => (float) $cart->total,
        'AddedItemCategories' => (array) kl_strip_explode(wc_get_product_category_list($added_product_id)),
        'AddedItemImageURL' => (string) wp_get_attachment_url(get_post_thumbnail_id($added_product_id)),
        'AddedItemPrice' => (float) $added_product->get_price(),
        'AddedItemQuantity' => (int) $quantity,
        'AddedItemProductID' => (int) $added_product_id,
        'AddedItemProductName' => (string) $added_product->get_name(),
        'AddedItemSKU' => (string) $added_product->get_sku(),
        'AddedItemTags' => (array) kl_strip_explode(wc_get_product_tag_list($added_product_id)),
        'AddedItemURL' => (string) $added_product->get_permalink(),
        'ItemNames' => (array) $wck_cart['ItemNames'],
        'Categories' => isset($wck_cart['Categories']) ? (array) $wck_cart['Categories'] : [],
        'ItemCount' => (int) $wck_cart['Quantity'],
        'Tags' =>  isset($wck_cart['Tags']) ? (array) $wck_cart['Tags'] : [],
        '$extra' => $wck_cart['$extra']
    );
}

/**
 * Check that the Public API token is set, build Added to Cart event payload,
 * create an options request array using kl_added_to_cart_options function and
 * send the request.
 *
 * @param array $customer_identify Identifies the customer based on email or exchange_id.
 * @param array $data Cart and AddedItem data.
 * @returns null
 */
function kl_track_request($customer_identify, $data)
{
    $public_api_key = WCK()->options->get_klaviyo_option('klaviyo_public_api_key');
    if (! $public_api_key) {
        return;
    }

    $atc_data = array(
        'token' => $public_api_key,
        'event' => 'Added to Cart',
        'customer_properties' => $customer_identify,
        'properties' => $data
    );

    $url = "https://a.klaviyo.com/api/track";
    $options = kl_added_to_cart_options($atc_data);

    wp_remote_post($url, $options);
}

/**
 * Set customer identity, call kl_build_add_to_cart_data and then call kl_track_request
 * to trigger the event.
 *
 * @param string $cart_item_key Unique key for item in cart.
 * @param int $product_id ID of item added to cart.
 * @param int $quantity Quantity of item added to cart.
 * @returns null
 */
function kl_added_to_cart_event($cart_item_key, $product_id, $quantity)
{
    if (! isset($_COOKIE['__kla_id'])) {
        return;
    }
    $kl_cookie = $_COOKIE['__kla_id'];
    $kl_decoded_cookie = json_decode(base64_decode($kl_cookie), true);
    if (isset($kl_decoded_cookie['$exchange_id'])) {
        $customer_identify = array('$exchange_id' => $kl_decoded_cookie['$exchange_id']);
    } elseif (isset($kl_decoded_cookie['$email'])) {
        $customer_identify = array('$email' => $kl_decoded_cookie['$email']);
    } else {
        return;
    }

    $added_product = wc_get_product($product_id);
    if (! $added_product instanceof WC_Product) {
        return;
    }

    kl_track_request($customer_identify, kl_build_add_to_cart_data($added_product, $quantity, WC()->cart));
}
