<?php

/**
 * WooCommerceKlaviyo API
 *
 * Handles WCK-API endpoint requests
 *
 * @author      Klaviyo
 * @category    API
 * @package     WooCommerceKlaviyo/API
 * @since       2.0
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WCK_API
{
    const VERSION = '3.0.7';
    const KLAVIYO_BASE_URL = 'klaviyo/v1';
    const ORDERS_ENDPOINT = 'orders';
    const EXTENSION_VERSION_ENDPOINT = 'version';
    const PRODUCTS_ENDPOINT = 'products';
    const OPTIONS_ENDPOINT = 'options';
    const DISABLE_ENDPOINT = 'disable';

    // API RESPONSES
    const API_RESPONSE_CODE = 'status_code';
    const API_RESPONSE_ERROR = 'error';
    const API_RESPONSE_REASON = 'reason';
    const API_RESPONSE_SUCCESS = 'success';

    // HTTP CODES
    const STATUS_CODE_HTTP_OK = 200;
    const STATUS_CODE_NO_CONTENT = 204;
    const STATUS_CODE_BAD_REQUEST = 400;
    const STATUS_CODE_AUTHENTICATION_ERROR = 401;
    const STATUS_CODE_AUTHORIZATION_ERROR = 403;
    const STATUS_CODE_INTERNAL_SERVER_ERROR = 500;

    const DEFAULT_RECORDS_PER_PAGE = '50';
    const DATE_MODIFIED = 'post_modified_gmt';
    const POST_STATUS_ANY = 'any';

    const ERROR_KEYS_NOT_PASSED = 'consumer key or consumer secret not passed';
    const ERROR_CONSUMER_KEY_NOT_FOUND = 'consumer_key not found';

    const PERMISSION_READ = 'read';
    const PERMISSION_WRITE = 'write';
    const PERMISSION_READ_WRITE = 'read_write';
    const PERMISSION_METHOD_MAP = array(
        self::PERMISSION_READ => array( 'GET' ),
        self::PERMISSION_WRITE => array( 'POST' ),
        self::PERMISSION_READ_WRITE => array( 'GET', 'POST' ),
    );

    /**
     * Check if there is a new version of the Klaviyo plugin available for download. WordPress stores this info in the
     * database as an object with the following properties:
     *   - last_checked (int) Unix timestamp when request was last made to Wordpress server.
     *   - response (array) Contains plugin data with updates available stored by key e.g.'klaviyo/klaviyo.php'.
     *   - no_update (array) Contains plugin data for plugins without updates stored by key e.g. 'klaviyo/klaviyo.php'.
     *   - translations (array) Not relevant to us here.
     *
     * The response and no_update arrays are mutually exclusive so we can see if Klaviyo's plugin has been checked for
     * and if it's in the response array.
     *
     * See wp_update_plugins function in `wordpress/wp-includes/update.php` for more information on how this is set.
     *
     * @param stdClass $plugins_transient Optional arg if the transient value is already in scope e.g. during update check.
     * @return bool
     */
    public static function is_most_recent_version(stdClass $plugins_transient = null)
    {
        if (! $plugins_transient) {
            $plugins_transient = get_site_transient('update_plugins');
        }
        // True if response property isn't set, we don't want to alert on a false positive here.
        if (! isset($plugins_transient->response)) {
            return true;
        }
        // True if Klaviyo plugin is not in the response array meaning no update available.
        return ! array_key_exists(KLAVIYO_BASENAME, $plugins_transient->response);
    }

    /**
     * Build payload for version endpoint and webhooks.
     *
     * @param bool $is_updating Short circuit checking version if plugin is being updated, we know it's most recent.
     * @return array
     */
    public static function build_version_payload($is_updating = false)
    {
        return array(
            'plugin_version' => self::VERSION,
            'is_most_recent_version' => $is_updating ?: self::is_most_recent_version(),
        );
    }
}

function count_loop(WP_Query $loop)
{
    $loop_ids = array();
    while ($loop->have_posts()) {
        $loop->the_post();
        $loop_id = get_the_ID();
        array_push($loop_ids, $loop_id);
    }
    return $loop_ids;
}

