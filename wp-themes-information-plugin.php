<?php
/**
 * Plugin Name:     WP Themes Information Plugin
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     A plugin for getting theme information from the wordpress.org themes API. It has some options for output.
 * Author:          William Patton
 * Author URI:      https://www.pattonwebz.com/
 * Text Domain:     wp-themes-api-plugin
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         WP_Themes_Information_Plugin
 */

/** -------------------------------- *
 * constants
 *  -------------------------------- */
if ( ! defined( 'WPTIP_PLUGIN_DIR' ) ) {
	define( 'WPTIP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

include WPTIP_PLUGIN_DIR . 'inc/class-wptip-theme-info.php';

$wptip_info = WPTIP_Theme_Info::init();
