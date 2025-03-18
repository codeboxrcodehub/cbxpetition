<?php

namespace Cbx\Petition;

use Cbx\Petition\CBXPetitionAdmin;
use Cbx\Petition\CBXPetitionPublic;
use Cbx\Petition\ShortCode\ShortCode;
use Cbx\Petition\Helpers\PetitionHelper;


/**
 * Define all hooks
 * Class Hooks
 * @package Cbx\Petition
 */
class Hooks {
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
	 * @var ShortCode
	 * @since 1.0.0
	 */
	private $short_code;

	public function __construct() {
		$this->admin      = new CBXPetitionAdmin();
		$this->public     = new CBXPetitionPublic();
		$this->short_code = new ShortCode();


		$this->common_hooks();
		$this->admin_hooks();
		$this->public_hooks();
		$this->shortcodes();
	}//end constructor

	/**
	 * Common Hooks
	 * @since 1.0.0
	 */
	private function common_hooks() {
		//add_action( 'plugins_loaded', [ $this, 'load_plugin_textdomain' ] );
		add_action( 'init', [ $this, 'load_plugin_textdomain' ] );
		add_action( 'init', [ $this, 'load_mailer' ] );
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
		add_filter( 'plugin_action_links_' . CBXPETITION_BASE_NAME, [
			$admin,
			'plugin_action_links'
		] ); //plugin listing links: left side
		add_filter( 'plugin_row_meta', [ $admin, 'plugin_row_meta' ], 10, 4 ); //plugin listing links : right side
		add_action( 'activated_plugin', [ $admin, 'check_pro_addon' ] );
		//add_action('plugins_loaded', [$admin, 'check_pro_addon']);
		add_action( 'init', [ $admin, 'check_pro_addon' ] );
		add_action( 'after_plugin_row_cbxpetitionproaddon/cbxpetitionproaddon.php', [
			$admin,
			'custom_message_after_plugin_row_proaddon'
		], 10, 2 );

		//setting misc
		add_action( 'wp_ajax_cbxpetition_permalink_cache_clear', [ $admin, 'permalink_cache_clear' ] );
		add_action( 'admin_init', [ $admin, 'save_email_setting' ] );
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

		add_action( 'wp_ajax_cbxpetition_sign_submit', [ $public, 'petition_sign_submit' ] );
		add_action( 'wp_ajax_nopriv_cbxpetition_sign_submit', [ $public, 'petition_sign_submit' ] );

		add_action( 'wp_ajax_cbxpetition_load_more_signs', [ $public, 'petition_load_more_signs' ] );
		add_action( 'wp_ajax_nopriv_cbxpetition_load_more_signs', [ $public, 'petition_load_more_signs' ] );

		add_action( 'wp_enqueue_scripts', [ $public, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $public, 'enqueue_scripts' ] );

		//classic widgets
		add_action( 'widgets_init', [ $public, 'init_widgets' ] );    //widget register init


		//elementor widgets
		add_action( 'elementor/widgets/widgets_registered', [ $public, 'init_elementor_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $public, 'add_elementor_widget_categories' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $public, 'elementor_icon_loader' ], 99999 );

		//custom template include
		add_action( 'template_include', [ $public, 'include_custom_templates' ] );

		add_action( 'cbxpetition_single_content_after_title', [ $public, 'category_display_after_title' ] );
		add_action( 'cbxpetition_archive_loop_item_content_inside_start', [ $public, 'category_display_after_title' ] );
		add_action( 'cbxpetition_single_content_after_details', [ $public, 'tag_display_after_title' ] );
	}//end method public_hooks

	/**
	 * Short code hooks here
	 * @since 1.0.0
	 */
	private function shortcodes() {
		$shortcode = $this->short_code;

		add_action( 'init', [ $shortcode, 'init_shortcodes' ] );
	}//end method shortcodes

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'cbxpetition', false, CBXPETITION_ROOT_PATH . 'languages/' );
	}//end method load_plugin_textdomain

	/**
	 * register migration command
	 */
	public function load_mailer() {
		cbxpetition_mailer();
	} //end method load_mailer
}//end class Hooks