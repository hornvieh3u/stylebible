<div class="wck-settings">
    <div class="wck-content-wrapper">
        <div class="wck-content">
            <div class="wck-logo">
                <img src="<?= KLAVIYO_URL; ?>includes/admin/assets/wck-logo.svg">
            </div>
            <div class="wck-content-subtitles">
                <?php if (isset($this->klaviyo_options['klaviyo_public_api_key'])) { ?>
                    <span class="wck-content-title">Your Klaviyo + WooCommerce integration is connected!</span>
                    <span class="wck-content-subtitle">Head back to the Klaviyo dashboard to continue with next steps for getting your account up and running or to modify any of your Klaviyo + WooCommerce integration settings.</a> </span>
                    <div class="connect-buttons">
                        <fieldset class="connect-button">
                            <a id="wck_manage_settings" class="button button-primary" href="https://www.klaviyo.com/integration/woocommerce" target="_blank">Go to your WooCommerce settings</a>
                        </fieldset>
                    </div>
                <?php } else { ?>
                    <span class="wck-content-title">Connect your Klaviyo account to use the Klaviyo + WooCommerce integration.</span>
                    <span class="wck-content-subtitle">Build custom segments, send automations, and track purchase activity in Klaviyo. Log in to authorize an account connection. New to Klaviyo and want to learn more? Check out our <a class="subtitle-guide-link" href="https://help.klaviyo.com/hc/en-us/articles/115005255808-How-to-Integrate-with-WooCommerce" target="_blank">How to Integrate with WooCommerce guide.</a> </span>
                    <div class="connect-buttons">
                        <fieldset class="connect-button">
                            <a id="wck_oauth_connect" class="button button-primary" href="https://www.klaviyo.com/integration-oauth-one/woocommerce/auth/handle?url=<?= get_home_url(); ?>">Connect Account</a>
                            <a id="wck_create_account" class="button create-account" href="https://www.klaviyo.com/signup/woocommerce" target="_blank">Create Account</a>
                        </fieldset>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
