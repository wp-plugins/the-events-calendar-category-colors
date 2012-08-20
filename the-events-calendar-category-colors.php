<?php
/*
Plugin Name: The Events Calendar Category Colors
Plugin URI: http://wordpress.org/extend/plugins/the-events-calendar-category-colors/
Description: This plugin adds event category background coloring to <a href="http://wordpress.org/extend/plugins/the-events-calendar/">The Events Calendar</a> plugin.
Version: 1.4.1
Text Domain: events-calendar-category-colors
Author: Andy Fragen
Author URI: http://thefragens.com/blog/
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/**
* The Events Calendar Category Colors
*
* This plugin adds background coloring to The Events Calendar plugin.
*
* @package      the-events-calendar-pro-alarm
* @link         https://github.com/afragen/events-calendar-category-colors/
* @link         http://wordpress.org/extend/plugins/the-events-calendar-category-colors/
* @author       Andy Fragen <andy@thefragens.com>
* @copyright    Copyright (c) 2012, Andrew Fragen
*
* The Events Calendar Category Colors is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License version 2, as published by the
* Free Software Foundation.
*
* You may NOT assume that you can use any other version of the GPL.
*
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details
*
* You should have received a copy of the GNU General Public License along with
* this program; if not, write to:
*
*      Free Software Foundation, Inc.
*      51 Franklin St, Fifth Floor
*      Boston, MA  02110-1301  USA
*
* The license for this software can also likely be found here:
* http://www.gnu.org/licenses/gpl-2.0.html*
*/
/* Add your functions below this line */

// 'teccc_' prefix is derived from [tec]the events calendar [c]ategory [c]olors
define(VERSION, '1.4.1');
$teccc_debug = false;

if ( $teccc_debug ) { var_dump(get_option('teccc_options')); }

add_action( 'plugins_loaded', 'teccc_requires_tec' );
function teccc_requires_tec() {
	if ( !class_exists( 'TribeEvents' ) ) { 
		echo '<div class="error">
       <p>The Events Calendar Category Colors requires The Events Calendar plugin to be active.</p>
    </div>';
		deactivate_plugins(__FILE__);
	}
}

global $teccc_text_colors;
$teccc_text_colors = array(
	'Black' => '#000',
	'White' => '#fff',
	'Gray' => '#999'
	);

global $teccc_font_weights;
$teccc_font_weights = array(
	'Bold' => 'bold',
	'Normal' => 'normal'
	);

function teccc_get_category_slugs() {
	$terms = get_terms('tribe_events_cat');
	$slugs = array();
	foreach ($terms as $term) {
		$slugs[] = $term->slug;
	}
	return $slugs;
}

function teccc_get_category_names() {
	$terms = get_terms('tribe_events_cat');
	$cat_names = array();
	foreach ($terms as $term) {
		$cat_names[] = preg_replace( '/\s/', '&nbsp;', $term->name );
	}
	return $cat_names;
}
	
function teccc_write_category_css() { 
	$slugs = teccc_get_category_slugs();
	$count = count($slugs);
	$options = get_option('teccc_options');
	$legend = teccc_write_legend_css();
	$catCSS = array();
	$catCSS[] = '';
	$catCSS[] = '<!-- The Events Calendar Category Colors ' . VERSION . ' generated CSS -->';
	$catCSS[] = '<style type="text/css" media="screen">';
	$catCSS[] = '.tribe-events-calendar a { font-weight:' . $options['font_weight'] .'; }';
	for ($i = 0; $i < $count; $i++) {
		$catCSS[] = '.tribe-events-calendar .cat_' . $slugs[$i] . ' a { color:' .  $options[$slugs[$i].'-text'] . '; }' ;
		$catCSS[] = '.cat_' . $slugs[$i] . ', .tribe-events-calendar .cat_' . $slugs[$i] . ', .cat_' . $slugs[$i] . ' > .tribe-events-tooltip .tribe-events-event-title { background-color:' . $options[$slugs[$i].'-background'] . '; border-left:5px solid ' . $options[$slugs[$i].'-border'] . '; border-right:5px solid ' . $options[$slugs[$i].'-background'] . '; color:' . $options[$slugs[$i].'-text'] . '; }' ;		
	}
	if ( ! isset( $options['custom_legend_css']  ) ) { $catCSS = array_merge( $catCSS, $legend ); }
	$catCSS[] = '</style>';
	$content = implode( "\n", $catCSS ) . "\n";
	if ( ! is_admin() ) { echo $content; }
	if ( isset( $options['add_legend'] ) ) { add_action( 'teccc_legend_hook', 'teccc_legend' ); }
}

