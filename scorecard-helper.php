<?php

/*
Plugin Name: Lawyerist Small firm Scorecard Helper Plugin
Plugin URI: https://lawyerist.com
Description: Enhanced functionality for the Small Firm Scorecard.
Author: Sam Glover
Version: [See README.md for changelog]
Author URI: http://lawyerist.com
*/

require_once( plugin_dir_path( __FILE__ ) . 'common/scorecard-helper-common.php' );
require_once( plugin_dir_path( __FILE__ ) . 'frontend/scorecard-helper-frontend.php' );

if ( is_admin() ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/scorecard-helper-admin.php' );
}

function scorecard_helper_frontend_stylesheet() {
	wp_enqueue_style( 'scorecard-helper-frontend-css', plugins_url( 'frontend/scorecard-helper-frontend.css', __FILE__ ) );
}

add_action( 'wp_enqueue_scripts', 'scorecard_helper_frontend_stylesheet' );