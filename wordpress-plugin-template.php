<?php
/*
 * Plugin Name: FifthSegment Whitelist
 * Version: 1.1
 * Plugin URI: http://www.fifthsegment.com/
 * Description: This plugin creates a Roadblock on your Wordpress site, allowing only people on the list to pass through
 * Author: Abdullah Irfan
 * Author URI: http://www.abdullahirfan.com/
 * Requires at least: 3.9
 * Tested up to: 4.3
 *
 *
 * @package WordPress
 * @author Abdullah Irfan
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-wordpress-plugin-template.php' );
require_once( 'includes/class-wordpress-plugin-template-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-wordpress-plugin-template-admin-api.php' );
require_once( 'includes/lib/class-wordpress-plugin-template-post-type.php' );
require_once( 'includes/lib/class-wordpress-plugin-template-taxonomy.php' );


/**
 * Returns the main instance of WordPress_Plugin_Template to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WordPress_Plugin_Template
 */
function WordPress_Plugin_Template () {
	$instance = WordPress_Plugin_Template::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = WordPress_Plugin_Template_Settings::instance( $instance );
	}

	return $instance;
}







WordPress_Plugin_Template();