// Create legend action hook, html and CSS
function teccc_legend_hook() {
	do_action( 'teccc_legend_hook' );
}

function teccc_legend() {
	$slugs = teccc_get_category_slugs();
	$count = count($slugs);
	$cat_names = teccc_get_category_names();
	$tribe_teccc = TribeEvents::instance();
	$legend = array();
	$legend[] = '<div id="legend_box">';
	$legend[] = '<ul id="legend">';
	for ($i = 0; $i < $count; $i++) {
		$legend[] = '<a href="' . $tribe_teccc->getLink() . 'category/' . $slugs[$i] . '"><li class="cat_' . $slugs[$i] . '">' . $cat_names[$i] . '</li></a>';
	}
	$legend[] = '</ul>';
	$legend[] = '</div>';
	$content = implode( "\n", $legend ) . "\n";
	echo $content;
}

function teccc_write_legend_css() {
	$arr = array();
	$arr[] = '#legend_box { font:bold 10px/4em sans-serif; text-align:center; }';
	$arr[] = '#legend a { text-decoration:none; }';
	$arr[] = '#legend li { display:inline; list-style-type:none; padding:7px; margin-left:0.7em; text-align:center; text-transform:uppercase; }';
	return $arr;
}


// Set-up Action and Filter Hooks
register_activation_hook(__FILE__, 'teccc_add_defaults');
register_uninstall_hook(__FILE__, 'teccc_delete_plugin_options');
add_action('admin_init', 'teccc_init' );

// Define default option settings
function teccc_add_defaults() {
	$slugs = teccc_get_category_slugs();
	$count = count($slugs);
	$tmp = get_option('teccc_options');
	if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
		delete_option('teccc_options');
		for ($i = 0; $i < $count; $i++) {
			$arr[$slugs[$i].'-text'] = '#000';
			$arr[$slugs[$i].'-background'] = '#CFCFCF';
			$arr[$slugs[$i].'-border'] = '#CFCFCF';
		}
		$arr['font_weight'] = 'bold';
		update_option('teccc_options', $arr);
	}
}

// Delete options table entries ONLY when plugin deactivated AND deleted
function teccc_delete_plugin_options() {
	delete_option('teccc_options');
}

