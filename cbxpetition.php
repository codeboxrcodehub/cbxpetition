<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://codeboxr.com
 * @since             1.0.0
 * @package           CBXPetition
 *
 * @wordpress-plugin
 * Plugin Name:       CBX Petition
 * Plugin URI:        https://codeboxr.com/product/cbx-petition-for-wordpress/
 * Description:       A plugin to create, manage petition and collect signatures for petition
 * Version:           2.0.0
 * Author:            Codeboxr
 * Author URI:        http://codeboxr.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cbxpetition
 * Domain Path:       /languages
 */

use Cbx\Petition\Helpers\PetitionHelper;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

defined( 'CBXPETITION_PLUGIN_NAME' ) or define( 'CBXPETITION_PLUGIN_NAME', 'cbxpetition' );
defined( 'CBXPETITION_PLUGIN_VERSION' ) or define( 'CBXPETITION_PLUGIN_VERSION', '2.0.0' );
defined( 'CBXPETITION_BASE_NAME' ) or define( 'CBXPETITION_BASE_NAME', plugin_basename( __FILE__ ) );
defined( 'CBXPETITION_ROOT_PATH' ) or define( 'CBXPETITION_ROOT_PATH', plugin_dir_path( __FILE__ ) );
defined( 'CBXPETITION_ROOT_URL' ) or define( 'CBXPETITION_ROOT_URL', plugin_dir_url( __FILE__ ) );


// Include the main class
if ( ! class_exists( 'CBXPetition', false ) ) {
	include_once CBXPETITION_ROOT_PATH . 'includes/CBXPetition.php';
}

/**
 * Checking wp version
 *
 * @param $version
 *
 * @return bool
 */
function cbxpetition_compatible_wp_version($version  = '5.3') {
	if ( version_compare( $GLOBALS['wp_version'], $version, '<' ) ) {
		return false;
	}

	// Add sanity checks for other version requirements here

	return true;
}//end method cbxpetition_compatible_wp_version

/**
 * Checking php version
 *
 * @param $version
 *
 * @return bool
 */
function cbxpetition_compatible_php_version($version = '7.4') {
	if ( version_compare( PHP_VERSION, $version, '<=' ) ) {
		return false;
	}

	return true;
}//end method cbxpetition_compatible_php_version


register_activation_hook( __FILE__, 'activate_cbxpetition' );
register_deactivation_hook( __FILE__, 'deactivate_cbxpetition' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cbxpetition-activator.php
 */
function activate_cbxpetition() {
	$wp_version  = '5.3';
	$php_version  = '7.4';

	if ( ! cbxpetition_compatible_wp_version() ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );

		/* translators: WordPress version */
		wp_die( sprintf(esc_html__( 'CBX Petition plugin requires WordPress %s or higher!', 'cbxpetition' ), $wp_version) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	if ( ! cbxpetition_compatible_php_version() ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );

		/* translators: PHP version */
		wp_die( sprintf(esc_html__( 'CBX Petition plugin requires PHP %s or higher!', 'cbxpetition' ), $php_version) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	
	
	cbxpetition_core();

	PetitionHelper::role_cap_assignment();
	PetitionHelper::create_tables();
	PetitionHelper::create_pages();

	add_option('cbxpetition_flush_rewrite_rules', 'true');
	set_transient('cbxpetition_activated_notice', 1);
	update_option( 'cbxpetition_version', CBXPETITION_PLUGIN_VERSION );

	//deactivate pro addon if version than 2.0.0
	cbxpetition_check_and_deactivate_plugin('cbxpetitionproaddon/cbxpetitionproaddon.php', '2.0.0', 'cbxpetition_proaddon_deactivated');
	/*if($action){
		set_transient('cbxpetition_proaddon_deactivated', 1);
	}*/
}//end method activate_cbxpetition

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cbxpetition-deactivator.php
 */
function deactivate_cbxpetition() {
	/*require_once plugin_dir_path( __FILE__ ) . 'includes/class-cbxpetition-deactivator.php';
	CBXPetition_Deactivator::deactivate();*/
}

/**
 * Initialize the plugin manually
 *
 * @return CBXPetition|null
 */
function cbxpetition_core() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	global $cbxpetition_core;

	if ( ! isset( $cbxpetition_core ) ) {
		$cbxpetition_core = run_cbxpetition();
	}

	return $cbxpetition_core;
}//end method cbxpetition_core


/**
 * Begins execution of the plugin.
 *
 * @since    2.0.0
 */
function run_cbxpetition() {
	return CBXPetition::instance();
}//end function run_cbxpetition


//load the plugin
$GLOBALS['cbxpetition_core'] = run_cbxpetition();