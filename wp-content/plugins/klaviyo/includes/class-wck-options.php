<?php

/**
 * Class WCK_Options
 *
 * What are we trying to solve here?
 * - reduce the calls to get_option which we make several times in a single page load (is this a bad thing? the values are cached)
 * - removing admin_settings_message and klaviyo_popup options from the options management but we still want to allow existing
 *       integrations to use them and future customers who expect them to be able to change them using a filter
 * - we have duplicate properties saved in the options array e.g. klaviyo_public_api_key and public_api_key
 */
class WCK_Options
{
    /** @var string[] Plugin settings that are no longer available to set. Values are the new default values to return. */
    const DEPRECATED_SETTINGS = array(
        'admin_settings_message' => true,
        'klaviyo_popup' => true,
    );

    /** @var string WordPress option key for plugin settings. */
    const KLAVIYO_SETTINGS = 'klaviyo_settings';

    /**
     * @var bool|mixed|void $_options Klaviyo plugin options array.
     */
    private $_options;

    public function __construct()
    {
        $this->_options = get_option(self::KLAVIYO_SETTINGS);
        // Add hooks when settings are updated to ensure instance variable is up to date if no page refresh.
        add_action('update_option_' . self::KLAVIYO_SETTINGS, array( $this, 'refresh_klaviyo_settings' ), 15, 2);
        add_action('add_option_' . self::KLAVIYO_SETTINGS, array( $this, 'refresh_klaviyo_settings' ), 15, 2);
    }

    /**
     * Gets the specific setting value from the klaviyo_settings option. Allows for deprecation of settings by first
     * checking if the setting exists in the db, if not will check if the setting is deprecated and will expose a filter
     * so customers can still adjust this value if absolutely necessary.
     *
     * @param string $option Name of sub-setting which is a key on the klaviyo_settings array.
     * @param mixed|string|void $default Default value to return if option value isn't present.
     * @return mixed|string|void
     */
    public function get_klaviyo_option($option, $default = '')
    {
        $option_value = $default;
        if (isset($this->_options[ $option ])) {
            $option_value = $this->_options[ $option ];
        } elseif (array_key_exists($option, self::DEPRECATED_SETTINGS)) {
            $option_value = apply_filters('wck_option_' . $option, self::DEPRECATED_SETTINGS[$option]);
        }

        return $option_value;
    }

    /**
     * Refresh the instance variable when the 'klaviyo_settings' WordPress option is updated. This is hooked into the
     * `update_option_klaviyo_settings` hook which is dynamically called in WordPress' update_option function.
     *
     * @param array $old_value Unused - this is passed along in the hook.
     * @param array $new_value The new value to save to the instance variable.
     */
    public function refresh_klaviyo_settings($old_value, $new_value)
    {
        $this->_options = $new_value;
    }

    /**
     * Gets all the settings values from the klaviyo_settings option. If not set returns an empty array.
     *
     * @return array
     */
    public function get_all_options()
    {
        return $this->_options ? $this->_options : array();
    }
}
