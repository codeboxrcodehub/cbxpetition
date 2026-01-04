<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Fired when the plugin is uninstalled.
 *
 *
 * @link       https://codeboxr.com
 * @since      1.0.0
 *
 * @package    cbxpetition
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . "vendor/autoload.php";

/**
 * The code that runs during plugin uninstall.
 */
function cbxpetition_uninstall() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/CBXPetitionUninstall.php';

    CBXPetitionUninstall::uninstall();
}//end function cbxpetition_uninstall

if ( ! defined( 'CBXPETITION_PLUGIN_NAME' ) ) {
    cbxpetition_uninstall();
}