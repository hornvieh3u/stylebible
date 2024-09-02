=== Very Simple Google Maps ===
Contributors: Michael Aronoff
Tags: maps, direction, contact, google
Requires at least: 3.3
Tested up to: 6.1
Requires PHP: 5.6
Stable tag: 2.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Contains a simple way to add an embedded Google Map to any page or post.

== Description ==
Adding an embedded Google Map is a must for most websites. I have created a simple way to add your map with just a simple shortcode. Despite the name there are some nice features that makes this plugin simple but powerful.

= Features Included =
* Simple to use with just a shortcode.
* No admin settings to mess with. All features are set within the shortcode.
* Can be styled with CSS
* Optionally link your map marker to your Google business listing directly so your business name is shown.

= Usage =
The basic shortcode to use is `[vsgmap address="street address to display"]`
See the FAQ for full usage options.

= Problems and Support =
To get fastest response use the support page in the plugin area on WordPress.org

= Please Review! =
I would love some feedback. I will try and respond to any issues you might have.

= Comments, Feedback and Request Features =
To send any suggestions, comments, or feedback about this plugin send a [message to us](http://www.ciic.com/contact-us/). 

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add shortcode to your post or page where map should appear.

== Frequently asked questions ==

1. Shortcodes what??

The shortcode you need to use is this:

`[vsgmap address="street address to display"]`

2. What are the options for use with the shortcode?

The following options are available:
address="street address to display" (Enter the street address for the location to be shown. NOT a Google Map URL)

align="left" (default is left. Valid are left, right, center)

width="400" (default is 400. Any pixel dimension is valid)

height="380" (default is 380. Any pixel dimension is valid)

info_window="A" (default is A which displays marker popup. Other option is near and the marker popup will not appear until marker is clicked on)

zoom="14" (set the default map zoom level. Valid options are 1 through 16)

companycode="" (Enter Google string cid from company maps listing URL)

maptype="" (default is m, m = normal map, k = satellite, h = hybrid, p = terrain)

3. How do I find the Google cid code???

Find your company listing on a Google Maps result page and click the link to email the link. It will open an email and the full link will be visible. The cid # is usually easy to see. you only need the part after cid= you do not need the whole thing. You can also try this web page CID Finder: http://ryanbradley.com/tools/google-cid-finder

4. How can I put a border around the map?

The plugin wraps the embed within a div so this can be achieved with with css.
Simply add the following to your themes style.css
`.vsg-map iframe { border: 1px solid; }`

Of course you can change the css any way you like.

== Changelog ==

= 2.0 =
First public and stable version.

= 2.2 =
Add the word "street" to the description so usage is more clear.

= 2.3 =
Update for WP 3.8.1

= 2.4 =
Update for WP 3.9

= 2.5 =
Added fix to center location point in the map as suggested by user SiNNeD.

= 2.6 =
Update for WP 4.2

= 2.7 =
Update for WP 4.3

= 2.8 =
Added maptype

= 2.8.2 =
Update for WP 4.8

= 2.8.3 =
Typos fixed as suggested by user G. Hyder and a few others I found myself.

= 2.8.4 =
Tested with WP 5.3
This plugin is still shortcode based. I will add a block for the new Block Editor at some point but it is not ready yet. You can still use the shortcode in a classic block until then.

= 2.8.5 =
Tested with WP 6

= 2.8.6 =
Tested with WP 6.1

= 2.9 =
Update to address security issue