function validate_request($request)
{
    $consumer_key = $request->get_param('consumer_key');
    $consumer_secret = $request->get_param('consumer_secret');
    if (empty($consumer_key) || empty($consumer_secret)) {
        return validation_response(
            true,
            WCK_API::STATUS_CODE_BAD_REQUEST,
            WCK_API::ERROR_KEYS_NOT_PASSED,
            false
        );
    }

    global $wpdb;
    // this is stored as a hash so we need to query on the hash
    $key = hash_hmac('sha256', $consumer_key, 'wc-api');
    $user = $wpdb->get_row(
        $wpdb->prepare(
            "
    SELECT consumer_key, consumer_secret
    FROM {$wpdb->prefix}woocommerce_api_keys
    WHERE consumer_key = %s
     ",
            $key
        )
    );

    if ($user->consumer_secret == $consumer_secret) {
        return validation_response(
            false,
            WCK_API::STATUS_CODE_HTTP_OK,
            null,
            true
        );
    }
    return validation_response(
        true,
        WCK_API::STATUS_CODE_AUTHORIZATION_ERROR,
        WCK_API::ERROR_CONSUMER_KEY_NOT_FOUND,
        false
    );
}

/**
 * Validate incoming requests to custom endpoints.
 *
 * @param WP_REST_Request $request Incoming request object.
 * @return bool|WP_Error True if validation succeeds, otherwise WP_Error to be handled by rest server.
 */
function validate_request_v2(WP_REST_Request $request)
{
    $consumer_key = $request->get_param('consumer_key');
    $consumer_secret = $request->get_param('consumer_secret');
    if (empty($consumer_key) || empty($consumer_secret)) {
        return new WP_Error(
            'klaviyo_missing_key_secret',
            'One or more of consumer key and secret are missing.',
            array( 'status' => WCK_API::STATUS_CODE_AUTHENTICATION_ERROR )
        );
    }

    global $wpdb;
    // this is stored as a hash so we need to query on the hash
    $key = hash_hmac('sha256', $consumer_key, 'wc-api');
    $user = $wpdb->get_row(
        $wpdb->prepare(
            "
    SELECT consumer_key, consumer_secret, permissions
    FROM {$wpdb->prefix}woocommerce_api_keys
    WHERE consumer_key = %s
     ",
            $key
        )
    );
    // User query lookup on consumer key can return null or false.
    if (! $user) {
        return new WP_Error(
            'klaviyo_cannot_authentication',
            'Cannot authenticate with provided credentials.',
            array( 'status' => 401 )
        );
    }
    // User does not have proper permissions.
    if (! in_array($request->get_method(), WCK_API::PERMISSION_METHOD_MAP[ $user->permissions ])) {
        return new WP_Error(
            'klaviyo_improper_permissions',
            'Improper permissions to access this resource.',
            array( 'status' => WCK_API::STATUS_CODE_AUTHORIZATION_ERROR )
        );
    }
    // Success!
    if ($user->consumer_secret == $consumer_secret) {
        return true;
    }
    // Consumer secret didn't match or some other issue authenticating.
    return new WP_Error(
        'klaviyo_invalid_authentication',
        'Invalid authentication.',
        array( 'status' => WCK_API::STATUS_CODE_AUTHENTICATION_ERROR )
    );
}

function validation_response($error, $code, $reason, $success)
{
    return array(
        WCK_API::API_RESPONSE_ERROR => $error,
        WCK_API::API_RESPONSE_CODE => $code,
        WCK_API::API_RESPONSE_REASON => $reason,
        WCK_API::API_RESPONSE_SUCCESS => $success,
    );
}

