<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://codeboxr.com
 * @since             1.0.0
 * @package           CBXPetition
 *
 * @wordpress-plugin
 * Plugin Name:       CBX Petition
 * Plugin URI:        https://codeboxr.com/product/cbx-petition-for-wordpress/
 * Description:       A plugin to create, manage petition and collect signatures for petition
 * Version:           2.0.10
 * Author:            Codeboxr
 * Author URI:        https://codeboxr.com
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
defined( 'CBXPETITION_PLUGIN_VERSION' ) or define( 'CBXPETITION_PLUGIN_VERSION', '2.0.10' );
defined( 'CBXPETITION_BASE_NAME' ) or define( 'CBXPETITION_BASE_NAME', plugin_basename( __FILE__ ) );
defined( 'CBXPETITION_ROOT_PATH' ) or define( 'CBXPETITION_ROOT_PATH', plugin_dir_path( __FILE__ ) );
defined( 'CBXPETITION_ROOT_URL' ) or define( 'CBXPETITION_ROOT_URL', plugin_dir_url( __FILE__ ) );

defined( 'CBXPETITION_WP_MIN_VERSION' ) or define( 'CBXPETITION_WP_MIN_VERSION', '5.3' );
defined( 'CBXPETITION_PHP_MIN_VERSION' ) or define( 'CBXPETITION_PHP_MIN_VERSION', '7.4' );
defined( 'CBXPETITION_PRO_VERSION' ) or define( 'CBXPETITION_PRO_VERSION', '2.0.6' );

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
function cbxpetition_compatible_wp_version( $version = '' ) {
	if($version == '') $version = CBXPETITION_WP_MIN_VERSION;

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
function cbxpetition_compatible_php_version( $version = '' ) {
	if($version == '') $version = CBXPETITION_PHP_MIN_VERSION;

	if ( version_compare( PHP_VERSION, $version, '<' ) ) {
		return false;
	}

	return true;
}//end method cbxpetition_compatible_php_version


register_activation_hook( __FILE__, 'cbxpetition_activate' );
register_deactivation_hook( __FILE__, 'cbxpetition_deactivate' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cbxpetition-activator.php
 */
function cbxpetition_activate() {
	$wp_version  = CBXPETITION_WP_MIN_VERSION;
	$php_version = CBXPETITION_PHP_MIN_VERSION;

	$activate_ok = true;

	if ( ! cbxpetition_compatible_wp_version() ) {
		$activate_ok = false;

		deactivate_plugins( plugin_basename( __FILE__ ) );

		/* translators: WordPress version */
		wp_die( sprintf( esc_html__( 'CBX Petition plugin requires WordPress %s or higher!', 'cbxpetition' ), esc_attr($wp_version) ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	if ( ! cbxpetition_compatible_php_version() ) {
		$activate_ok = false;

		deactivate_plugins( plugin_basename( __FILE__ ) );

		/* translators: PHP version */
		wp_die( sprintf( esc_html__( 'CBX Petition plugin requires PHP %s or higher!', 'cbxpetition' ), esc_attr($php_version) ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	if($activate_ok){
		cbxpetition_core();
		PetitionHelper::role_cap_assignment();
		PetitionHelper::create_tables();
		PetitionHelper::create_pages();
		//PetitionHelper::create_default_categories(); //from V2.0.3

		set_transient( 'cbxpetition_flush_rewrite_rules', 1 );
		set_transient( 'cbxpetition_activated_notice', 1 );
		set_transient( 'cbxpetition_create_cats', 1 );
		update_option( 'cbxpetition_version', CBXPETITION_PLUGIN_VERSION );

		//deactivate pro addon if version than 2.0.0
		cbxpetition_check_and_deactivate_plugin( 'cbxpetitionproaddon/cbxpetitionproaddon.php', '2.0.0', 'cbxpetition_proaddon_deactivated' );
		
	}//end $activate_ok
}//end method cbxpetition_activate

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cbxpetition-deactivator.php
 */
function cbxpetition_deactivate() {
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
		$cbxpetition_core = cbxpetition_run();
	}

	return $cbxpetition_core;
}//end method cbxpetition_core


/**
 * Begins execution of the plugin.
 *
 * @since    2.0.0
 */
function cbxpetition_run() {
	return CBXPetition::instance();
}//end function cbxpetition_run


//load the plugin
$GLOBALS['cbxpetition_core'] = cbxpetition_run();