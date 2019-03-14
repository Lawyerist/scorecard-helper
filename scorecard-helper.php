<?php

/*
Plugin Name: Lawyerist Small firm Scorecard Helper Plugin
Plugin URI: https://lawyerist.com
Description: Enhanced functionality for the Small Firm Scorecard.
Author: Sam Glover
Version: [See README.md for changelog]
Author URI: http://lawyerist.com
*/

namespace SCORECARD_HELPER;

define( __NAMESPACE__ . '\NS', __NAMESPACE__ . '\\' );
define( NS . 'PLUGIN_NAME', 'scorecard-helper' );
define( NS . 'PLUGIN_VERSION', '1.0.0' );
define( NS . 'PLUGIN_NAME_DIR', plugin_dir_path( __FILE__ ) );
define( NS . 'PLUGIN_NAME_URL', plugin_dir_url( __FILE__ ) );
define( NS . 'PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( NS . 'PLUGIN_TEXT_DOMAIN', 'scorecard-helper' );

// Includes PHP files.
require_once( PLUGIN_NAME_DIR . 'common/scorecard-helper-common.php' );

if ( is_admin() ) {

	require_once( PLUGIN_NAME_DIR . 'admin/scorecard-helper-admin.php' );

} else {

	require_once( PLUGIN_NAME_DIR . 'frontend/scorecard-helper-frontend.php' );

}


// Registers and enqueues the admin stylesheet.
function scorecard_helper_admin_stylesheet() {
	wp_register_style( 'scorecard-helper-admin-css', plugins_url( '/frontend/scorecard-helper-admin.css', __FILE__ ) );
	wp_enqueue_style( 'scorecard-helper-admin-css' );
}

add_action( 'admin_enqueue_scripts', 'scorecard_helper_admin_stylesheet' );


// Registers and enqueues the frontend stylesheet.
function scorecard_helper_frontend_stylesheet() {
	wp_register_style( 'scorecard-helper-frontend-css', plugins_url( '/frontend/scorecard-helper-frontend.css', __FILE__ ) );
	wp_enqueue_style( 'scorecard-helper-frontend-css' );
}

add_action( 'wp_enqueue_scripts', 'scorecard_helper_frontend_stylesheet' );