function process_resource_args($request, $post_type)
{
    $page_limit = $request->get_param('page_limit');
    if (empty($page_limit)) {
        $page_limit = WCK_API::DEFAULT_RECORDS_PER_PAGE;
    }
    $date_modified_after = $request->get_param('date_modified_after');
    $date_modified_before = $request->get_param('date_modified_before');
    $page = $request->get_param('page');

    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => $page_limit,
        'post_status' => WCK_API::POST_STATUS_ANY,
        'paged' => $page,
        'date_query' => array(
            array(
                'column' => WCK_API::DATE_MODIFIED,
                'after' => $date_modified_after,
                'before' => $date_modified_before
            )
        ),
    );
    return $args;
}

function get_orders_count(WP_REST_Request $request)
{
    $validated_request = validate_request($request);
    if ($validated_request['error'] === true) {
        return $validated_request;
    }

    $args = process_resource_args($request, 'shop_order');

    $loop = new WP_Query($args);
    $data = count_loop($loop);
    return array('order_count' => $loop->found_posts);
}

function get_products_count(WP_REST_Request $request)
{
    $validated_request = validate_request($request);
    if ($validated_request['error'] === true) {
        return $validated_request;
    }

    $args = process_resource_args($request, 'product');
    $loop = new WP_Query($args);
    $data = count_loop($loop);
    return array('product_count' => $loop->found_posts);
}

function get_products(WP_REST_Request $request)
{
    $validated_request = validate_request($request);
    if ($validated_request['error'] === true) {
        return $validated_request;
    }

    $args = process_resource_args($request, 'product');

    $loop = new WP_Query($args);
    $data = count_loop($loop);
    return array('product_ids' => $data);
}

function get_orders(WP_REST_Request $request)
{
    $validated_request = validate_request($request);
    if ($validated_request['error'] === true) {
        return $validated_request;
    }

    $args = process_resource_args($request, 'shop_order');

    $loop = new WP_Query($args);
    $data = count_loop($loop);
    return array('order_ids' => $data);
}

/**
 * Handle GET request to /klaviyo/v1/version. Returns the current version and if
 * the installed version is the most recent available in the plugin directory.
 *
 * @return array
 */
function get_extension_version()
{
    return WCK_API::build_version_payload();
}

/**
 * Handle POST request to /klaviyo/v1/options and update plugin options.
 *
 * @param WP_REST_Request $request
 * @return bool|mixed|void|WP_Error
 */
function update_options(WP_REST_Request $request)
{
    $body = json_decode($request->get_body(), $assoc = true);
    if (! $body) {
        return new WP_Error(
            'klaviyo_empty_body',
            'Body of request cannot be empty.',
            array( 'status' => 400 )
        );
    }

    $options = get_option('klaviyo_settings');
    if (! $options) {
        $options = array();
    }

    $updated_options = array_replace($options, $body);
    $is_update = (bool) array_diff_assoc($options, $updated_options);
    // If there is no change between existing and new settings `update_option` returns false. Want to distinguish
    // between that scenario and an actual problem when updating the plugin options.
    if (! update_option('klaviyo_settings', $updated_options) && $is_update) {
        return new WP_Error(
            'klaviyo_update_failed',
            'Options update failed.',
            array(
                'status' => WCK_API::STATUS_CODE_INTERNAL_SERVER_ERROR,
                'options' => get_option('klaviyo_settings')
            )
        );
    }

    // Return plugin version info so this can be saved in Klaviyo when setting up integration for the first time.
    return array_merge($updated_options, WCK_API::build_version_payload());
}

/**
 * Handle GET request to /klaviyo/v1/options and return options set for plugin.
 *
 * @return array Klaviyo plugin options.
 */
function get_options()
{
    return get_option('klaviyo_settings');
}

/**
 * Handle POST request to /klaviyo/v1/disable by deactivating the plugin.
 *
 * @param WP_REST_Request $request Incoming request object.
 * @return WP_Error|WP_REST_Response
 */
