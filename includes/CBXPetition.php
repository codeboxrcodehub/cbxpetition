<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Cbx\Petition\Helpers\PetitionHelper;
use Cbx\Petition\CBXPetitionAdmin;
use Cbx\Petition\CBXPetitionPublic;
use Cbx\Petition\CBXPetitionShortCodes;

/**
 * Petition plugin main class file
 *
 * Class CBXPetition
 * @package Cbx\Petition
 */
final class CBXPetition {
	/**
	 * @var CBXPetitionAdmin
	 * @since 1.0.0
	 */
	private $admin;

	/**
	 * @var CBXPetitionPublic
	 * @since 1.0.0
	 */
	private $public;

	/**
	 * @var CBXPetitionShortCodes
	 * @since 1.0.0
	 */
	private $short_code;
	
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  2.0.0
	 */
	private static $instance = null;

	/**
	 * Singleton Instance.
	 *
	 * Ensures only one instance of CBXPetition is loaded or can be loaded.
	 *
	 * @return self Main instance.
	 * @since  2.0.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}//end method instance

	public function __construct() {

		if ( cbxpetition_compatible_php_version() ) {
			$GLOBALS['cbxpetition_loaded'] = true;

			$this->include_files();

			$this->admin      = new CBXPetitionAdmin();
			$this->public     = new CBXPetitionPublic();
			$this->short_code = new CBXPetitionShortCodes();

			$this->common_hooks();
			$this->admin_hooks();
			$this->public_hooks();
			$this->init_shortcodes();
		} else {
			add_action( 'admin_notices', [ $this, 'php_version_notice' ] );
		}
	}//end method constructor

	/**
	 * Autoload inaccessible or non-existing properties on demand.
	 *
	 * @param $key
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function __get( $key ) {
		if ( in_array( $key, [ 'mailer' ], true ) ) {
			return $this->$key();
		}
	}//end magic method get

	/**
	 * Set the value of an inaccessible or non-existing property.
	 *
	 * @param string $key Property name.
	 * @param mixed $value Property value.
	 * @since 2.0.0
	 */
	public function __set( string $key, $value ) {
		if ( property_exists( $this, $key ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error( 'Cannot access private property CBXPetition::$' . esc_html( $key ), E_USER_ERROR );
		} else {
			$this->$key = $value;
		}
	}//end magic mathod set

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __clone() {
		cbxpetition_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'cbxpetition' ), '2.0.0' );
	}//end method clone

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __wakeup() {
		cbxpetition_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'cbxpetition' ), '2.0.0' );
	}//end method wakeup


	/**
	 * Include necessary files
	 *
	 * @return void
	 */
	private function include_files() {
		require_once __DIR__ . '/../vendor/autoload.php';
		include_once __DIR__ . '/CBXPetitionEmails.php';
	}//end method include_files

	/**
	 * Email Class.
	 *
	 * @return CBXPetitionEmails
	 * @since 2.0.0
	 */
	public function mailer() {
		return CBXPetitionEmails::instance();
	}//end method mailer

	/**
	 * Common Hooks
	 * @since 1.0.0
	 */
	private function common_hooks() {
		$helper = new PetitionHelper();

		add_action( 'init', [ $helper, 'load_mailer' ] );
	}//end method common_hooks

	/**
	 * Admin hooks
	 * @since 1.0.0
	 */
	private function admin_hooks() {
		$admin = $this->admin;

		add_action( 'init', [ $admin, 'post_type_init' ] );
		add_action( 'admin_init', [ $admin, 'admin_init_misc' ] );
		add_action( 'admin_init', [ $admin, 'setting_init' ] );


		add_action( 'admin_menu', [ $admin, 'admin_menus' ] );
		add_filter( 'set-screen-option', [ $admin, 'cbxpetition_sign_results_per_page' ], 10, 3 );


		// add meta box and hook save meta box
		add_action( 'add_meta_boxes', [ $admin, 'meta_boxes_display' ] );
		add_action( 'save_post', [ $admin, 'petition_meta_save' ], 10, 2 );
		add_action( 'wp_ajax_cbxpetition_settings_reset_load', [ $admin, 'settings_reset_load' ] );
		add_action( 'wp_ajax_cbxpetition_settings_reset', [ $admin, 'plugin_options_reset' ] );

		add_filter( 'manage_cbxpetition_posts_columns', [
			$admin,
			'columns_header'
		] );                 // show or remove extra column
		add_action( 'manage_cbxpetition_posts_custom_column', [
			$admin,
			'custom_column_row'
		], 10, 2 );                                                                                        // modify column's row data to display
		add_filter( 'manage_edit-cbxpetition_sortable_columns', [ $admin, 'custom_column_sortable' ] );
		add_filter( 'post_row_actions', [ $admin, 'row_actions_petition_listing' ], 10, 2 );


		add_action( 'admin_enqueue_scripts', [ $admin, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $admin, 'enqueue_scripts' ] );

		//petition media(photos, banner) hooks
		add_action( 'wp_ajax_petition_admin_photo_upload', [ $admin, 'petition_admin_photo_upload' ] );    //admin  //ok
		add_action( 'wp_ajax_petition_admin_photo_delete', [ $admin, 'petition_admin_photo_delete' ] );    //admin //ok
		add_action( 'wp_ajax_petition_admin_photos_delete', [ $admin, 'petition_admin_photos_delete' ] );  //admin //ok

		add_action( 'wp_ajax_petition_admin_banner_upload', [ $admin, 'petition_admin_banner_upload' ] );//admin  //ok
		add_action( 'wp_ajax_petition_admin_banner_delete', [ $admin, 'petition_admin_banner_delete' ] );//admin //ok
		//end petition media(photos, banner) hooks

		//signature hooks
		add_filter( 'manage_cbxpetition_page_cbxpetitionsigns_columns', [ $admin, 'signature_listing_screen_cols' ] );
		add_action( 'wp_ajax_cbxpetition_sign_edit', [ $admin, 'petition_sign_edit' ] );
		add_action( 'wp_ajax_cbxpetition_sign_delete', [ $admin, 'petition_sign_delete' ] );

		add_action( 'delete_user', [ $admin, 'on_user_delete_sign_delete' ] );
		//end signature hooks

		//plugin upgrade and notice
		add_action( 'plugins_loaded', [ $admin, 'plugin_upgrader_process_complete' ] );
		add_action( 'admin_notices', [ $admin, 'plugin_activate_upgrade_notices' ] );
		add_filter( 'plugin_action_links_' . CBXPETITION_BASE_NAME, [	$admin, 'plugin_action_links'] );
		add_filter( 'plugin_row_meta', [ $admin, 'custom_plugin_row_meta' ], 10, 4 );
		add_action( 'activated_plugin', [ $admin, 'check_pro_addon' ] );
		add_action( 'init', [ $admin, 'check_pro_addon' ] );
		add_action( 'after_plugin_row_cbxpetitionproaddon/cbxpetitionproaddon.php', [
			$admin,
			'custom_message_after_plugin_row_proaddon'
		], 10, 2 );

		//setting misc
		add_action( 'wp_ajax_cbxpetition_permalink_cache_clear', [ $admin, 'permalink_cache_clear' ] );
		add_action( 'admin_init', [ $admin, 'save_email_setting' ] );

		//default category
		add_action('admin_init', [$admin, 'create_default_category']);
	}//end method admin_hooks

	/**
	 * Public hooks
	 * @since 1.0.0
	 */
	private function public_hooks() {
		$public = $this->public;

		add_filter( 'the_content', [ $public, 'auto_integration' ] ); //auto integration petition features


		add_filter( 'query_vars', [ $public, 'add_query_vars' ] );
		add_action( 'init', [ $public, 'rewrite_rules' ] );
		add_action( 'template_redirect', [ $public, 'guest_email_validation' ] );
		add_action( 'template_redirect', [ $public, 'signature_delete_handler' ] );

		add_action( 'wp_ajax_cbxpetition_sign_submit', [ $public, 'petition_sign_submit' ] );
		add_action( 'wp_ajax_nopriv_cbxpetition_sign_submit', [ $public, 'petition_sign_submit' ] );

		add_action( 'wp_ajax_cbxpetition_load_more_signs', [ $public, 'petition_load_more_signs' ] );
		add_action( 'wp_ajax_nopriv_cbxpetition_load_more_signs', [ $public, 'petition_load_more_signs' ] );

		//frontend signature delete by logged-in owner
		add_action( 'wp_ajax_cbxpetition_front_sign_delete', [ $public, 'petition_sign_delete_front' ] );

		add_action( 'wp_enqueue_scripts', [ $public, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $public, 'enqueue_scripts' ] );

		//classic widgets
		add_action( 'widgets_init', [ $public, 'init_widgets' ] );//widget register init

		//custom template include
		add_action( 'template_include', [ $public, 'include_custom_templates' ] );

		add_action( 'cbxpetition_single_content_after_title', [ $public, 'category_display_after_title' ] );
		add_action( 'cbxpetition_archive_loop_item_content_inside_start', [ $public, 'category_display_after_title' ] );
		add_action( 'cbxpetition_single_content_after_details', [ $public, 'tag_display_after_title' ] );
	}//end method public_hooks

	/**
	 * Short code hooks here
	 *
	 * @since 1.0.0
	 */
	private function init_shortcodes() {
		$shortcode = $this->short_code;

		add_action( 'init', [ $shortcode, 'init_shortcodes' ] );
	}//end method init_shortcodes

	/**
	 * Show php version notice in dashboard
	 *
	 * @return void
	 */
	public function php_version_notice() {
		echo '<div class="error"><p>';
		/* Translators:  PHP Version */
		echo sprintf(esc_html__( 'CBX Petition requires at least PHP %s. Please upgrade PHP to run CBX Petition.', 'cbxpetition' ), esc_attr(CBXPETITION_PHP_MIN_VERSION));
		echo '</p></div>';
	}//end method php_version_notice
}//end class CBXPetition