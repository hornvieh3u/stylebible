<?php

/**
 * Manages Klaviyo plugin updating on the Plugins screen.
 *
 * @version     2.4.0
 */

class KL_Plugins_Screen_Updates
{
    /**
     * The version for the update to Klaviyo.
     *
     * @var string
     */
    protected $new_version = '';

    /**
     * The upgrade notice shown inline.
     *
     * @var string
     */
    protected $upgrade_notice = '';

    /**
     * Upgrade notice messages for specific versions.
     */
    const UPGRADE_NOTICE_BY_VERSION = array(
        '2.4.0' => '<b>IMPORTANT:</b> Please review and save your Klaviyo plugin settings after upgrading to ensure consistent functionality.',
    );

    public function __construct()
    {
        add_action('in_plugin_update_message-klaviyo/klaviyo.php', array( $this, 'in_plugin_update_message' ), 10, 2);
    }

    /**
     * Callback method that adds upgrade notice in Plugins page.
     *
     * @param $plugin_data
     * @param $response
     */
    public function in_plugin_update_message($plugin_data, $response)
    {
        $this->new_version = $response->new_version;
        $this->upgrade_notice = $this->get_upgrade_notice();

        echo $this->upgrade_notice ? '</p><p>' . wp_kses_post($this->upgrade_notice) : '';
    }

    /**
     * Gets upgrade notice for corresponding new version from map.
     *
     * This can be expanded to utilize data available in plugin
     * readme.txt but keeping this super basic and manual for now.
     *
     * @return string
     */
    protected function get_upgrade_notice()
    {
        if (isset(self::UPGRADE_NOTICE_BY_VERSION[ $this->new_version ])) {
            return self::UPGRADE_NOTICE_BY_VERSION[ $this->new_version ];
        }
    }
}

new KL_Plugins_Screen_Updates();