function wck_disable_plugin(WP_REST_Request $request)
{
    $body = json_decode($request->get_body(), $assoc = true);
    // Verify body contains required data.
    if (! isset($body['klaviyo_public_api_key'])) {
        return new WP_Error(
            'klaviyo_disable_failed',
            'Disable plugin failed, \'klaviyo_public_api_key\' missing from body.',
            array( 'status' => WCK_API::STATUS_CODE_BAD_REQUEST )
        );
    }
    // Verify keys match if set in WordPress options table
    $public_api_key = WCK()->options->get_klaviyo_option('klaviyo_public_api_key');
    if ($public_api_key && $body['klaviyo_public_api_key'] !== $public_api_key) {
        return new WP_Error(
            'klaviyo_disable_failed',
            'Disable plugin failed, \'klaviyo_public_api_key\' does not match key set in WP options.',
            array( 'status' => WCK_API::STATUS_CODE_BAD_REQUEST )
        );
    }

    WCK()->installer->deactivate_klaviyo();
    return new WP_REST_Response(null, WCK_API::STATUS_CODE_NO_CONTENT);
}

add_action('rest_api_init', function () {
    register_rest_route(WCK_API::KLAVIYO_BASE_URL, WCK_API::EXTENSION_VERSION_ENDPOINT, array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_extension_version',
        'permission_callback' => '__return_true',
    ));
    register_rest_route(WCK_API::KLAVIYO_BASE_URL, 'orders/count', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_orders_count',
        'permission_callback' => '__return_true',
    ));
    register_rest_route(WCK_API::KLAVIYO_BASE_URL, 'products/count', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_products_count',
        'permission_callback' => '__return_true',
    ));
    register_rest_route(WCK_API::KLAVIYO_BASE_URL, WCK_API::ORDERS_ENDPOINT, array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_orders',
        'args' => array(
            'id' => array(
                'validate_callback' => 'is_numeric'
            ),
        ),
        'permission_callback' => '__return_true',
    ));
    register_rest_route(WCK_API::KLAVIYO_BASE_URL, WCK_API::PRODUCTS_ENDPOINT, array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_products',
        'args' => array(
            'id' => array(
                'validate_callback' => 'is_numeric'
            ),
        ),
        'permission_callback' => '__return_true',
    ));
    register_rest_route(WCK_API::KLAVIYO_BASE_URL, WCK_API::OPTIONS_ENDPOINT, array(
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'update_options',
            'permission_callback' => 'validate_request_v2',
        ),
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'get_options',
            'permission_callback' => 'validate_request_v2',
        )
    ));
    register_rest_route(WCK_API::KLAVIYO_BASE_URL, WCK_API::DISABLE_ENDPOINT, array(
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'wck_disable_plugin',
            'permission_callback' => 'validate_request_v2',
        )
    ));
});

/**
 * Check if there is a new version of the Klaviyo plugin. Return early if we've already identified that we are out of
 * date. This is stored in a transient that does not expire. This transient is created here the first time we identify
 * the plugin is out of date and send that information to Klaviyo. It's deleted when someone updates the Klaviyo plugin.
 *
 * @param $transient_value stdClass The transient value to be saved, this is an object with various lists of plugins and their data.
 */
function klaviyo_check_for_plugin_update(stdClass $transient_value)
{
    // If we're up to date or we've already sent this information along just return early.
    if (WCK_API::is_most_recent_version($transient_value) || get_site_transient('is_klaviyo_plugin_outdated')) {
        return;
    }

    // Send options payload to Klaviyo.
    WCK()->webhook_service->send_options_webhook();

    // Set site transient so we don't keep making requests
    set_site_transient('is_klaviyo_plugin_outdated', 1);
}

/**
 * This fires when the 'update_plugins' transient is updated which occurs when WordPress polls the plugin directory api
 * to check for plugin updates. The wp_update_plugins cron runs every 12 hours but requires a pageload for the cron
 * check to fire. More information at: https://developer.wordpress.org/plugins/cron/ and https://developer.wordpress.org/reference/functions/wp_update_plugins/
 *
 * We hook into this to see if there's a new version of the Klaviyo plugin available, if
 * so we want to send this information to the corresponding Klaviyo account. Only do so
 * if we haven't already sent this information to Klaviyo.
 */
if (! get_site_transient('is_klaviyo_plugin_outdated')) {
    add_action('set_site_transient_update_plugins', 'klaviyo_check_for_plugin_update');
}
