<?php
/**
 * @package Wp GAP Notifications
 * @version 1.1
 */
/*
Plugin Name: Wp GAP Notifications
Description: Receive your WordPress site notifications in your Gap account and publish your posts to Gap channel.
Author: Group Raha
Version: 1.1.2
Text Domain: wgn-plugin
Domain Path: /lang
*/
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! defined( 'wgn_PLUGIN_URL' ) ){
    define( 'wgn_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
}
if ( ! defined( 'wgn_PLUGIN_DIR' ) ){
    define( 'wgn_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
}
/**
 * Update when DB tables change
 */
define( "wgn_DB_VERSION", 1 );

require_once(wgn_PLUGIN_DIR."/inc/Wgn_notific.class.php");
require_once(wgn_PLUGIN_DIR."/functions.php");

// Get all of our plugin options in just one query :)
$tdata = wgn_get_option();
$wgn_settings = 
// create custom plugin settings menu
add_action('admin_menu', 'wgn_create_menu');
function wgn_create_menu() {
 //create new top-level menu
    add_menu_page('wp gap notifications Plugin Settings', __('wp gap notifications Settings','wgn-plugin'), 'administrator', 'wgn', 'wgn_settings_page',plugins_url('icon.png', __FILE__));
 //call register settings function
   add_action( 'admin_init', 'register_wgn_settings' );
}
function register_wgn_settings() {
            //register our settings
            register_setting( 'wgn-settings-group', 'wgn_api_token' , 'sanitize_text_field');
            register_setting( 'wgn-settings-group', 'wgn_bot_token', 'sanitize_text_field');
            register_setting( 'wgn-settings-group', 'wgn_channel_username', 'sanitize_text_field' );
            register_setting( 'wgn-settings-group', 'wgn_send_to_channel', 'sanitize_text_field' );
            register_setting( 'wgn-settings-group', 'wgn_channel_pattern', 'wgn_sanitize_text_field');
            register_setting( 'wgn-settings-group', 'wgn_send_thumb', 'sanitize_text_field' );
            register_setting( 'wgn-settings-group', 'wgn_hashtag', 'sanitize_text_field' );
            register_setting( 'wgn-settings-group', 'wgn_markdown', 'sanitize_text_field' );
            register_setting( 'wgn-settings-group', 'wgn_web_preview', 'sanitize_text_field' );
            register_setting( 'wgn-settings-group', 'wgn_img_position', 'sanitize_text_field' );
            register_setting( 'wgn-settings-group', 'wgn_excerpt_length', 'sanitize_text_field' );
}
// If api_token has been set, then add our hook to phpmailer.
if ($tdata['wgn_api_token'] != null ) {
    add_action( 'phpmailer_init', 'wgn_phpmailer_hook' );
}
?>