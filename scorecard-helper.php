<?php

/*
Plugin Name: Lawyerist Small Firm Scorecard Helper Plugin
Plugin URI: https://lawyerist.com
Description: Enhanced functionality for the Small Firm Scorecard.
Author: Sam Glover
Version: 1.0.0.
Author URI: https://lawyerist.com
*/

if ( !defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . 'common/scorecard-helper-common.php' );

if ( is_admin() ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/scorecard-helper-admin.php' );
}

if ( !is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'frontend/scorecard-helper-frontend.php' );

	function scorecard_helper_frontend_stylesheet() {
		wp_enqueue_style( 'scorecard-helper-frontend-css', plugins_url( 'frontend/scorecard-helper-frontend.css', __FILE__ ) );
	}

	add_action( 'wp_enqueue_scripts', 'scorecard_helper_frontend_stylesheet' );

}
