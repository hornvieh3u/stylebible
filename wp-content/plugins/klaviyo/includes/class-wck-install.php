<?php

/**
 * Installation related functions and actions.
 *
 * @author    Klaviyo
 * @category  Admin
 * @package   WooCommerceKlaviyo/Classes
 * @version     0.9.0
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (! class_exists('WCK_Install')) :

/**
 * WCK_Install Class
 */
    class WCK_Install
    {
      /**
       * Hook in tabs.
       */
        public function __construct()
        {
            register_activation_hook(WCK_PLUGIN_FILE, array( $this, 'install' ));

            add_action('admin_init', array( $this, 'admin_init' ), 5);
        }

    /**
     * Check plugin version and maybe redirect to Klaviyo settings page if recently activated.
     */
        public function admin_init()
        {
            $this->check_version();
        }

      /**
       * Check version of plugin against that saved in the DB to identify update.
       *
       * @access public
       * @return void
       */
        public function check_version()
        {
            if (! defined('IFRAME_REQUEST') && ( get_option('woocommerce_klaviyo_version') != WCK()->getVersion() )) {
                $this->install();
              // Send options and version number to Klaviyo.
                $this->post_update_to_klaviyo();

                do_action('woocommerce_klaviyo_updated');
            }
        }

    /**
     * Send options and plugin version information to Klaviyo during the plugin update. Remove
     * the site transient so we start checking for a new version of Klaviyo again.
     */
        protected function post_update_to_klaviyo()
        {
            // Send options to Klaviyo.
            WCK()->webhook_service->send_options_webhook($is_updating = true);

            // Remove transient so we start checking 'set_site_transient_update_plugins' again.
            delete_site_transient('is_klaviyo_plugin_outdated');
        }

  /**
   * Install WCK
   */
        public function install()
        {

            $this->create_options();
            $this->create_roles();

            // Update version
            update_option('woocommerce_klaviyo_version', WCK()->getVersion());

            // Flush rules after install
            flush_rewrite_rules();
        }

  /**
   * Default options
   *
   * Sets up the default options used on the settings page
   *
   * @access public
   */
        function create_options()
        {
        }

  /**
   * Create roles and capabilities
   */
        public function create_roles()
        {
            global $wp_roles;

            if (class_exists('WP_Roles')) {
                if (! isset($wp_roles)) {
                    $wp_roles = new WP_Roles();
                }
            }

            // Add supplemental permissions to certain users. Assumes WooCommerce roles exist.
            if (is_object($wp_roles)) {
                $capabilities = self::get_core_capabilities();

                foreach ($capabilities as $cap_group) {
                    foreach ($cap_group as $cap) {
                        $wp_roles->add_cap('shop_manager', $cap);
                        $wp_roles->add_cap('administrator', $cap);
                    }
                }
            }
            $test_roles = get_object_vars($wp_roles)['roles'];
            return $test_roles;
        }

  /**
   * Get capabilities for WooCommerceKlaviyo - these are assigned to admin/shop manager during installation or reset
   *
   * @access public
   * @return array
   */
        public static function get_core_capabilities()
        {
            $capabilities = array();

            $capability_types = array( 'klaviyo_shop_cart', );

            foreach ($capability_types as $capability_type) {
                $capabilities[ $capability_type ] = array(
                // Post type
                "edit_{$capability_type}",
                "read_{$capability_type}",
                "delete_{$capability_type}",
                "edit_{$capability_type}s",
                "edit_others_{$capability_type}s",
                "publish_{$capability_type}s",
                "read_private_{$capability_type}s",
                "delete_{$capability_type}s",
                "delete_private_{$capability_type}s",
                "delete_published_{$capability_type}s",
                "delete_others_{$capability_type}s",
                "edit_private_{$capability_type}s",
                "edit_published_{$capability_type}s",

                // Terms
                "manage_{$capability_type}_terms",
                "edit_{$capability_type}_terms",
                "delete_{$capability_type}_terms",
                "assign_{$capability_type}_terms"
                );
            }

            return $capabilities;
        }

  /**
   * woocommerce-klaviyo_remove_roles function.
   *
   * @access public
   * @return void
   */
        public static function remove_roles()
        {
            global $wp_roles;

            if (class_exists('WP_Roles')) {
                if (! isset($wp_roles)) {
                    $wp_roles = new WP_Roles();
                }
            }

            if (is_object($wp_roles)) {
                $capabilities = self::get_core_capabilities();

                foreach ($capabilities as $cap_group) {
                    foreach ($cap_group as $cap) {
                        $wp_roles->remove_cap('shop_manager', $cap);
                        $wp_roles->remove_cap('administrator', $cap);
                    }
                }
            }
        }

    /**
     * Called from WCK_Api via the `disable` route. Deactivate Klaviyo plugin via builtin function so hooks fire.
     */
        public function deactivate_klaviyo()
        {
            deactivate_plugins(KLAVIYO_BASENAME);
        }

    /**
     * Handle cleanup of the plugin.
     * Delete options and remove WooCommerce webhooks.
     */
        public function cleanup_klaviyo()
        {
            // We can't remove webhooks without WooCommerce. No need to remove the integration app-side.
            if (is_plugin_active('woocommerce/woocommerce.php')) {
                // Remove WooCommerce webhooks
                self::remove_klaviyo_webhooks();
            }

            // Lastly, delete Klaviyo-related options.
            delete_option('klaviyo_settings');
            delete_option('woocommerce_klaviyo_version');
            delete_site_transient('is_klaviyo_plugin_outdated');
        }

    /**
     * Remove Klaviyo related webhooks. The only way to identify these are through the delivery url so check for the
     * Woocommerce webhook path.
     */
        private static function remove_klaviyo_webhooks()
        {
            $webhook_data_store = WC_Data_Store::load('webhook');
            $webhooks_by_status = $webhook_data_store->get_count_webhooks_by_status();
            // $webhooks_by_status returns an associative array with a count of webhooks in each status.
            $count = array_sum($webhooks_by_status);

            if (0 === $count) {
                return;
            }

            // We can only get IDs and there's not a way to search by delivery url which is the only way to identify
            // a webhook created by Klaviyo. We'll have to iterate no matter what so might as well get them all.
            $webhook_ids = $webhook_data_store->get_webhooks_ids();

            foreach ($webhook_ids as $webhook_id) {
                $webhook = wc_get_webhook($webhook_id);
                if (! $webhook) {
                    continue;
                }

                if (false !== strpos($webhook->get_delivery_url(), '/api/webhook/integration/woocommerce')) {
                    $webhook_data_store->delete($webhook);
                }
            }
        }
    }

endif;
