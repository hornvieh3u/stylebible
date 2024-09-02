=== Klaviyo ===
Contributors: klaviyo, bialecki, bawhalley
Tags: analytics, email, marketing, klaviyo, woocommerce
Requires at least: 4.4
Tested up to: 6.1.1
Stable tag: 3.0.7

Easily integrate Klaviyo with your WooCommerce stores. Makes it simple to send abandoned cart emails, add a newsletter sign up to your site and more.

== Description ==

[Klaviyo](https://www.klaviyo.com) is a unified customer platform that gives your online brand direct ownership of your consumer data and interactions, empowering you to turn transactions with customers into productive long-term relationships—at scale. Because the Klaviyo database integrates seamlessly with your tech stack, you can get the full story on every customer that visits, and then—from the same platform—use those insights to automate personalized email and SMS communications that make people feel seen. With Klaviyo, it’s easy to talk to every customer like you know them, and grow your business—on your own terms.

Talk to customers like you know them. Because you do.

####Sync all your store data with a single click
Our seamless one-click ecommerce integration allows you to sync all your historical and real-time data, so you can stay on top of every single interaction people have with your brand.

####Automations help you make money while you sleep
Dozens of built-in automations are fully customizable, like welcome emails, happy birthday, or abandon cart. Each can have any mix of emails and texts. So while you’re dreaming up your next big idea, customers are automatically getting timely, actionable info.

####Smarter targeting. Deeper personalization. Bigger payoff.
Drive more sales with powerful personalization. Whether you’re sending a drip campaign, a transactional email, or a special holiday campaign, ultra-relevant content can help you boost engagement—and earn more revenue.

####Get more answers out of your data
Data is powerful—but only if you can find it, understand it, and act on it. With Klaviyo, performance is clear. Pre-built reports answer the marketing questions that matter most. Go beyond vanity metrics and understand what’s driving sales. (Really.)

####Benchmarks
See how your performance stacks up against peers. With Klaviyo, you get relevant, real-life benchmarks based on real-time data from 90,000+ brands. See how you compare to businesses of your size and scope, and to your overall industry. You always know what to do to improve.

== Installation ==

Integrating Klaviyo and your WooCommerce store is a simple process:

1. Install/activate Klaviyo's plugin.
2. Click on Klaviyo in your left-menu, then click Connect Account.
3. Click through two screens to approve access and finish setting up your integration.

For detailed instructions on integrating Klaviyo and WooCommerce please visit our [Help Center](https://help.klaviyo.com/hc/en-us/articles/115005255808-Integrate-with-WooCommerce).

== Changelog ==
= 3.0.7 2023-01-24 =
* Changed - Updated "Tested up to" from 6.0.1 => 6.1.1

= 3.0.6 2023-01-03 =
* Fixed - Undefined categories in cart rebuild.
* Fixed - Added ProductID to viewed product events for better integration with product recommenders.

= 3.0.5 2022-12-08 =
* Fixed - Prevent automatic integration removal.
* Removed - Sending webhook on plugin deactivation.

= 3.0.4 2022-10-20 =
* Update Removed product description from the kl_build_add_to_cart_data method to reduce the size of the payload.
* Update Started Checkout events not working with TT2 theme
* Update Use POST instead of GET when sending through Added to Cart Event.

= 3.0.3 2022-04-12 =
* Update - Query only for product post_type at klaviyo/v1/products resource.
* Update - Use get_home_url() for url query param in auth kickoff request.

= 3.0.2 2022-03-28 =
* Update - Assets for brand refresh.
* Fix - Undefined index warnings in cart build.

= 3.0.1 2022-02-07 =
* Fix - Remove redirect after update/install.

= 3.0.0 2022-02-07 =
* Add - Options endpoint supporting GET/POST requests.
* Add - Improved validation function for custom endpoints.
* Add - `is_most_recent_version` key to the response from the /klaviyo/v1/version endpoint detailing whether plugin update is available.
* Add - Webhook service for outgoing requests to Klaviyo's webhook endpoint.
* Add - Redirect to Klaviyo settings page after activation.
* Add - Deactivation logic removing options, webhooks and sending request to Klaviyo to keep integration state aligned.
* Add - WCK_Options class to handle deprecated options and adjusting via filter.
* Add - `disable` endpoint to handle plugin data cleanup and deactivation when removed in Klaviyo.
* Update - Updated plugin settings page allowing for management of settings in Klaviyo. Maintain original for non-WooCommerce sites.
* Update - Use __DIR__ to define KLAVIYO_PATH constant for test compatibility.
* Fix - PHP Notices on admin page when initial options are not set.
* Deprecate - Removed 'klaviyo_popup' and 'admin_settings_message' from `klaviyo_settings` option.

= 2.5.5 2021-12-09 =
* Update - Support for Synching Product Variations.

= 2.5.4 2021-11-10 =
* Update - Default SMS consent disclosure text

= 2.5.3 2021-10-27 =
* Fixed - Over representation of cart value in Added to Cart events.

= 2.5.2 2021-08-10 =
* Add - Support for Chained Products
* Deprecation - Displaying Email checkbox on checkout pages based on ListId set in Plugin settings.
This will be displayed using the Email checkbox setting on the Plugin settings page, as done for SMS checkout checkbox

= 2.5.1 2021-07-23 =
* Update - Adjusted priority of kl_added_to_cart_event hook to allow for line item calculations.

= 2.5.0 2021-07-12 =
* Add - Added to Cart event.

= 2.4.2 2021-06-16 =
* Add - Use exchange_id for "Started Checkout" if available
* Update - Lowered priority of consent checkboxes to address conflicts with some checkout plugins

= 2.4.1 2021-04-14 =
* Fix - Address console error faced while displaying deprecation notice on plugin settings page.

= 2.4.0 2021-03-17 =
* Add - Class to handle Plugins screen update messages.
* Add - Collecting SMS consent at checkout.
* Update - Refactor adding checkout checkbox to allow for re-ordering in form.
* Update - Plugin settings form redesigned to be more intuitive.
* Update - Enqueue Identify script before Viewed Product script.
* Update - Moving to webhooks to collect Email and SMS consent.
* Fix - Remove unnecessary wp_reset_query call in Klaviyo analytics.
* Fix - Move _learnq assignment outside of conditional in identify javascript.
* Fix - Assign commenter email value for localization.

= 2.3.6 2020-10-27 =
* Fix - Remove escaping backslashes from Started Checkout title property

= 2.3.5 2020-10-19 =
* Fix - Remove escaping backslashes from Viewed Product title property

= 2.3.4 2020-10-01 =
* Fix - Remove unused import.

= 2.3.3 2020-09-25 =
* Fix - Cart state issue with rebuild when composite products are present

= 2.3.2 2020-09-11 =
* Fix - Encode non-ascii started checkout event data
* Fix - Handle checkout without Klaviyo cookie

= 2.3.1 2020-09-08 =
* Fix - Update to fix fatal error for websites not using WooCommerce plugin

= 2.3.0 2020-09-07 =
* Update - Removing all external javascripts from the Checkout page

= 2.2.6 2020-09-04 =
* Fix - Update to add permission callback for all custom endpoints (Wordpress 5.5)

= 2.2.5 2020-08-20 =
* Fix - Rename undefined variable

= 2.2.4 2020-08-05 =
* Tweak - Update to be more defensive around global server variables

= 2.2.3 2020-06-23 =
* Fix - Identify call in checkout billing fields

= 2.2.2 2020-06-11 =
* Fix - Check for checkout variable
* Fix - Resolve register_rest_route_warning
* Dev - Increase max WP version to 5.4.2
* Dev - Increase max WC version to 4.2.0

= 2.2.1 2020-05-26 =
* Tweak - Small update to legacy signup form widget

= 2.2.0 2020-05-14 =
* Fix - Custom order and product count method

= 2.1.9 2020-05-12 =
* Fix - Security fix

= 2.1.8 2020-04-24 =
* Dev - Refactor API code for unit tests

= 2.1.7 2020-01-28 =
* Add new authentication for api

= 2.1.6 2020-01-27 =
* Fix - Revert authentication patch
* Fix - Making sure characters are encoded correctly on signup success

= 2.1.5 2020-01-22 =
* Fix - Improve authentication for custom api endpoints

= 2.1.4 2019-12-04 =
* Fix - Check index is set for subscribe checkbox during checkout
* Fix - Move klaviyo.js script to highest priority in footer and add missing single quotes around src

= 2.1.3 =
* Fix - Deactivate old Klaviyo plugins if active
* Fix - Check if Klaviyo Settings index exists
* Fix - Pluck product categories only if array

= 2.1.2 =
* Add support for latest api version (v3)

= 2.1.1 =
* Check for existing Klaviyo plugins avoiding incompatibility

= 2.1.0 =
* Move all javascript to external files
* Compatible with just WP

= 2.0.7 =
* Add widget for Klaviyo's built-in signup forms

= 2.0.6 =
* Be able to customize CSS for forms
* Fix issue with button text display

= 2.0.5 =
* Remove signupform js as it's included in klaviyo.js

= 2.0.4 =
* Add klaviyo.js

= 2.0.3 =
* Escape quotes in product titles

= 2.0.2 =
* Use new endpoint for checkout subscriptions

= 2.0.1 =
* Compatibility for PHP 7.2 and remove PHP warnings
* Add persistent cart URL for rebuilding abandoned carts
* Add support for composite product cart rebuild

= 2.0 =
* Bundles the Wordpress and Woocommerce plugin together as one.
* An option to Add a checkbox at the end of the billing form that can be configured to sync with a specified Klaviyo list. The text can be configured in the settings. Currently set to off by default.
* Install the Klaviyo pop-up code by clicking a checkbox in the admin UI
* Automatically adds the viewed product snippet to product pages.
* Adds product categories which can be segmented to the started checkout metric.
* Removes the old unused code and functions.
* Updates all deprecated WC and Wordpress functions/methods.
* Removes the description tag from the checkout started event.
* Captures first and last names to the started check out metric.

= 1.3.3 =
* Updating docs.

= 1.3.2 =
* Tested for support for Wordpress 4.8.

= 1.3 =
* Added HTTPS support for embedded form.
* Updated logo branding.
* Updated links.
* Updated previously deprecated functions.

= 1.2.0 =
* Updating to allow embedding an email sign up form.

= 1.1.2 =
* Updating docs.

= 1.1.1 =
* Fixing documentation a bit and one bug fix.

= 1.1 =
* Adding in automatic tracking of users if they log in or post a comment.

= 1.0 =
* Initial version