// Init plugin options to white list our options
function teccc_init(){
	register_setting( 'teccc_category_colors', 'teccc_options', 'teccc_validate_options' );
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function teccc_validate_options($input) {
	global $teccc_text_colors;
	$slugs = teccc_get_category_slugs();
	$count = count($slugs);
	for ($i = 0; $i < $count; $i++) {
		// Sanitize textbox input (strip html tags, and escape characters)
		// May not be needed with jQuery color picker
		$input[$slugs[$i].'-background'] =  wp_filter_nohtml_kses($input[$slugs[$i].'-background']);
		$input[$slugs[$i].'-background'] =  ereg_replace( '[^#A-Za-z0-9]', '', $input[$slugs[$i].'-background'] );
		if ( $input[$slugs[$i].'-background'] == '' ) { $input[$slugs[$i].'-background'] = '#CFCFCF'; }
		
		$input[$slugs[$i].'-border'] =  wp_filter_nohtml_kses($input[$slugs[$i].'-border']);
		$input[$slugs[$i].'-border'] =  ereg_replace( '[^#A-Za-z0-9]', '', $input[$slugs[$i].'-border'] );
		if ( $input[$slugs[$i].'-border'] == '' ) { $input[$slugs[$i].'-border'] = '#CFCFCF'; }
		
		// Sanitize dropdown input (make sure value is one of options allowed)
		if ( !in_array($input[$slugs[$i].'-text'], $teccc_text_colors, true) ) { $input[$slugs[$i].'-text'] = '#000'; }
		
		// Helps to set value when checked
		if ( isset( $input[$slugs[$i].'-border_transparent'] ) ) { $input[$slugs[$i].'-border'] = 'transparent'; }
		if ( isset( $input[$slugs[$i].'-background_transparent'] ) ) { $input[$slugs[$i].'-background'] = 'transparent'; }	
	}
	return $input;
}

add_action( 'tribe_settings_below_tabs_tab_category-colors', 'teccc_is_saved' );
function teccc_is_saved () {
	if ( isset( $_GET['settings-updated'] ) && ( $_GET['settings-updated'] ) ) {
		$message = __( 'Settings saved.', 'tribe-events-calendar' );
		$output = '<div id="message" class="updated"><p><strong>' . $message . '</strong></p></div>';
		echo apply_filters( 'tribe_settings_success_message', $output, 'category-colors' );
	}
}

function teccc_options_elements() {
	global $teccc_text_colors, $teccc_font_weights;
	$slugs = teccc_get_category_slugs();
	$cat_names = teccc_get_category_names();
	$count = count($slugs);
	$options = get_option('teccc_options');
	$form = array();
	$form[] = '<table class="form-table">';
	$form[] = '<style type="text/css">.form-table th { font-size: 12px; }</style>';
	$form[] = '<tr><th><strong>Category Slug</strong></th><th><strong>Border Color</strong></th><th><strong>Background Color</strong></th><th><strong>Text Color</strong></th><th><strong>Current Display</strong></th></tr>';
	for ($i = 0; $i < $count; $i++) {
		$form[] = '<tr>';
		$form[] = '<td>' . $slugs[$i] . '</td>';
		
		$form[] = '<td><label><input name="teccc_options[' . $slugs[$i].'-border_transparent' . ']" type="checkbox" value="1"' . checked('1', $options[$slugs[$i].'-border_transparent'], false) . ' /> Transparent</label><br />';
			
		if ( isset( $options[$slugs[$i].'-border_transparent'] ) ) { 
			$options[$slugs[$i].'-border'] = 'transparent';			
		} else {
			$form[] = '<input type="text" class="color-picker" autocomplete="on" size="6" name="teccc_options[' . $slugs[$i] . '-border]" value="' . $options[$slugs[$i].'-border'] . '" /></td>' ;
		}
			
		$form[] = '<td><label><input name="teccc_options[' . $slugs[$i].'-background_transparent' . ']" type="checkbox" value="1"' . checked('1', $options[$slugs[$i].'-background_transparent'], false) . ' /> Transparent</label><br />';
			
		if ( isset( $options[$slugs[$i].'-background_transparent'] ) ) {
			$options[$slugs[$i].'-background'] = 'transparent';
		} else {
			$form[] = '<input type="text" class="color-picker" autocomplete="on" size="6" name="teccc_options[' . $slugs[$i] . '-background]" value="' . $options[$slugs[$i].'-background'] . '" /></td>' ;
		}

		$form[] = "<td><select name='teccc_options[" . $slugs[$i] . "-text]'>" ;		
		foreach ($teccc_text_colors as $key => $value) {
			$form[] = "<option value='$value'" . selected($value, $options[$slugs[$i].'-text'], false) . ">$key</option>";
		}
		$form[] = '</select></td>';
		if ( $options[$slugs[$i].'-text'] == '0' ) {
			$form[] = '<td><span style="border:1px #ddd solid;background-color:' . $options[$slugs[$i].'-background'] . ';border-left: 5px solid ' . $options[$slugs[$i].'-border'] . ';padding:0.5em 1em;font-weight:' . $options['font_weight'] . ';">' . $cat_names[$i] . '</span></td>';
		} else {
			$form[] = '<td><span style="background-color:' . $options[$slugs[$i].'-background'] . ';border-left: 5px solid ' . $options[$slugs[$i].'-border'] . ';border-right: 5px solid ' . $options[$slugs[$i].'-background'] . ';color:' . $options[$slugs[$i].'-text'] . ';padding:0.5em 1em;font-weight:' . $options['font_weight'] . ';">' . $cat_names[$i] . '</span></td>';
		}
		$form[] = "</tr>\n";
	}
	
	$form[] = '<tr valign="top" style="border-top:#dddddd 1px solid;"><td colspan="5"></td></tr>';
	$form[] = '<tr><th scope="row">Font-Weight Options</th>';
	$form[] = "<td><select name='teccc_options[font_weight]'>" ;
	foreach ( $teccc_font_weights as $key => $value ) {
		$form[] = "<option value='$value'" . selected($value, $options['font_weight'], false) . ">$key</option>";
		}
	$form[] = '</select></td></tr>';
	
	$form[] = '<tr><th scope="row">Add Category Legend</th><td colspan="5">';
	$form[] = '<label><input name="teccc_options[add_legend]" type="checkbox" value="1"' . checked('1', $options['add_legend'], false) . " /> Check to add a Category Legend to the calendar.</label>";
	$form[] = '<p style="color:#666666;margin-left:2px;">Remember to add `&lt;?php teccc_legend_hook(); ?&gt;` to your ecp-page-template.php</p></td></tr>';
	
	$form[] = '<tr><th scope="row">Custom Legend CSS</th><td colspan="5">';
	$form[] = '<label><input name="teccc_options[custom_legend_css]" type="checkbox" value="1"' . checked('1', $options['custom_legend_css'], false) . " /> Check to use your own CSS for category legend.</label>";
	
	$form[] = '<tr><th scope="row">Database Options</th><td colspan="5">';
	$form[] = '<label><input name="teccc_options[chk_default_options_db]" type="checkbox" value="1"' . checked('1', $options['chk_default_options_db'], false) . " /> Restore defaults upon plugin deactivation/reactivation</label>";
	$form[] = '<p style="color:#666666;margin-left:2px;">Only check this if you want to reset plugin settings upon Plugin reactivation</p></td></tr></table>';
  
	$content = implode ( "\n", $form );
	return $content;
}


//Create Category Colors tab in The Events Calendar Settings
add_action('tribe_settings_do_tabs', 'tribe_add_category_colors_tab');
function tribe_add_category_colors_tab () {
	include_once('category-colors-settings.php');
	add_action('tribe_settings_form_element_tab_category-colors', 'teccc_form_header');
	add_action('tribe_settings_before_content_tab_category-colors', 'teccc_settings_fields');
	new TribeSettingsTab( 'category-colors', __('Category Colors', 'tribe-events-calendar'), $categoryColorsTab);
}
function teccc_form_header() {
	echo '<form method="post" action="options.php">' ;
}
function teccc_settings_fields() {
	settings_fields('teccc_category_colors');
}

//load js and css for jquery-miniColors
function teccc_miniColors() {
	wp_enqueue_style( 'miniColors-css', plugin_dir_url(__FILE__) . 'resources/jquery-miniColors/jquery.miniColors.css' );
	wp_enqueue_script( 'miniColors-js', plugin_dir_url(__FILE__) . 'resources/jquery-miniColors/jquery.miniColors.js' );
	wp_enqueue_script( 'miniColors-init', plugin_dir_url(__FILE__) . 'resources/jquery-miniColors-init.js' );
}

// 'pre_get_posts' hook needed to determine events page
add_action('pre_get_posts', 'add_CSS');
//This function determines month view for displaying CSS
function add_CSS($query) {
	if ( ($query->query_vars['post_type'] == 'tribe_events') ) {
		if ( ($query->query_vars['eventDisplay'] == 'month') ) {
			add_action('wp_head', 'teccc_write_category_css');
		}
	}
}


?>