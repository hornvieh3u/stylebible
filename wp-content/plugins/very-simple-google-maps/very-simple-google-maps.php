<?php
/*
Plugin Name: Very Simple Google Maps
Description: Contains a simple way to add an embedded Google Map to any page or post. Use: [vsgmap address="street address to display"] Other optional items: companycode="Google string cid for company maps listing" width="" height="" align="" info_window="A or near for off" maptype="m, k, h, or p" (m – normal map, k – satellite, h – hybrid, p – terrain). Align Width and Height default to Left 480x300 unless entered.
Version: 2.9
Author: Michael Aronoff
License: GPL2
*/
/* This section enables adding an very simple embeded Google Map with only a simple shortcode */
    function vsg_maps_shortcode($atts, $content = null) {
    extract(shortcode_atts(array(
    "align" => 'left',
    "width" => '400',
    "height" => '380',
    "address" => '',
	"info_window" => 'A',
	"zoom" => '14',
	"companycode" => '',
	"maptype" => 'm'
    ), $atts));
	$query_string = 'q=' . rawurlencode($address) . '&cid=' . rawurlencode($companycode) . '&t=' . rawurlencode($maptype) . '&center=' . rawurlencode($address);
    return '<div class="vsg-map"><iframe align="'.$align.'" width="'.esc_html($width).'" height="'.esc_html($height).'" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?&'.htmlentities($query_string).'&output=embed&z='.esc_html($zoom).'&iwloc='.esc_html($info_window).'&visual_refresh=true"></iframe></div>';
    }
    add_shortcode("vsgmap", "vsg_maps_shortcode");
	
?>