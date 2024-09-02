<?php

/**
 * Check if this the user has a premium liscense
 *
 * @param [type] $func
 * @return void
 */
function wpcf7r_is_premium_user($func)
{
    if (function_exists($func) && wpcf7r_is_parent_active_and_loaded()) {
        return $func()->is__premium_only() && $func()->is_paying_or_trial();
    }

    return false;
} 

/**
 * General loading addon function
 *
 * @param [type] $name
 * @return void
 */
function wpcf7r_load_freemius_addon($name)
{
    $callback = $name;
    $loaded_hook = $name . "_loaded";

    if (wpcf7r_is_parent_active_and_loaded()) {
        // If parent already included, init add-on.
        $callback();

        do_action($loaded_hook);
    } else if (wpcf7r_is_parent_active()) {
        // Init add-on only after the parent is loaded.
        add_action('wpcf7_fs_loaded', function () use ($callback) {
            $callback();

            do_action($loaded_hook);
        });
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        $callback();

        do_action($loaded_hook);
    }
}

/**
 * Check if the parent plugins is active and loaded
 *
 * @return void
 */
function wpcf7r_is_parent_active_and_loaded()
{
    return function_exists('wpcf7_fs');
}

/**
 * Check if the parent plugin is active
 *
 * @return void
 */
function wpcf7r_is_parent_active()
{
    $active_plugins = get_option('active_plugins', array());

    if (is_multisite()) {
        $network_active_plugins = get_site_option('active_sitewide_plugins', array());
        $active_plugins = array_merge($active_plugins, array_keys($network_active_plugins));
    }

    foreach ($active_plugins as $basename) {
        if (0 === strpos($basename, 'wpcf7-redirect/') || 0 === strpos($basename, 'wpcf7-redirect-premium/')) {
            return true;
        }
    }
    return false;
}

/**
 * Get the path for the admin page
 *
 * @return void
 */
function get_freemius_addons_path()
{
    return 'admin.php?page=wpc7_redirect-addons';
}

function freemius_get_id()
{
    return 9546;
}

if (!function_exists('wpcf7_fs')) {
    // Create a helper function for easy SDK access.
    function wpcf7_fs()
    {
        global $wpcf7_fs;

        if (!isset($wpcf7_fs)) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $wpcf7_fs = fs_dynamic_init(array(
                'id'                  => freemius_get_id(),
                'slug'                => 'wpcf7-redirect',
                'type'                => 'plugin',
                'public_key'          => 'pk_f6edcea690f1f0ec55e21eff1fd4a',
                'is_premium'          => false,
                'has_addons'          => true,
                'bundle_id'           => '9566',
                'bundle_public_key'   => 'pk_93c540ee75ee6e1f565670f760a12',
                'bundle_license_auto_activation' => true,
                'has_paid_plans'      => true,
                'menu'                => array(
                    'first-path'     => get_freemius_addons_path(),
                    'slug'           => 'wpc7_redirect',
                    'parent'         => array(
                        'slug' => 'wpcf7',
                    ),
                ),
            ));
        }

        return $wpcf7_fs;
    }

    // Init Freemius.
    wpcf7_fs();
    // Signal that SDK was initiated.
    do_action('wpcf7_fs_loaded');

    wpcf7_fs()->add_filter('connect_message_on_update', 'wpcf7_fs_custom_connect_message_on_update', 10, 6);
}

function wpcf7_fs_custom_connect_message_on_update(
    $message,
    $user_first_name,
    $plugin_title,
    $user_login,
    $site_link,
    $freemius_link
) {
    return sprintf(
        __('Hey %1$s') . ',<br>' .
            __('Please help us improve %2$s! If you opt-in, some data about your usage of %2$s will be sent to %5$s. If you skip this, that\'s okay! %2$s will still work just fine.', 'wpcf7-redirect'),
        $user_first_name,
        '<b>' . $plugin_title . '</b>',
        '<b>' . $user_login . '</b>',
        $site_link,
        $freemius_link
    );
}
