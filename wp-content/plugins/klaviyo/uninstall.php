<?php

/**
 * WooCommerceKlaviyo Uninstall
 *
 * Uninstalling WooCommerceKlaviyo deletes user roles, options, tables, and pages.
 *
 * @author    Klaviyo
 * @category  Core
 * @package   WooCommerceKlaviyo/Uninstaller
 * @version   0.9.0
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// Remove role capabilities.
include('includes/class-wck-install.php');
WCK_Install::remove_roles();
