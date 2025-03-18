<?php

use Cbx\Petition\Helpers;

use Cbx\Petition\CBXSetting;

/**
 * Fired when the plugin is uninstalled.
 *
 *
 * @link       http://codeboxr.com
 * @since      1.0.0
 *
 * @package    cbxpetition
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * The code that runs during plugin uninstall.
 */
function uninstall_cbxpetition() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/Uninstall.php';

	Uninstall::uninstall();
}//end function uninstall_cbxpetition

if ( ! defined( 'CBXPETITION_PLUGIN_NAME' ) ) {
    uninstall_cbxpetition();
}