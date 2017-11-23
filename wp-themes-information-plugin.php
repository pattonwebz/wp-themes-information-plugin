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
include WPTIP_PLUGIN_DIR . 'inc/class-wptip-theme-info-metabox.php';

/**
 * Initiate the class and add actions to put metaboxs on certain post types.
 */
$wptip_instance = WPTIP_Theme_Info::init();
add_action( 'load-post.php',     'WPTIP_Theme_Info_Metabox::init' ); // adds a metabox and save action on editor screen for editing posts.
add_action( 'load-post-new.php', 'WPTIP_Theme_Info_Metabox::init' ); // adds a metabox and save action on editor for new posts.
// check if the class was instantiated because the widget class depends on it being present.
if ( $wptip_instance instanceof WPTIP_Theme_Info ) {
	include WPTIP_PLUGIN_DIR . 'inc/class-wptip-theme-info-widget.php';
	function wptip_register_widget() {
		register_widget( 'WPTIP_Theme_Info_Widget' );
	}
	add_action( 'widgets_init', 'wptip_register_widget' );
}
