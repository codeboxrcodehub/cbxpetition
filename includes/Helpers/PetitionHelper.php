<?php
namespace Cbx\Petition\Helpers;
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Cbx\Petition\CBXSetting;

/**
 * Petition helper class
 */
class PetitionHelper {
	/**
	 * Petition manage role/cap assignment
	 *
	 * @return void
	 */
	public static function role_cap_assignment() {
		$role = get_role( 'administrator' );

		//who can manage or manage accounting capability
		if ( ! $role->has_cap( 'manage_cbxpetition' ) ) {
			$role->add_cap( 'manage_cbxpetition' );
		}
	}//end method role_cap_assignment

	/**
	 * Create tables
	 * @since 1.0.0
	 */
	public static function create_tables() {
		global $wpdb;


		$signature_table = $wpdb->prefix . 'cbxpetition_signs';

		//db table migration if exists
		$charset_collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


		//create petition_signs table
		$signature_table_sql = "CREATE TABLE $signature_table (
                          id bigint(11) unsigned NOT NULL AUTO_INCREMENT,
                          petition_id bigint(11) unsigned NOT NULL DEFAULT 0 COMMENT 'petition id',
                          f_name varchar(255) DEFAULT NULL COMMENT 'signer first name',
                          l_name varchar(255) DEFAULT NULL COMMENT 'signer last name',
                          email varchar(100) NOT NULL COMMENT 'signer email',
                          comment text DEFAULT NULL COMMENT 'signer comment about petition',
                          state varchar(30) NOT NULL DEFAULT 'pending' COMMENT 'sign condition',
                          activation VARCHAR(255) DEFAULT NULL COMMENT 'activation code',
                          delete_token VARCHAR(255) DEFAULT NULL COMMENT 'delete token for signature deletion via email link',
                          add_by bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'foreign key of user table. who added this, if uest zero',
                          mod_by bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'foreign key of user table. who last modify this list',
                          add_date datetime DEFAULT NULL COMMENT 'add date',
                          mod_date datetime DEFAULT NULL COMMENT 'last modified date',
                          PRIMARY KEY (id)
                        ) $charset_collate; ";

		dbDelta( [ $signature_table_sql ] );

	}//end method create_tables

	/**
	 * Get all  core tables list
	 */
	public static function getAllDBTablesList() {
		global $wpdb;

		$signature_table = $wpdb->prefix . 'cbxpetition_signs';

		//todo: are we using the table names hardcoded ?
		$table_names                         = [];
		$table_names['Signature List Table'] = $signature_table;


		return apply_filters( 'cbxpetition_table_list', $table_names );
	}//end getAllDBTablesList

	/**
	 * Create pages that the plugin relies on, storing page id's in variables.
	 */
	public static function create_pages() {
		$pages = apply_filters( 'cbxpetition_front_settings',
			[
				'user_dashboard_page' => [
					'slug'    => _x( 'cbxpetition-dashboard', 'Page slug', 'cbxpetition' ),
					'title'   => _x( 'Petition Dashboard', 'Page title', 'cbxpetition' ),
					'content' => '[cbxpetition_dashboard]',
				],
			] );

		foreach ( $pages as $key => $page ) {
			self::create_page( $key, esc_sql( $page['slug'] ), $page['title'], $page['content'] );
		}
	}//end cbxbookmark_create_pages

	/**
	 * Create a page and store the ID in an option.
	 *
	 * @param  string  $key
	 * @param  string  $slug
	 * @param  string  $page_title
	 * @param  string  $page_content
	 *
	 * @return int|string|WP_Error|null
	 */
	public static function create_page( $key = '', $slug = '', $page_title = '', $page_content = '' ) {
		global $wpdb;

		if ( $key == '' ) {
			return null;
		}
		if ( $slug == '' ) {
			return null;
		}

		$cbxpetition_front_settings = get_option( 'cbxpetition_front_settings' );
		if ( ! is_array( $cbxpetition_front_settings ) ) {
			$cbxpetition_front_settings = [];
		}

		$option_value = isset( $cbxpetition_front_settings[ $key ] ) ? intval( $cbxpetition_front_settings[ $key ] ) : 0;


		$page_id     = 0;
		$page_status = '';
		//if valid page id already exists
		if ( $option_value > 0 ) {
			$page_object = get_post( $option_value );

			if ( is_object( $page_object ) ) {
				//at least found a valid post
				$page_id     = $page_object->ID;
				$page_status = $page_object->post_status;

				if ( 'page' === $page_object->post_type && $page_object->post_status == 'publish' ) {

					return $page_id;
				}
			}
		}


		$page_id = absint( $page_id );
		if ( $page_id > 0 ) {
			//page found
			if ( $page_status == 'trash' ) {
				//if trashed then untrash it, it will be published automatically
				wp_untrash_post( $page_id );
			} else {

				$page_data = [
					'ID'          => $page_id,
					'post_status' => 'publish',
				];

				wp_update_post( $page_data );
			}

		} else {
			//search by slug for non trashed and then trashed, then if not found create one

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( ( $page_id = intval( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'page' AND post_status != 'trash' AND post_name = %s LIMIT 1;",
					$slug ) ) ) ) > 0 ) {

				//non trashed post found by slug
				//page found but not publish, so publish it
				//$page_id   = $page_found_by_slug;
				$page_data = [
					'ID'          => $page_id,
					'post_status' => 'publish',
				];
				wp_update_post( $page_data );
			} elseif ( ( $page_id = intval( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$slug . '__trashed' ) ) ) ) > 0 ) {

				//trash post found and unstrash/publish it
				wp_untrash_post( $page_id );
			} else {
				$page_data = [
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'post_title'     => $page_title,
					'post_name'      => $slug,
					'post_content'   => $page_content,
					'comment_status' => 'closed'
				];

				$page_id = wp_insert_post( $page_data );
			}
		}

		//let's update the option
		if ( is_numeric( $page_id ) ) {
			$cbxpetition_front_settings[ $key ] = $page_id;
		}
		update_option( 'cbxpetition_front_settings', $cbxpetition_front_settings );

		return $page_id;
	}//end create_page

	/**
	 * Create petition custom post type, taxonomies
	 * @since 1.0.0
	 */
	public static function create_cbxpetition_post_type() {
		$settings = new CBXSetting();

		//post slugs
		$post_slug_default    = esc_attr( $settings->get_field( 'post_slug', 'cbxpetition_basic', 'cbxpetition' ) );
		$archive_slug_default = esc_attr( $settings->get_field( 'archive_slug', 'cbxpetition_basic', 'cbxpetitions' ) );

		$post_slug    = apply_filters( 'cbxpetition_post_slug', $post_slug_default );
		$archive_slug = apply_filters( 'cbxpetition_archive_slug', $archive_slug_default );


		$labels = [
			'name'              => _x( 'Petitions', 'Post Type General Name', 'cbxpetition' ),
			'singular_name'     => _x( 'Petition', 'Post Type Singular Name', 'cbxpetition' ),
			'menu_name'         => esc_html__( 'Petitions', 'cbxpetition' ),
			'parent_item_colon' => esc_html__( 'Parent Item:', 'cbxpetition' ),
			'all_items'         => esc_html__( 'Petitions', 'cbxpetition' ),
			'view_item'         => esc_html__( 'View Petition', 'cbxpetition' ),
			'add_new_item'      => esc_html__( 'Create Petition', 'cbxpetition' ),
			'add_new'           => esc_html__( 'Create Petition', 'cbxpetition' ),
			'edit_item'         => esc_html__( 'Edit Petition', 'cbxpetition' ),
			'update_item'       => esc_html__( 'Update Petition', 'cbxpetition' ),
			'search_items'      => esc_html__( 'Search Petition', 'cbxpetition' ),
		];

		$args = [
			'label'               => esc_html__( 'Petition', 'cbxpetition' ),
			'description'         => esc_html__( 'Petition', 'cbxpetition' ),
			'labels'              => apply_filters( 'cbxpetition_post_type_labels', $labels ),
			'supports'            => [ 'title', 'editor', 'thumbnail' ],
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_icon'           => CBXPETITION_ROOT_URL . 'assets/images/menu_icon.svg',
			'can_export'          => true,
			//'has_archive'         => true,
			'has_archive'         => $archive_slug,
			'rewrite'             => [
				'slug' => $post_slug
			],
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
		];
		register_post_type( 'cbxpetition', apply_filters( 'cbxpetition_post_type_args', $args ) );


		// register cbxpetition_cat taxonomy
		$cat_enable       = $settings->get_field( 'cat_enable', 'cbxpetition_basic', 'on' );
		$cat_slug_default = $settings->get_field( 'cat_slug', 'cbxpetition_basic', esc_attr_x( 'petition-cat', 'Petition category slug', 'cbxpetition' ) );
		$cat_slug         = apply_filters( 'cbxpetition_category_slug', $cat_slug_default );

		if ( $cat_enable == 'on' ) {
			$petition_cat_labels = [
				'name'                       => _x( 'Categories', 'Taxonomy General Name', 'cbxpetition' ),
				'singular_name'              => _x( 'Category', 'Taxonomy Singular Name', 'cbxpetition' ),
				'menu_name'                  => esc_html__( 'Categories', 'cbxpetition' ),
				'all_items'                  => esc_html__( 'All Categories', 'cbxpetition' ),
				'parent_item'                => esc_html__( 'Parent Category', 'cbxpetition' ),
				'parent_item_colon'          => esc_html__( 'Parent Category:', 'cbxpetition' ),
				'new_item_name'              => esc_html__( 'New Category Name', 'cbxpetition' ),
				'add_new_item'               => esc_html__( 'Add New Category', 'cbxpetition' ),
				'edit_item'                  => esc_html__( 'Edit Category', 'cbxpetition' ),
				'update_item'                => esc_html__( 'Update Category', 'cbxpetition' ),
				'view_item'                  => esc_html__( 'View Category', 'cbxpetition' ),
				'separate_items_with_commas' => esc_html__( 'Separate Categories with commas', 'cbxpetition' ),
				'add_or_remove_items'        => esc_html__( 'Add or remove Categories', 'cbxpetition' ),
				'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'cbxpetition' ),
				'popular_items'              => esc_html__( 'Popular Categories', 'cbxpetition' ),
				'search_items'               => esc_html__( 'Search Categories', 'cbxpetition' ),
				'not_found'                  => esc_html__( 'Not Found', 'cbxpetition' ),
				'no_terms'                   => esc_html__( 'No Categories', 'cbxpetition' ),
				'items_list'                 => esc_html__( 'Categories list', 'cbxpetition' ),
				'items_list_navigation'      => esc_html__( 'Categories list navigation', 'cbxpetition' ),
			];

			$petition_cat_args = [
				'labels'            => apply_filters( 'cbxpetition_post_tax_category_labels', $petition_cat_labels ),
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_tagcloud'     => true,
				'rewrite'           => [
					'slug' => $cat_slug
				]
			];
			register_taxonomy( 'cbxpetition_cat', [ 'cbxpetition' ], apply_filters( 'cbxpetition_post_tax_category_args', $petition_cat_args ) );
		}


		// register cbxpetition_tag taxonomy
		$tag_enable       = $settings->get_field( 'tag_enable', 'cbxpetition_basic', 'on' );
		$tag_slug_default = $settings->get_field( 'tag_slug', 'cbxpetition_basic', esc_attr_x( 'petition-tag', 'Petition tag slug', 'cbxpetition' ) );
		$tag_slug         = apply_filters( 'cbxpetition_tag_slug', $tag_slug_default );

		if ( $tag_enable == 'on' ) {
			$petition_tag_labels = [
				'name'                       => _x( 'Tags', 'Taxonomy General Name', 'cbxpetition' ),
				'singular_name'              => _x( 'Tag', 'Taxonomy Singular Name', 'cbxpetition' ),
				'menu_name'                  => esc_html__( 'Tags', 'cbxpetition' ),
				'all_items'                  => esc_html__( 'All Tags', 'cbxpetition' ),
				'parent_item'                => esc_html__( 'Parent Tag', 'cbxpetition' ),
				'parent_item_colon'          => esc_html__( 'Parent Tag:', 'cbxpetition' ),
				'new_item_name'              => esc_html__( 'New Tag Name', 'cbxpetition' ),
				'add_new_item'               => esc_html__( 'Add New Tag', 'cbxpetition' ),
				'edit_item'                  => esc_html__( 'Edit Tag', 'cbxpetition' ),
				'update_item'                => esc_html__( 'Update Tag', 'cbxpetition' ),
				'view_item'                  => esc_html__( 'View Tag', 'cbxpetition' ),
				'separate_items_with_commas' => esc_html__( 'Separate Tags with commas', 'cbxpetition' ),
				'add_or_remove_items'        => esc_html__( 'Add or remove Tags', 'cbxpetition' ),
				'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'cbxpetition' ),
				'popular_items'              => esc_html__( 'Popular Tags', 'cbxpetition' ),
				'search_items'               => esc_html__( 'Search Tags', 'cbxpetition' ),
				'not_found'                  => esc_html__( 'Not Found', 'cbxpetition' ),
				'no_terms'                   => esc_html__( 'No Tags', 'cbxpetition' ),
				'items_list'                 => esc_html__( 'Tags list', 'cbxpetition' ),
				'items_list_navigation'      => esc_html__( 'Tags list navigation', 'cbxpetition' ),
			];

			$petition_tag_args = [
				'labels'            => apply_filters( 'cbxpetition_post_tax_tag_labels', $petition_tag_labels ),
				'hierarchical'      => false,
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_tagcloud'     => true,
				'rewrite'           => [
					'slug' => $tag_slug
				]
			];

			register_taxonomy( 'cbxpetition_tag', [ 'cbxpetition' ], apply_filters( 'cbxpetition_post_tax_tag_args', $petition_tag_args ) );
		}
	}//end method create_cbxpetition_post_type

	/**
	 * HTML elements, attributes, and attribute values will occur in your output
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function allowedHtmlTags() {
		$allowed_html_tags = [
			'a'      => [
				'href'  => [],
				'title' => [],
				//'class' => array(),
				//'data'  => array(),
				//'rel'   => array(),
			],
			'br'     => [],
			'em'     => [],
			'ul'     => [//'class' => array(),
			],
			'ol'     => [//'class' => array(),
			],
			'li'     => [//'class' => array(),
			],
			'strong' => [],
			'p'      => [
				//'class' => array(),
				//'data'  => array(),
				//'style' => array(),
			],
			'span'   => [
				//					'class' => array(),
				//'style' => array(),
			],
		];

		return apply_filters( 'cbxpetition_allowed_html_tags', $allowed_html_tags );
	}//end method allowedHtmlTags

	/**
	 * Get user display name
	 *
	 * @param  null  $user_id
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function userDisplayName( $user_id = null ) {
		$current_user      = $user_id ? new \WP_User( $user_id ) : wp_get_current_user();
		$user_display_name = $current_user->display_name;
		if ( $user_display_name != '' ) {
			return $user_display_name;
		}

		if ( $current_user->first_name ) {
			if ( $current_user->last_name ) {
				return $current_user->first_name . ' ' . $current_user->last_name;
			}

			return $current_user->first_name;
		}

		return esc_html__( 'Unnamed', 'cbxpetition' );
	}//end method userDisplayName

	/**
	 * Get user display name alternative if display_name value is empty
	 *
	 * @param $current_user
	 * @param $user_display_name
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function userDisplayNameAlt( $current_user, $user_display_name = '' ) {
		if ( $user_display_name != '' ) {
			return $user_display_name;
		}

		if ( $current_user->first_name ) {
			if ( $current_user->last_name ) {
				return $current_user->first_name . ' ' . $current_user->last_name;
			}

			return $current_user->first_name;
		}

		return esc_html__( 'Unnamed', 'cbxpetition' );
	}//end method userDisplayNameAlt

	/**
	 * @param  int  $petition_id
	 * @param  int  $user_id
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function isPetitionSignedByUser( $petition_id = 0, $user_id = 0 ) {
		$petition_id = absint( $petition_id );
		$user_id     = absint( $user_id );

		if ( $petition_id == 0 || $user_id == 0 ) {
			return false;
		}

		global $wpdb;

		$signature_table = $wpdb->prefix . 'cbxpetition_signs';
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$sql = $wpdb->prepare( "SELECT * FROM $signature_table WHERE petition_id=%d AND add_by=%d", $petition_id, $user_id );

		//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$log_info = $wpdb->get_row( $sql,
			ARRAY_A );
		if ( is_null( $log_info ) ) {
			return false;
		}

		return true;
	}//end method isPetitionSignedByUser

	/**
	 * Is petition signe by guest user by email
	 *
	 * @param  int  $petition_id
	 * @param  string  $email
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function isPetitionSignedByGuest( $petition_id = 0, $email = '' ) {
		$petition_id = absint( $petition_id );
		$email       = sanitize_email( $email );

		if ( $petition_id == 0 || $email == '' ) {
			return false;
		}

		if ( ! is_email( $email ) ) {
			return false;
		}

		global $wpdb;

		$signature_table = $wpdb->prefix . 'cbxpetition_signs';
		//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$sql      = $wpdb->prepare( "SELECT * FROM $signature_table WHERE petition_id=%d AND email=%s", $petition_id, $email );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$log_info = $wpdb->get_row( $sql,
			ARRAY_A );

		if ( is_null( $log_info ) ) {
			return false;
		}

		return true;
	}//end method isPetitionSignedByGuest

	/**
	 * Return human readable date using custom format
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function dateShowingFormat( $date ) {
		return gmdate( 'M j, Y', strtotime( $date ) );
	}//end method dateShowingFormat

	/**
	 * Return human readable time using custom format
	 *
	 * @param $date
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function timeShowingFormat( $date ) {
		return gmdate( 'h:i a', strtotime( $date ) );
	}//end method timeShowingFormat

	/**
	 * Return human readable date using custom format
	 *
	 * @param $date
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function datetimeShowingFormat( $date ) {
		if ( $date == null ) {
			return '';
		}

		return gmdate( 'M j, Y h:i a', strtotime( $date ) );
	}//end method datetimeShowingFormat

	/**
	 * @return array
	 * @since 1.0.0
	 */
	public static function allTablesArr() {
		$all_tables_arr = [
			'petition_signs' => 'Signature Table',
		];

		return apply_filters( 'cbxpetition_table_names', $all_tables_arr );
	}//end method allTablesArr

	/**
	 * List all global option name with prefix cbxpetition_
	 * @since 1.0.0
	 */
	public static function getAllOptionNames() {
		/*global $wpdb;

		$prefix       = 'cbxpetition_';
		$option_names = $wpdb->get_results( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE '{$prefix}%'",
			ARRAY_A );

		return apply_filters( 'cbxpetition_option_names', $option_names );*/


		global $wpdb;

		$prefix = 'cbxpetition_';

		$wild = '%';
		$like = $wpdb->esc_like( $prefix ) . $wild;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$option_names = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s", $like ), ARRAY_A );

		return apply_filters( 'cbxpetition_option_names', $option_names );
	}//end method getAllOptionNames


	/**
	 * Returns all petition sign states
	 * @return array
	 * @since 1.0.0
	 */
	public static function getPetitionSignStates() {

		$states = [
			'unverified' => esc_html__( 'Unverified', 'cbxpetition' ),
			'pending'    => esc_html__( 'Pending', 'cbxpetition' ),
			'approved'   => esc_html__( 'Approved', 'cbxpetition' ),
			'unapproved' => esc_html__( 'Unapproved', 'cbxpetition' ),
		];

		return apply_filters( 'cbxpetition_sign_state', $states );
	}

	/**
	 * Return petition sign state value corresponding key
	 *
	 * @param  string  $state_key
	 *
	 * @return mixed|string
	 * @since 1.0.0
	 */
	public static function getPetitionSignState( $state_key = '' ) {
		$state = '';
		if ( $state_key != '' ) {
			$states = self::getPetitionSignStates();

			if ( is_array( $states ) && sizeof( $states ) > 0 ) {
				$state = isset( $states[ $state_key ] ) ? $states[ $state_key ] : $state_key;
			}
		}

		return $state;
	}//end method getPetitionSignState

	/**
	 * return all published petition
	 * @return array|null|object
	 * @since 1.0.0
	 */
	public static function getAllPetitions() {
		global $post;

		$all_petitions = [];

		$args = [
			'posts_per_page' => - 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_type'      => 'cbxpetition',
			'post_status'    => 'publish',

		];

		$myposts = get_posts( $args );

		foreach ( $myposts as $post ) : setup_postdata( $post );
			$post_id                   = get_the_ID();
			$post_title                = get_the_title();
			$all_petitions[ $post_id ] = $post_title;
		endforeach;

		wp_reset_postdata();

		return $all_petitions;
	}//end method getAllPetitions

	/**
	 * Get petition sign Data
	 *
	 * @param  string  $search
	 * @param  int  $petition_id
	 * @param  int  $user_id
	 * @param  string  $state
	 * @param  string  $order
	 * @param  string  $order_by
	 * @param  int  $per_page
	 * @param  int  $page
	 *
	 * @return array|null|object
	 * @since 1.0.0
	 */
	public static function getSignListingData( $search = '', $petition_id = 0, $user_id = 0, $state = 'all', $order = 'DESC', $order_by = 'id', $per_page = 20, $page = 1 ) {
		global $wpdb;


		$sortable_keys = array_values( cbxpetition_signature_get_sortable_keys() );
		if ( ! in_array( $order_by, $sortable_keys ) ) {
			$order_by = 'id';
		}

		$order      = strtoupper( $order );
		$order_keys = cbxpetition_get_order_keys();

		if ( ! in_array( $order, $order_keys ) ) {
			$order = 'DESC';
		}

		$signature_table = $wpdb->prefix . 'cbxpetition_signs';

		$sql_select = $join = $where_sql = $sortingOrder = '';
		$sql_select = "SELECT * FROM $signature_table as signs";

		if ( $search != '' ) {
			if ( $where_sql != '' ) {
				$where_sql .= ' AND ';
			}
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQueryWithPlaceholder
			$where_sql .= $wpdb->prepare( " (f_name LIKE '%%%s%%' OR l_name LIKE '%%%s%%' OR email LIKE '%%%s%%' OR comment LIKE '%%%s%%' )",
				$search,
				$search,
				$search,
				$search );
		}

		if ( intval( $petition_id ) > 0 ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'petition_id=%d', intval( $petition_id ) );
		}

		if ( intval( $user_id ) > 0 ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'add_by=%d', intval( $user_id ) );
		}

		if ( $state !== 'all' ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'state=%s', $state );
		}

		if ( $where_sql == '' ) {
			$where_sql = '1';
		}

		$limit_sql = '';

		if ( $per_page != - 1 ) {
			$start_point = ( $page * $per_page ) - $per_page;
			$limit_sql   = "LIMIT";
			$limit_sql   .= ' ' . $start_point . ',';
			$limit_sql   .= ' ' . $per_page;
		}


		$sortingOrder = " ORDER BY $order_by $order ";

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		return $wpdb->get_results( "$sql_select  WHERE  $where_sql $sortingOrder $limit_sql", 'ARRAY_A' );
	}//end method getSignListingData

	/**
	 * Get total petition sign data count
	 *
	 * @param  string  $search
	 * @param  int  $petition_id
	 * @param  int  $user_id
	 * @param  string  $state
	 * @param  int  $per_page
	 * @param  int  $page
	 *
	 * @return null|string
	 * @since 1.0.0
	 */
	public static function getSignListingDataCount( $search = '', $petition_id = 0, $user_id = 0, $state = 'all', $per_page = 20, $page = 1 ) {
		global $wpdb;


		$signature_table = $wpdb->prefix . 'cbxpetition_signs';

		$sql_select = $join = $where_sql = $sortingOrder = '';

		$sql_select = "SELECT COUNT(*) FROM $signature_table as signs";

		if ( $search != '' ) {
			if ( $where_sql != '' ) {
				$where_sql .= ' AND ';
			}
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQueryWithPlaceholder
			$where_sql .= $wpdb->prepare( " (f_name LIKE '%%%s%%' OR l_name LIKE '%%%s%%' OR email LIKE '%%%s%%' OR comment LIKE '%%%s%%' )",
				$search,
				$search,
				$search,
				$search );
		}

		if ( intval( $petition_id ) > 0 ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'petition_id=%d', intval( $petition_id ) );
		}

		if ( intval( $user_id ) > 0 ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'add_by=%d', intval( $user_id ) );
		}

		if ( $state !== 'all' ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'state=%s', $state );
		}

		if ( $where_sql == '' ) {
			$where_sql = '1';
		}

		//$sortingOrder = " ORDER BY $order_by $order ";
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		return $wpdb->get_var( "$sql_select $join  WHERE  $where_sql" );
	}//end method getSignListingDataCount

	/**
	 * petitions to expire for widget
	 *
	 * @param  int  $per_page
	 *
	 * @return int[]|\WP_Post[]
	 * @since 1.0.0
	 */
	public static function petitionsToExpire( $per_page = 10 ) {
		//phpcs:disable
		$args = [
			'post_type'      => 'cbxpetition',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'meta_key'       => '_cbxpetition_expire_date',
			'orderby'        => '_cbxpetition_expire_date',
			'order'          => 'ASC',
			'meta_query'     => [
				[
					'key'     => '_cbxpetition_expire_date',
					//						'value'   => date( 'Y-m-d', strtotime( "+7 days" ) ),
					'value'   => gmdate( 'Y-m-d H:i:s' ),
					'compare' => '>=',
				],
			],
		];
		//phpcs:enable

		return get_posts( $args );
	}//end method petitionsToExpire

	/**
	 * petitions that are recently completed
	 *
	 * @param  int  $per_page
	 *
	 * @return int[]|\WP_Post[]
	 * @since 1.0.0
	 */
	public static function completedPetitions( $per_page = 10 ) {
		//phpcs:disable
		$args = [
			'post_type'      => 'cbxpetition',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'meta_key'       => '_cbxpetition_expire_date',
			'orderby'        => '_cbxpetition_expire_date',
			'order'          => 'ASC',
			'meta_query'     => [
				[
					'key'     => '_cbxpetition_expire_date',
					'value'   => gmdate( 'Y-m-d' ),
					'compare' => '<',
				],
			],
		];
		//phpcs:enable

		return get_posts( $args );
	}//end method

	/**
	 * Petition Info
	 *
	 * @param $sign_id
	 *
	 * @return array|null|object|void
	 * @since 1.0.0
	 */
	public static function petitionSignInfo( $sign_id ) {
		global $wpdb;
		$signature_table = $wpdb->prefix . 'cbxpetition_signs';
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = $wpdb->prepare( "SELECT * FROM $signature_table WHERE id=%d ", intval( $sign_id ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		return $wpdb->get_row( $sql, ARRAY_A );
	}//end method petitionSignInfo

	/**
	 * get single petition expire date
	 *
	 * @param  int  $petition_id
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function petitionExpireDate( $petition_id = 0 ) {
		$expire_date = [];

		$petition_id = intval( $petition_id );
		if ( $petition_id > 0 ) {
			$expire_date = get_post_meta( $petition_id, '_cbxpetition_expire_date', true );
			$expire_date = self::dateShowingFormat( $expire_date );
		}

		return $expire_date;
	}//end method petitionExpireDate

	/**
	 * get single petition media info data arr
	 *
	 * @param  int  $petition_id
	 *
	 * @return array|mixed
	 * @since 1.0.0
	 */
	public static function petitionMediaInfo( $petition_id = 0 ) {
		$media_info = [];

		$petition_id = intval( $petition_id );
		if ( $petition_id > 0 ) {
			$media_info = get_post_meta( $petition_id, '_cbxpetition_media_info', true );
		}

		return $media_info;
	}//end method petitionMediaInfo

	/**
	 * get single petition banner image
	 *
	 * @param  int  $petition_id
	 *
	 * @return mixed|string
	 * @since 1.0.0
	 */
	public static function petitionBannerImage( $petition_id = 0 ) {
		$banner = '';

		$petition_id = intval( $petition_id );
		if ( $petition_id > 0 ) {
			$media_info = self::petitionMediaInfo( $petition_id );
			$banner     = isset( $media_info['banner-image'] ) ? sanitize_text_field( $media_info['banner-image'] ) : '';
			if ( $banner != '' ) {
				$dir_info = self::checkUploadDir();
				$banner   = $dir_info['cbxpetition_base_url'] . $petition_id . '/' . $banner;
			}
		}

		return $banner;
	}//end method petitionBannerImage

	/**
	 * get single petition signature target
	 *
	 * @param  int  $petition_id
	 *
	 * @return int|mixed|string
	 * @since 1.0.0
	 */
	public static function petitionSignatureTarget( $petition_id = 0 ) {
		$signature_target = 0;

		$petition_id = intval( $petition_id );

		if ( $petition_id > 0 ) {
			$signature_target = intval( get_post_meta( $petition_id, '_cbxpetition_signature_target', true ) );
		}

		return $signature_target;
	}//end method petitionSignatureTarget

	/**
	 * get single petition video info
	 *
	 * @param  int  $petition_id
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function petitionVideoInfo( $petition_id = 0 ) {
		$video_info  = [];
		$petition_id = intval( $petition_id );
		if ( $petition_id > 0 ) {
			$media_info = self::petitionMediaInfo( $petition_id );

			if ( ! is_array( $media_info ) ) {
				$media_info = [];
			}

			$video_info['video-url']         = isset( $media_info['video-url'] ) ? $media_info['video-url'] : '';
			$video_info['video-title']       = isset( $media_info['video-title'] ) ? $media_info['video-title'] : '';
			$video_info['video-description'] = isset( $media_info['video-description'] ) ? $media_info['video-description'] : '';
		}

		return $video_info;
	}//end method petitionVideoInfo

	/**
	 * get single petition photos
	 *
	 * @param  int  $petition_id
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function petitionPhotos( $petition_id = 0 ) {
		$petition_photos = [];

		$petition_id = intval( $petition_id );
		if ( $petition_id > 0 ) {
			$media_info = self::petitionMediaInfo( $petition_id );
			if ( ! is_array( $media_info ) ) {
				$media_info = [];
			}

			$petition_photos = isset( $media_info['petition-photos'] ) ? $media_info['petition-photos'] : [];
			if ( ! is_array( $petition_photos ) ) {
				$petition_photos = [];
			}
		}

		return $petition_photos;
	}//end method petitionPhotos

	/**
	 * get single petition letter info
	 *
	 * @param  int  $petition_id
	 *
	 * @return array|mixed
	 * @since 1.0.0
	 */
	public static function petitionLetterInfo( $petition_id = 0 ) {
		$letter_info = [];

		$petition_id = intval( $petition_id );
		if ( $petition_id > 0 ) {
			$letter_info = get_post_meta( $petition_id, '_cbxpetition_letter', true );
		}

		return $letter_info;
	}//end method petitionLetterInfo

	/**
	 * get single petition letter
	 *
	 * @param  int  $petition_id
	 *
	 * @return mixed|string
	 * @since 1.0.0
	 */
	public static function petitionLetter( $petition_id = 0 ) {
		$petition_letter = '';

		$petition_id = intval( $petition_id );
		if ( $petition_id > 0 ) {
			$letter_info = self::petitionLetterInfo( $petition_id );

			if ( is_array( $letter_info ) && sizeof( $letter_info ) > 0 ) {
				if ( isset( $letter_info['letter'] ) ) {
					$petition_letter = $letter_info['letter'];
				}
			}
		}

		return $petition_letter;
	}//end method petitionLetter

	/**
	 * get single petition recipients
	 *
	 * @param  int  $petition_id
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function petitionRecipients( $petition_id = 0 ) {
		$petition_recipients = [];

		$petition_id = intval( $petition_id );
		if ( $petition_id > 0 ) {
			$letter_info = self::petitionLetterInfo( $petition_id );

			$petition_recipients = $letter_info['recipients'];
		}

		return $petition_recipients;
	}//end method petitionRecipients

	/**
	 * get single petition signature count
	 *
	 * @param  int  $petition_id
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public static function petitionSignatureCount( $petition_id = 0 ) {
		$signature_count = 0;

		$petition_id = intval( $petition_id );

		if ( $petition_id > 0 ) {
			global $wpdb;
			$signature_table = $wpdb->prefix . 'cbxpetition_signs';

			$sql_select = "SELECT COUNT(*) FROM $signature_table as signs";

			$where_sql = $wpdb->prepare( "petition_id=%d AND state=%s", $petition_id, 'approved' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$signature_count = $wpdb->get_var( " $sql_select WHERE  $where_sql" );
		}

		return intval( $signature_count );
	}//end method petitionSignatureCount

	/**
	 * get single petition signature count ratio
	 *
	 * @param  int  $petition_id
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public static function petitionSignatureTargetRatio( $petition_id = 0 ) {
		$ratio = 0;

		$petition_id = intval( $petition_id );
		if ( $petition_id > 0 ) {
			$target          = absint( self::petitionSignatureTarget( $petition_id ) );
			$signature_count = absint( self::petitionSignatureCount( $petition_id ) );

			if ( $target > 0 && $signature_count > 0 ) {
				$ratio = ( $signature_count / $target ) * 100;
				$ratio = number_format( $ratio, 2 );
			}
		}

		return $ratio;
	}//end method petitionSignatureTargetRatio

	/**
	 * Get all the pages
	 *
	 * @param $show_empty
	 *
	 * @return array
	 */
	public static function get_pages( $show_empty = false ) {
		$pages         = get_pages();
		$pages_options = [];

		if ( $show_empty ) {
			$pages_options[0] = esc_html__( 'Select page', 'cbxpetition' );
		}

		if ( $pages ) {
			foreach ( $pages as $page ) {
				$pages_options[ $page->ID ] = $page->post_title;
			}
		}


		return $pages_options;
	}//end method get_pages

	/**
	 * Get the user roles for voting purpose
	 *
	 * @param  bool  $plain
	 * @param  bool  $include_guest
	 * @param  array  $ignore
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function user_roles( $plain = true, $include_guest = false, $ignore = [] ) {
		global $wp_roles;

		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/user.php' );

		}

		$userRoles = [];
		if ( $plain ) {
			foreach ( get_editable_roles() as $role => $roleInfo ) {
				if ( in_array( $role, $ignore ) ) {
					continue;
				}
				$userRoles[ $role ] = $roleInfo['name'];
			}
			if ( $include_guest ) {
				$userRoles['guest'] = esc_html__( "Guest", 'cbxpetition' );
			}
		} else {
			//optgroup
			$userRoles_r = [];
			foreach ( get_editable_roles() as $role => $roleInfo ) {
				if ( in_array( $role, $ignore ) ) {
					continue;
				}
				$userRoles_r[ $role ] = $roleInfo['name'];
			}

			$userRoles = [
				'Registered' => $userRoles_r,
			];

			if ( $include_guest ) {
				$userRoles['Anonymous'] = [
					'guest' => esc_html__( "Guest", 'cbxpetition' ),
				];
			}
		}

		return apply_filters( 'cbxpetition_userroles', $userRoles, $plain, $include_guest );
	}//end method user_roles

	/**
	 * count user petition post
	 *
	 * @param  int  $user_id
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public static function count_user_petition_posts( $user_id = 0 ) {
		$user_id = intval( $user_id );
		if ( $user_id == 0 ) {
			return 0;
		}

		$count = 0;

		global $wpdb;

		$where = " WHERE ( ( post_type = 'cbxpetition' AND ( post_status = 'publish' OR post_status = 'pending' OR post_status = 'draft' ) ) ) AND post_author = %d ";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts $where", $user_id ) );


		return intval( $count );
	}//end method count_user_cbxpetition_posts

	/**
	 * paginate_links_as_bootstrap()
	 * JPS 20170330
	 * Wraps paginate_links data in Twitter bootstrap pagination component
	 *
	 * @param  array  $args  {
	 *                         Optional. {@see 'paginate_links'} for native argument list.
	 *
	 * @type string $nav_class classes for <nav> element. Default empty.
	 * @type string $ul_class additional classes for <ul.pagination> element. Default empty.
	 * @type string $li_class additional classes for <li> elements.
	 * }
	 * @return array|string|void String of page links or array of page links.
	 * @since 1.0.0
	 */
	public static function paginate_links_as_bootstrap( $args = '' ) {
		$args['type'] = 'array';
		$defaults     = [
			'nav_class' => '',
			'ul_class'  => '',
			'li_class'  => '',
		];

		$args       = wp_parse_args( $args, $defaults );
		$page_links = paginate_links( $args );


		if ( $page_links ) {
			$r         = '';
			$nav_class = empty( $args['nav_class'] ) ? '' : 'class="' . $args['nav_class'] . '"';
			$ul_class  = empty( $args['ul_class'] ) ? '' : ' ' . $args['ul_class'];

			//$r .= '<nav '. $nav_class .' aria-label="navigation">' . "\n\t";
			$r .= '<div ' . $nav_class . ' aria-label="navigation">' . "\n\t";

			$r .= '<ul class="cbxpetition-pagination ' . $ul_class . '">' . "\n";
			foreach ( $page_links as $link ) {
				$li_classes = explode( " ", $args['li_class'] );
				strpos( $link, 'current' ) !== false ? array_push( $li_classes, 'active' ) : ( strpos( $link, 'dots' ) !== false ? array_push( $li_classes, 'disabled' ) : '' );
				$class = empty( $li_classes ) ? '' : 'class="' . join( " ", $li_classes ) . '"';
				$r     .= "\t\t" . '<li ' . $class . '>' . $link . '</li>' . "\n";
			}

			$r .= "\t</ul>";
			$r .= "\n</div>";

			return '<div class="clearfix"></div><div class="cbxpetition-pagination-wrap">' . $r . '</div><div class="clearfix"></div>';
		}
	}//end method paginate_links_as_bootstrap

	/**
	 * Check recipient
	 *
	 * @param $recipients
	 *
	 * @return array|null
	 * @since 1.0.0
	 */
	public static function recipient_checkRecipient( $recipients ) {
		$new_recipients = [];
		foreach ( $recipients as $recipient ) {
			if ( ( isset( $recipient['name'] ) && $recipient['name'] != '' ) || ( isset( $recipient['designation'] ) && $recipient['designation'] ) != '' ) {
				array_push( $new_recipients, $recipient );
			}
		}

		if ( sizeof( $new_recipients ) > 0 ) {
			return $new_recipients;
		} else {
			return [];
		}
	}//end method recipient_checkRecipient

	/**
	 * Email type formats
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public static function email_type_formats() {
		return apply_filters( 'cbxpetition_email_type_formats',
			[
				'html'  => esc_html__( 'Rich Html Email', 'cbxpetition' ),
				//'multipart' => esc_html__( 'Rich Html with Attachment', 'cbxpetition' ),
				'plain' => esc_html__( 'Plain Text', 'cbxpetition' ),
			] );
	}//end method email_type_formats

	/**
	 * All public styles enqueue in cbxpetition
	 * @since 1.0.0
	 */
	public static function cbxpetition_public_styles() {
		$version = CBXPETITION_PLUGIN_VERSION;

		$css_url_part         = CBXPETITION_ROOT_URL . 'assets/css/';
		$css_url_part_vendors = CBXPETITION_ROOT_URL . 'assets/vendors/';

		wp_register_style( 'awesome-notifications', $css_url_part_vendors . 'awesome-notifications/style.css', [], $version );
		wp_register_style( 'venobox', $css_url_part_vendors . 'venobox/venobox.min.css', [], $version );

		//wp_register_style( 'slick-css', $css_url_part_vendors . 'slick/slick.css', [], $version );
		//wp_register_style( 'slick-theme', $css_url_part_vendors . 'slick/slick-theme.css', [], $version );

		//$public_css_dep = ['awesome-notifications', 'venobox', 'slick-css', 'slick-theme'];
		$public_css_dep = [ 'awesome-notifications', 'venobox' ];

		wp_register_style( 'cbxpetition-public', $css_url_part . 'cbxpetition-public.css', $public_css_dep, $version );

		wp_enqueue_style( 'awesome-notifications' );
		wp_enqueue_style( 'venobox' );
		//wp_enqueue_style( 'slick-css' );
		//wp_enqueue_style( 'slick-theme' );
		wp_enqueue_style( 'cbxpetition-public' );
	}//end method cbxpetition_public_styles

	/**
	 * all public scripts enqueue in cbxpetition
	 * @since 1.0.0
	 */
	public static function cbxpetition_public_scripts() {
		$version = CBXPETITION_PLUGIN_VERSION;

		$js_url_part         = CBXPETITION_ROOT_URL . 'assets/js/';
		$js_url_part_vendors = CBXPETITION_ROOT_URL . 'assets/vendors/';
		$js_url_part_vanila  = CBXPETITION_ROOT_URL . 'assets/js/vanila/';
		$js_url_part_build   = CBXPETITION_ROOT_URL . 'assets/js/build/';

		wp_register_script( 'awesome-notifications', $js_url_part_vendors . 'awesome-notifications/script.js', [], $version, true );
		wp_register_script( 'readmore', $js_url_part_vendors . 'readmore/readmore.js', [ 'jquery' ], $version, true );
		wp_register_script( 'venobox', $js_url_part_vendors . 'venobox/venobox.min.js', [ 'jquery' ], $version, true );
		wp_register_script( 'jquery-validate', $js_url_part_vendors . 'jquery-validation/jquery.validate.min.js', [ 'jquery' ], $version, true );


		wp_register_script( 'cbxpetition-public', $js_url_part_vanila . 'cbxpetition-public.js',
			[
				'jquery',
				'awesome-notifications',
				'readmore',
				'venobox',
				'jquery-validate'
			], $version, true );

		$settings = new    CBXSetting();
		//photo
		$photo_max_files     = $photo_max_size_mb = absint( $settings->get_field( 'photo_max_files', 'cbxpetition_general', 10 ) );
		$photo_max_file_size = absint( $settings->get_field( 'max_file_size', 'cbxpetition_general', 1 ) );        //in mega bytes
		$photo_max_file_size = $photo_max_file_size * 1024 * 1024;
		$photo_file_exts     = $settings->get_field( 'photo_allow_filexts', 'cbxpetition_general', [] );

		if ( ! is_array( $photo_file_exts ) ) {
			$photo_file_exts = [];
		}

		$photo_file_exts = array_filter( $photo_file_exts );

		$photo_ext_mimes = 'image\/';
		$photo_exts      = array_keys( $photo_file_exts );
		$photo_ext_mimes .= '' . implode( "|", $photo_file_exts );

		//end photo


		//banner
		$banner_max_file_size = absint( $settings->get_field( 'banner_max_file_size', 'cbxpetition_general', 2 ) );//mega bytes
		$banner_max_file_size = $banner_max_file_size * 1024 * 1024;                                           //bytes

		$banner_file_exts = $settings->get_field( 'banner_allow_filexts', 'cbxpetition_general', [] );


		if ( ! is_array( $banner_file_exts ) ) {
			$banner_file_exts = [];
		}
		$banner_file_exts = array_filter( $banner_file_exts );


		$banner_ext_mimes = 'image\/';
		$banner_exts      = array_keys( $banner_file_exts );
		$banner_ext_mimes .= '' . implode( "|", $banner_file_exts );
		//end banner

		// Localize the script with new data
		$translation_array = [
			'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
			'nonce'                    => wp_create_nonce( 'cbxpetition_nonce' ),
			'is_user_logged_in'        => is_user_logged_in() ? 1 : 0,
			'validation'               => [
				'required'    => esc_html__( 'This field is required.', 'cbxpetition' ),
				'remote'      => esc_html__( 'Please fix this field.', 'cbxpetition' ),
				'email'       => esc_html__( 'Please enter a valid email address.', 'cbxpetition' ),
				'url'         => esc_html__( 'Please enter a valid URL.', 'cbxpetition' ),
				'date'        => esc_html__( 'Please enter a valid date.', 'cbxpetition' ),
				'dateISO'     => esc_html__( 'Please enter a valid date ( ISO ).', 'cbxpetition' ),
				'number'      => esc_html__( 'Please enter a valid number.', 'cbxpetition' ),
				'digits'      => esc_html__( 'Please enter only digits.', 'cbxpetition' ),
				'equalTo'     => esc_html__( 'Please enter the same value again.', 'cbxpetition' ),
				'maxlength'   => esc_html__( 'Please enter no more than {0} characters.', 'cbxpetition' ),
				'minlength'   => esc_html__( 'Please enter at least {0} characters.', 'cbxpetition' ),
				'rangelength' => esc_html__( 'Please enter a value between {0} and {1} characters long.', 'cbxpetition' ),
				'range'       => esc_html__( 'Please enter a value between {0} and {1}.', 'cbxpetition' ),
				'max'         => esc_html__( 'Please enter a value less than or equal to {0}.', 'cbxpetition' ),
				'min'         => esc_html__( 'Please enter a value greater than or equal to {0}.', 'cbxpetition' ),
				'recaptcha'   => esc_html__( 'Please check the captcha.', 'cbxpetition' ),
			],
			'ajax'                     => [
				'loading'  => esc_html__( 'Please wait, loading', 'cbxpetition' ),
				'loadmore' => esc_html__( 'Load More', 'cbxpetition' ),
				'fail'     => esc_html__( 'Sorry, request failed, please submit again', 'cbxpetition' ),
			],
			'readmore'                 => [
				'moreLink' => '<a class="cbxpetition_button secondary outline button_readmore button_readmore_open small" href="#">' . esc_html__( 'Read More', 'cbxpetition' ) . '</a>',
				'lessLink' => '<a class="cbxpetition_button secondary outline button_readmore button_readmore_close small" href="#">' . esc_html__( 'Close', 'cbxpetition' ) . '</a>',
			],
			'awn_options'              => [
				'tip'           => esc_html__( 'Tip', 'cbxpetition' ),
				'info'          => esc_html__( 'Info', 'cbxpetition' ),
				'success'       => esc_html__( 'Success', 'cbxpetition' ),
				'warning'       => esc_html__( 'Attention', 'cbxpetition' ),
				'alert'         => esc_html__( 'Error', 'cbxpetition' ),
				'async'         => esc_html__( 'Loading', 'cbxpetition' ),
				'confirm'       => esc_html__( 'Confirmation', 'cbxpetition' ),
				'confirmOk'     => esc_html__( 'OK', 'cbxpetition' ),
				'confirmCancel' => esc_html__( 'Cancel', 'cbxpetition' )
			],
			'are_you_sure_global'      => esc_html__( 'Are you sure?', 'cbxpetition' ),
			'are_you_sure_delete_desc' => esc_html__( 'Once you delete, it\'s gone forever. You can not revert it back.', 'cbxpetition' ),
			'logout'                   => [
				'confirm_title' => esc_html__( 'Confirm Logout?', 'cbxpetition' ),
				'confirm_desc'  => esc_html__( 'Once you logout you can not undone this process.', 'cbxpetition' )
			],
			'photo'                    => [
				'exists'                 => 0,
				'data'                   => '',
				'max_files'              => $photo_max_files,
				'file_types'             => $photo_ext_mimes,
				'file_exts'              => $photo_exts,
				'max_filesize'           => $photo_max_file_size,
				'error_wrong_file_count' => esc_html__( 'Photo upload failed, maximum allowed reached.', 'cbxpetition' ),
				'error_wrong_file_type'  => esc_html__( 'Photo upload failed, wrong file type.', 'cbxpetition' ),
				/* translators: %d: image maximum size in mb  */
				'error_wrong_file_size'  => sprintf( esc_html__( 'Photo upload failed, wrong file size. Maximum allowed size %d MB', 'cbxpetition' ), $photo_max_size_mb ),
			],
			'banner'                   => [
				'exists'                => 0,
				'data'                  => '',
				'file_types'            => $banner_ext_mimes,
				'file_exts'             => $banner_exts,
				'max_filesize'          => $banner_max_file_size,
				'error_wrong_file_type' => esc_html__( 'Banner upload failed, wrong file type.', 'cbxpetition' ),
				'error_wrong_file_size' => esc_html__( 'Banner upload failed, wrong file size.', 'cbxpetition' ),
			],
		];

		wp_localize_script( 'cbxpetition-public', 'cbxpetition_public_js_vars', $translation_array );


		wp_enqueue_script( 'awesome-notifications' );
		wp_enqueue_script( 'readmore' );
		wp_enqueue_script( 'venobox' );
		wp_enqueue_script( 'jquery-validate' );
		wp_enqueue_script( 'cbxpetition-public' );
	}//end method cbxpetition_public_scripts

	/**
	 * Get the max photo limit 1 to 10 dropdown
	 *
	 * @return type
	 * @since 1.0.0
	 */
	public static function get_max_photo_limit() {
		$max_photo_limit_arr = [];
		for ( $i = 1; $i <= 10; $i ++ ) {
			$max_photo_limit_arr[ $i ] = $i;
		}

		return apply_filters( 'cbxpetition_max_photo_limit', $max_photo_limit_arr );
	}//end method get_max_photo_limit

	/**
	 * delete uploaded photos of the petition
	 *
	 * @param  int  $petition_id
	 *
	 * @since 1.0.0
	 */
	public static function deletePetitionPhotosFolder( $petition_id = 0 ) {
		$dir_info = self::checkUploadDir();
		if ( absint( $petition_id ) > 0 && intval( $dir_info['folder_exists'] ) == 1 ) {

			global $wp_filesystem;
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();

			//$dir_to_del       = wp_upload_dir()['basedir'] . '/cbxpetition/' . $review_id;
			//$dir_thumb_to_del = $dir_to_del . '/thumbnail';
			$dir_to_del = $dir_info['cbxpetition_base_dir'] . $petition_id;
			//$dir_thumb_to_del = $dir_to_del . '/thumbnail';

			//if dir exists then delete


			/*array_map( 'unlink', glob( "$dir_to_del/*.*" ) );
            array_map( 'unlink', glob( "$dir_thumb_to_del/*.*" ) );
            if ( @rmdir( $dir_thumb_to_del ) ) {
                @rmdir( $dir_to_del );
            }*/

			$wp_filesystem->delete( $dir_to_del, true, 'd' );
		}
	}//end method deletePetitionPhotosFolder

	/**
	 * make cbxpetition folder in uploads directory if not exist, return path info
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public static function checkUploadDir( $dir_part = '' ) {
		$upload_dir = wp_upload_dir();

		//wordpress core base dir and url
		$upload_dir_basedir = $upload_dir['basedir'];
		$upload_dir_baseurl = $upload_dir['baseurl'];

		$dir_info = [
			'upload_dir_basedir' => $upload_dir_basedir,
			'upload_dir_baseurl' => $upload_dir_baseurl,
		];

		//petition base dir and base url
		$petition_base_dir = $upload_dir_basedir . '/cbxpetition/';
		$petition_base_url = $upload_dir_baseurl . '/cbxpetition/';

		//cbxpetition temp dir and temp url
		$petition_temp_dir = $upload_dir_basedir . '/cbxpetition/temp/';
		$petition_temp_url = $upload_dir_baseurl . '/cbxpetition/temp/';

		$petition_dir_info = [
			'cbxpetition_base_dir' => $petition_base_dir,
			'cbxpetition_base_url' => $petition_base_url,
			'cbxpetition_temp_dir' => $petition_temp_dir,
			'cbxpetition_temp_url' => $petition_temp_url
		];

		$dir_info = array_merge( $dir_info, $petition_dir_info );


		global $wp_filesystem;
		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		WP_Filesystem();

		$folder_exists = 1;
		//let's check if the cbxpetition folder exists in upload dir
		if ( ! $wp_filesystem->exists( $petition_temp_dir ) ) {
			$created = wp_mkdir_p( $petition_temp_dir );
			if ( $created ) {
				$folder_exists             = 1;
				$dir_info['folder_exists'] = $folder_exists;
			} else {
				$folder_exists             = 0;
				$dir_info['folder_exists'] = $folder_exists;
			}
		}

		//core petition folder exists and partial dir creation is requested
		if ( $folder_exists && $dir_part != '' ) {
			$dir_part_created = 0;
			if ( ! $wp_filesystem->exists( $petition_base_dir . $dir_part ) ) {
				$dir_part_created = wp_mkdir_p( $petition_base_dir . $dir_part );
			} else {
				$dir_part_created = 1;
			}

			if ( $dir_part_created ) {
				$dir_part_base_dir = $petition_base_dir . $dir_part;
				$dir_part_base_url = $petition_base_url . $dir_part;

				$dir_part_info = [
					'dir_part_base_dir' => $dir_part_base_dir,
					'dir_part_base_url' => $dir_part_base_url,
				];

				$dir_info = array_merge( $dir_info, $dir_part_info );
			}
		}


		return apply_filters( 'cbxpetition_dir_info', $dir_info );
	}//end method checkUploadDir

	/**
	 * acceptable image ext
	 * @return array
	 * @since 1.0.0
	 */
	public static function getImageExts() {
		$img_exts = [
			'jpg'  => esc_attr__( 'Jpg', 'cbxpetition' ),
			'jpeg' => esc_attr__( 'Jpeg', 'cbxpetition' ),
			'gif'  => esc_attr__( 'Gif', 'cbxpetition' ),
			'png'  => esc_attr__( 'Png', 'cbxpetition' ),
			'webp' => esc_attr__( 'Webp', 'cbxpetition' ),
			'svg'  => esc_attr__( 'Svg', 'cbxpetition' ),
			'bmp'  => esc_attr__( 'Bmp', 'cbxpetition' ),
		];

		return apply_filters( 'cbxpetition_img_exts', $img_exts );
	}//end method getImageExts

	public static function getImageExtMimes() {
		$img_mimes = [
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
			'gif'  => 'image/gif',
			'bmp'  => 'image/bmp',
			'svg'  => 'image/svg+xml',
			'webp' => 'image/webp',
		];

		return apply_filters( 'cbxpetition_img_mimes', $img_mimes );
	}//end method getImageExtMimes

	/**
	 * Admin page slugs
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public static function admin_page_slugs() {
		$slugs = [ 'cbxpetition-signatures', 'cbxpetition-settings', 'cbxpetition-doc' ];

		return apply_filters( 'cbxpetition_admin_page_slugs', $slugs );
	}//end admin_page_slugs


	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * https://wordpress.stackexchange.com/a/317041/6343
	 *
	 * Case #1: After WP_REST_Request initialisation
	 * Case #2: Support "plain" permalink settings
	 * Case #3: It can happen that WP_Rewrite is not yet initialized,
	 *          so do this (wp-settings.php)
	 * Case #4: URL Path begins with wp-json/ (your REST prefix)
	 *          Also supports WP installations in subfolders
	 *
	 * @returns boolean
	 * @author matzeeable
	 * @since 1.0.0
	 */
	public static function is_rest() {
		$prefix = rest_get_url_prefix();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended 
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST || isset( $_GET['rest_route'] ) && strpos( trim( sanitize_text_field( wp_unslash( $_GET['rest_route'] ) ), '\\/' ), $prefix, 0 ) === 0 ) {
			return true;
		}
		// (#3)
		global $wp_rewrite;
		if ( $wp_rewrite === null ) {
			$wp_rewrite = new WP_Rewrite();
		}

		// (#4)
		$rest_url    = wp_parse_url( trailingslashit( rest_url() ) );
		$current_url = wp_parse_url( add_query_arg( [] ) );

		return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
	}//end is_rest

	/**
	 * Setup a post object and store the original loop item so we can reset it later
	 *
	 * @param  obj  $post_to_setup  The post that we want to use from our custom loop
	 *
	 * @since 1.0.0
	 */
	public static function setup_admin_postdata( $post_to_setup ) {
		//only on the admin side
		if ( is_admin() ) {

			//get the post for both setup_postdata() and to be cached
			global $post;

			//only cache $post the first time through the loop
			if ( ! isset( $GLOBALS['post_cache'] ) ) {
				$GLOBALS['post_cache'] = $post; //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			}

			//setup the post data as usual
			$post = $post_to_setup;
			setup_postdata( $post );
		} else {
			setup_postdata( $post_to_setup );
		}
	}//end method setup_admin_postdata

	/**
	 * Reset $post back to the original item
	 * @since 1.0.0
	 */
	public static function wp_reset_admin_postdata() {
		//only on the admin and if post_cache is set
		if ( is_admin() && ! empty( $GLOBALS['post_cache'] ) ) {

			//globalize post as usual
			global $post;

			//set $post back to the cached version and set it up
			$post = $GLOBALS['post_cache'];
			setup_postdata( $post );

			//cleanup
			unset( $GLOBALS['post_cache'] );
		} else {
			wp_reset_postdata();
		}
	}//end method wp_reset_admin_postdata

	/**
	 * Petition sections
	 * @since 1.0.0
	 */
	public static function petition_default_sections() {
		$sections = [
			'cbxpetition_banner'     => esc_html__( 'Banner', 'cbxpetition' ),
			'cbxpetition_stat'       => esc_html__( 'Stat', 'cbxpetition' ),
			'cbxpetition_video'      => esc_html__( 'Video', 'cbxpetition' ),
			'cbxpetition_photos'     => esc_html__( 'Photos', 'cbxpetition' ),
			'cbxpetition_letter'     => esc_html__( 'letter', 'cbxpetition' ),
			'cbxpetition_signform'   => esc_html__( 'Sign form', 'cbxpetition' ),
			'cbxpetition_signatures' => esc_html__( 'Signatures', 'cbxpetition' ),
		];

		return apply_filters( 'cbxpetition_default_sections', $sections );
	}//end petition_default_sections

	/**
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public static function petition_default_section_keys() {
		$sections      = self::petition_default_sections();
		$sections_keys = array_keys( $sections );

		return apply_filters( 'cbxpetition_default_section_keys', $sections_keys );
	}//end cbxpetition_default_section_keys

	/**
	 * Add utm params to any url
	 *
	 * @param  string  $url
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function url_utmy( $url = '' ) {
		if ( $url == '' ) {
			return $url;
		}

		$url = add_query_arg( [
			'utm_source'   => 'plgsidebarinfo',
			'utm_medium'   => 'plgsidebar',
			'utm_campaign' => 'wpfreemium',
		], $url );

		return $url;
	}//end url_utmy

	/**
	 * Bookmark login form
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function guest_login_forms() {
		$forms = [];

		$forms['wordpress'] = esc_html__( 'WordPress Core Login Form', 'cbxpetition' );
		$forms['none']      = esc_html__( 'Don\'t show login form, show default login url', 'cbxpetition' );
		$forms['off']       = esc_html__( 'Show nothing!', 'cbxpetition' );

		return apply_filters( 'cbxpetition_guest_login_forms', $forms );
	}//end guest_login_forms

	public static function get_settings_sections() {
		return apply_filters( 'cbxpetition_setting_sections',
			[
				[
					'id'    => 'cbxpetition_basic',
					'title' => esc_html__( 'Basic Settings', 'cbxpetition' ),
				],
				[
					'id'    => 'cbxpetition_general',
					'title' => esc_html__( 'General Settings', 'cbxpetition' ),
				],
				[
					'id'    => 'cbxpetition_email_tpl',
					'title' => esc_html__( 'Global Email Template', 'cbxpetition' ),
				],
				[
					'id'    => 'cbxpetition_tools',
					'title' => esc_html__( 'Tools', 'cbxpetition' ),
				]
			] );
	}//end method get_settings_sections

	/**
	 * Global Setting Fields
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_settings_fields() {
		global $wpdb;

		//$table_names      = self::allTablesArr();
		//$option_values = self::getAllOptionNames();
		$external_svg = cbxpetition_esc_svg( cbxpetition_load_svg( 'icon_external' ) );
		$archive_url  = get_post_type_archive_link( 'cbxpetition' );

		$gust_login_forms        = self::guest_login_forms();
		$tools_delete_table_html = '<div id="setting_resetinfo">' . esc_html__( 'Loading ...', 'cbxpetition' ) . '</div>';


		$settings_builtin_fields = [
			'cbxpetition_basic'     => [
				'basic_heading'   => [
					'name'    => 'basic_heading',
					'label'   => esc_html__( 'Post Type, Taxonomy and Slug', 'cbxpetition' ),
					'type'    => 'heading',
					'default' => '',
				],
				'post_slug'       => [
					'name'    => 'post_slug',
					'label'   => esc_html__( 'Petition Slug', 'cbxpetition' ),
					'desc'    => esc_html__( 'Petition post type slug. Default: cbxpetition', 'cbxpetition' ),
					'type'    => 'slug',
					'default' => 'cbxpetition'
				],
				'archive_slug'    => [
					'name'    => 'archive_slug',
					'label'   => esc_html__( 'Archive Slug', 'cbxpetition' ),
					'desc'    => esc_html__( 'Petition post type archive slug. Default: cbxpetition',
							'cbxpetition' ) . '<a target="_blank" class="button small outline icon icon-inline icon-right ml-10" href="' . esc_url( $archive_url ) . '"><i class="cbx-icon">' . $external_svg . '</i><span class="button-label">' . esc_attr__( 'View Archive',
							'cbxpetition' ) . '</span></a>',
					'type'    => 'slug',
					'default' => 'cbxpetitions'
				],
				'cat_enable'      => [
					'name'    => 'cat_enable',
					'label'   => esc_html__( 'Enable Category', 'cbxpetition' ),
					'desc'    => esc_html__( 'Enable/disable category taxonomy for petition. Category is hierarchical taxonomy.', 'cbxpetition' ),
					'type'    => 'checkbox',
					'default' => 'on'
				],
				'cat_slug'        => [
					'name'    => 'cat_slug',
					'label'   => esc_html__( 'Category Slug', 'cbxpetition' ),
					'desc'    => esc_html__( 'Petition category slug. Default: petition-cat', 'cbxpetition' ),
					'type'    => 'slug',
					'default' => esc_attr_x( 'petition-cat', 'Petition category slug', 'cbxpetition' )
				],
				'tag_enable'      => [
					'name'    => 'tag_enable',
					'label'   => esc_html__( 'Enable Tag', 'cbxpetition' ),
					'desc'    => esc_html__( 'Enable/disable category tag for petition. Category is non hierarchical taxonomy.', 'cbxpetition' ),
					'type'    => 'checkbox',
					'default' => 'on'
				],
				'tag_slug'        => [
					'name'    => 'tag_slug',
					'label'   => esc_html__( 'Tag Slug', 'cbxpetition' ),
					'desc'    => esc_html__( 'Petition tag slug. Default: petition-tag', 'cbxpetition' ),
					'type'    => 'slug',
					'default' => esc_attr_x( 'petition-tag', 'Petition tag slug', 'cbxpetition' )
				],
				'custom_template' => [
					'name'    => 'custom_template',
					'label'   => esc_html__( 'Use Custom Template', 'cbxpetition' ),
					'desc'    => esc_html__( 'Use custom template for details petition, archive, taxonomy from plugin\'s template folder', 'cbxpetition' ),
					'default' => 'on',
					'type'    => 'checkbox'
				]
			],
			'cbxpetition_general'   => [
				'general_heading'          => [
					'name'    => 'basic_heading',
					'label'   => esc_html__( 'Petition Basic', 'cbxpetition' ),
					'type'    => 'heading',
					'default' => '',
				],
				'terms_page'               => [
					'name'        => 'terms_page',
					'label'       => esc_html__( 'Petition Terms Page', 'cbxpetition' ),
					'desc'        => esc_html__( 'Petition terms and condition page.', 'cbxpetition' ),
					'type'        => 'page',
					'allow_clear' => 1,
					'default'     => 0,
					'options'     => self::get_pages( true ),
				],
				'default_state'            => [
					'name'    => 'default_state',
					'label'   => esc_html__( 'Default Sign Status', 'cbxpetition' ),
					'desc'    => esc_html__( 'What will be status when a new sign is requested?', 'cbxpetition' ),
					'type'    => 'select',
					'default' => 'approved',
					'options' => self::getPetitionSignStates(),
				],
				'allow_guest_sign'         => [
					'name'              => 'allow_guest_sign',
					'label'             => esc_html__( 'Allow guest to sign', 'cbxpetition' ),
					'desc'              => esc_html__( 'Allow guest to sign petition or not.',
						'cbxpetition' ),
					'type'              => 'radio',
					'default'           => 'yes',
					'options'           => [
						'yes' => esc_html__( 'Yes', 'cbxpetition' ),
						'no'  => esc_html__( 'No', 'cbxpetition' ),
					],
					'sanitize_callback' => 'sanitize_text_field'
				],
				/*'show_login_form'          => [
					'name'              => 'show_login_form',
					'label'             => esc_html__( 'Show login form for guest user', 'cbxpetition' ),
					'desc'              => esc_html__( 'If select yes then show the login form for the guest users on the new job creating',
						'cbxpetition' ),
					'type'              => 'radio',
					'default'           => 'yes',
					'options'           => [
						'yes' => esc_html__( 'Yes', 'cbxpetition' ),
						'no'  => esc_html__( 'No', 'cbxpetition' ),
					],
					'sanitize_callback' => 'sanitize_text_field'
				],*/
				'guest_login_form'         => [
					'name'    => 'guest_login_form',
					'label'   => esc_html__( 'Guest User Login Form', 'cbxpetition' ),
					'desc'    => esc_html__( 'Default guest user is shown wordpress core login form. Pro addon helps to integrate 3rd party plugins like woocommerce, restrict content pro etc.',
						'cbxpetition' ),
					'type'    => 'select',
					'default' => 'wordpress',
					'options' => $gust_login_forms
				],
				'guest_show_register'      => [
					'name'    => 'guest_show_register',
					'label'   => esc_html__( 'Show Register link to guest', 'cbxpetition' ),
					'desc'    => esc_html__( 'Show register link to guest, depends on if registration is enabled in wordpress core',
						'cbxpetition' ),
					'type'    => 'radio',
					'default' => 1,
					'options' => [
						1 => esc_html__( 'Yes', 'cbxpetition' ),
						0 => esc_html__( 'No', 'cbxpetition' ),
					],
				],
				'guest_activation'         => [
					'name'    => 'guest_activation',
					'label'   => esc_html__( 'Guest Email Verify', 'cbxpetition' ),
					'desc'    => wp_kses( __( 'Enable/Disable (To make this feature work need to enable user email notification on and user email template should have the tag syntax <code>{activation_link}</code>)',
						'cbxpetition' ), [ 'code' => [] ] ),
					'type'    => 'checkbox',
					'default' => 'on',
				],
				'state_on_verify'          => [
					'name'    => 'state_on_verify',
					'label'   => esc_html__( 'Sign Status after Verify', 'cbxpetition' ),
					'desc'    => esc_html__( 'What will be status when a guest user verify sign from email', 'cbxpetition' ),
					'type'    => 'select',
					'default' => 'approved',
					'options' => self::getPetitionSignStates(),
				],
				'sign_limit'               => [
					'name'              => 'sign_limit',
					'label'             => esc_html__( 'Signature Listing Per Page', 'cbxpetition' ),
					'desc'              => esc_html__( 'Display signature per page in pagination', 'cbxpetition' ),
					'type'              => 'text',
					'default'           => 20,
					'sanitize_callback' => 'absint',
				],
				'sign_comment_req'         => [
					'name'    => 'sign_comment_req',
					'label'   => esc_html__( 'Signature Comment Required', 'cbxpetition' ),
					'desc'    => esc_html__( 'Signature comment text required or not, if required at least 50 chars long', 'cbxpetition' ),
					'type'    => 'radio',
					'options' => [
						1 => esc_html__( 'Yes', 'cbxpetition' ),
						0 => esc_html__( 'No', 'cbxpetition' ),
					],
					'default' => 0,
				],
				'auto_integration_heading' => [
					'name'    => 'auto_integration_heading',
					'label'   => esc_html__( 'Petition Auto Integration', 'cbxpetition' ),
					'type'    => 'heading',
					'default' => '',
				],
				'enable_auto_integration'  => [
					'name'    => 'enable_auto_integration',
					'label'   => esc_html__( 'Enable Auto Integration', 'cbxpetition' ),
					'desc'    => esc_html__( 'Show petition features before or after content', 'cbxpetition' ),
					'type'    => 'checkbox',
					'default' => 'on',
				],
				'auto_integration_before'  => [
					'name'     => 'auto_integration_before',
					'label'    => esc_html__( 'Auto Integration Before Content', 'cbxpetition' ),
					'desc'     => esc_html__( 'Which shortcode/blocks will be added before content', 'cbxpetition' ),
					'type'     => 'multicheck',
					'default'  => [ 'cbxpetition_banner', 'cbxpetition_stat' ],
					'options'  => apply_filters( 'cbxpetition_auto_integration_before',
						[
							'cbxpetition_banner'     => esc_html__( 'Banner', 'cbxpetition' ),
							'cbxpetition_stat'       => esc_html__( 'Statistics', 'cbxpetition' ),
							'cbxpetition_video'      => esc_html__( 'Video', 'cbxpetition' ),
							'cbxpetition_photos'     => esc_html__( 'Photos', 'cbxpetition' ),
							'cbxpetition_letter'     => esc_html__( 'Letter', 'cbxpetition' ),
							'cbxpetition_signform'   => esc_html__( 'Signature Form', 'cbxpetition' ),
							'cbxpetition_signatures' => esc_html__( 'Signature Listing', 'cbxpetition' ),
						]
					),
					'sortable' => 1,
				],
				'auto_integration_after'   => [
					'name'     => 'auto_integration_after',
					'label'    => esc_html__( 'Auto Integration After Content', 'cbxpetition' ),
					'desc'     => esc_html__( 'Which shortcode/blocks will be added after content', 'cbxpetition' ),
					'type'     => 'multicheck',
					'default'  => [
						'cbxpetition_video',
						'cbxpetition_photos',
						'cbxpetition_letter',
						'cbxpetition_signform',
						'cbxpetition_signatures',
					],
					'options'  => apply_filters( 'cbxpetition_auto_integration_after',
						[
							'cbxpetition_banner'     => esc_html__( 'Banner', 'cbxpetition' ),
							'cbxpetition_stat'       => esc_html__( 'Statistics', 'cbxpetition' ),
							'cbxpetition_video'      => esc_html__( 'Video', 'cbxpetition' ),
							'cbxpetition_photos'     => esc_html__( 'Photos', 'cbxpetition' ),
							'cbxpetition_letter'     => esc_html__( 'Letter', 'cbxpetition' ),
							'cbxpetition_signform'   => esc_html__( 'Signature Form', 'cbxpetition' ),
							'cbxpetition_signatures' => esc_html__( 'Signature Listing', 'cbxpetition' ),
						]
					),
					'sortable' => 1,

				],

				'photos_information'  => [
					'name'    => 'photos_information',
					'label'   => esc_html__( 'Petition Photo(s) Configuration', 'cbxpetition' ),
					'type'    => 'heading',
					'default' => '',
				],
				'photo_max_files'     => [
					'name'              => 'photo_max_files',
					'label'             => esc_html__( 'Petition Photo Limit', 'cbxpetition' ),
					'desc'              => esc_html__( 'Maximum number of photos allowed in petition', 'cbxpetition' ),
					'type'              => 'text',
					'default'           => 6,
					'sanitize_callback' => 'absint',
				],
				'photo_max_file_size' => [
					'name'              => 'photo_max_file_size',
					'label'             => esc_html__( 'Petition Photo Max File Size(MB)', 'cbxpetition' ),
					'desc'              => esc_html__( 'What will be the maximum photo size in MB?', 'cbxpetition' ),
					'type'              => 'text',
					'default'           => '1',
					'sanitize_callback' => 'absint',
				],
				'photo_allow_filexts' => [
					'name'    => 'photo_allow_filexts',
					'label'   => esc_html__( 'Petition Photo Extensions', 'cbxpetition' ),
					'desc'    => esc_html__( 'Photo extensions that are allowable to upload, if all unchecked then jpg, jpeg, gif, png are allowed.', 'cbxpetition' ),
					'type'    => 'multicheck',
					'options' => self::getImageExts(),
					'default' => array_keys( self::getImageExts() )
				],
				'photo_max_width'     => [
					'name'              => 'photo_max_width',
					'label'             => esc_html__( 'Petition Photo(s) max width', 'cbxpetition' ),
					'desc'              => esc_html__( 'What will be the maximum width of photo?', 'cbxpetition' ),
					'type'              => 'text',
					'default'           => 800,
					'sanitize_callback' => 'absint',
				],
				'photo_max_height'    => [
					'name'              => 'photo_max_height',
					'label'             => esc_html__( 'Petition Photo(s) max height', 'cbxpetition' ),
					'desc'              => esc_html__( 'What will be the maximum height of photo?', 'cbxpetition' ),
					'type'              => 'text',
					'default'           => 800,
					'sanitize_callback' => 'absint',
				],
				'thumb_max_width'     => [
					'name'              => 'thumb_max_width',
					'label'             => esc_html__( 'Petition Photo Thumbnail max width', 'cbxpetition' ),
					'desc'              => esc_html__( 'What will be the maximum width of thumbnail photo?', 'cbxpetition' ),
					'type'              => 'text',
					'default'           => '400',
					'sanitize_callback' => 'absint',
				],
				'thumb_max_height'    => [
					'name'              => 'thumb_max_height',
					'label'             => esc_html__( 'Petition Photo Thumbnail max height', 'cbxpetition' ),
					'desc'              => esc_html__( 'What will be the maximum height of thumbnail photo?', 'cbxpetition' ),
					'type'              => 'text',
					'default'           => '400',
					'sanitize_callback' => 'absint',
				],

				'banner_information'   => [
					'name'    => 'banner_information',
					'label'   => esc_html__( 'Petition Banner Configuration', 'cbxpetition' ),
					'type'    => 'heading',
					'default' => '',
				],
				'banner_max_file_size' => [
					'name'              => 'banner_max_file_size',
					'label'             => esc_html__( 'Petition Banner Max File Size(MB)', 'cbxpetition' ),
					'desc'              => esc_html__( 'What will be the maximum banner size in MB?', 'cbxpetition' ),
					'type'              => 'text',
					'default'           => 2,
					'sanitize_callback' => 'absint',
				],
				'banner_allow_filexts' => [
					'name'    => 'banner_allow_filexts',
					'label'   => esc_html__( 'Petition Banner Extensions', 'cbxpetition' ),
					'desc'    => esc_html__( 'Banner extensions that are allowable to upload, if all unchecked then jpg, jpeg, gif, png are allowed.', 'cbxpetition' ),
					'type'    => 'multicheck',
					'options' => self::getImageExts(),
					'default' => array_keys( self::getImageExts() )
				],
				'banner_max_width'     => [
					'name'              => 'banner_max_width',
					'label'             => esc_html__( 'Petition Banner max width', 'cbxpetition' ),
					'desc'              => esc_html__( 'What will be the maximum width of banner?', 'cbxpetition' ),
					'type'              => 'text',
					'default'           => 1500,
					'sanitize_callback' => 'absint',
				],
				'banner_max_height'    => [
					'name'              => 'banner_max_height',
					'label'             => esc_html__( 'Petition Banner max height', 'cbxpetition' ),
					'desc'              => esc_html__( 'What will be the maximum height of banner?', 'cbxpetition' ),
					'type'              => 'text',
					'default'           => 400,
					'sanitize_callback' => 'absint',
				],

			],
			'cbxpetition_email_tpl' => [
				'email_template_heading' => [
					'name'    => 'email_template_heading',
					'label'   => esc_html__( 'Petition Email Template', 'cbxpetition' ),
					'type'    => 'heading',
					'default' => '',
				],
				'headerimage'            => [
					'name'    => 'headerimage',
					'label'   => esc_html__( 'Header Image', 'cbxpetition' ),
					//'desc'    => esc_html__( 'Url To email you want to show as email header.Upload Image by media uploader.','cbxpetition' ),
					'type'    => 'file',
					'default' => '',
				],
				'footertext'             => [
					'name'    => 'footertext',
					'label'   => esc_html__( 'Footer Text', 'cbxpetition' ),
					'desc'    => wp_kses( __( 'The text to appear at the email footer. Syntax available - <code>{site_title}</code>', 'cbxpetition' ), [ 'code' => [] ] ),
					'type'    => 'wysiwyg',
					'default' => '{site_title}',
				],
				'basecolor'              => [
					'name'    => 'basecolor',
					'label'   => esc_html__( 'Base Color', 'cbxpetition' ),
					'desc'    => esc_html__( 'The base color of the email.', 'cbxpetition' ),
					'type'    => 'color',
					'default' => '#557da1',
				],
				'backgroundcolor'        => [
					'name'    => 'backgroundcolor',
					'label'   => esc_html__( 'Background Colour', 'cbxpetition' ),
					'desc'    => esc_html__( 'The background color of the email.', 'cbxpetition' ),
					'type'    => 'color',
					'default' => '#f5f5f5',
				],
				'bodybackgroundcolor'    => [
					'name'    => 'bodybackgroundcolor',
					'label'   => esc_html__( 'Body Background Color', 'cbxpetition' ),
					'desc'    => esc_html__( 'The background colour of the main body of email.', 'cbxpetition' ),
					'type'    => 'color',
					'default' => '#fdfdfd',
				],
				'bodytextcolor'          => [
					'name'    => 'bodytextcolor',
					'label'   => esc_html__( 'Body Text Color', 'cbxpetition' ),
					'desc'    => esc_html__( 'The body text colour of the main body of email.', 'cbxpetition' ),
					'type'    => 'color',
					'default' => '#505050',
				],
				'footertextcolor'        => [
					'name'    => 'footertextcolor',
					'label'   => esc_html__( 'Footer Text Color', 'cbxpetition' ),
					'desc'    => esc_html__( 'The footer text colour of the footer of email.', 'cbxpetition' ),
					'type'    => 'color',
					'default' => '#3c3c3c',
				],
			],
			'cbxpetition_tools'     => [
				'tools_heading'        => [
					'name'    => 'tools_heading',
					'label'   => esc_html__( 'Tools Settings', 'cbxpetition' ),
					'type'    => 'heading',
					'default' => '',
				],
				'delete_global_config' => [
					'name'    => 'delete_global_config',
					'label'   => esc_html__( 'On Uninstall delete plugin data', 'cbxpetition' ),
					'desc'    => '<p>' . esc_html__( 'Delete Global Config data(options/plugin settings), custom table(s), files/folders, all petition custom post type  created by this plugin on uninstall. Please note that this process can not be undone and it is recommended to keep full database and files backup before doing this.',
							'cbxpetition' ) . '</p>',
					'type'    => 'radio',
					'options' => [
						'yes' => esc_html__( 'Yes', 'cbxpetition' ),
						'no'  => esc_html__( 'No', 'cbxpetition' ),
					],
					'default' => 'no',
				],
				'reset_data'           => [
					'name'    => 'reset_data',
					'label'   => esc_html__( 'Reset all section', 'cbxpetition' ),
					'desc'    => $tools_delete_table_html . '<p>' . esc_html__( 'This will reset all option/section created by this plugin.',
							'cbxpetition' ) . '<a data-busy="0" class="button secondary ml-20" id="reset_data_trigger"  href="#">' . esc_html__( 'Reset Sections',
							'cbxpetition' ) . '</a></p>',
					'type'    => 'html',
					'default' => 'off'
				],
			],
		];

		$settings_fields = []; //final setting array that will be passed to different filters

		$sections = self::get_settings_sections();


		foreach ( $sections as $section ) {
			if ( ! isset( $settings_builtin_fields[ $section['id'] ] ) ) {
				$settings_builtin_fields[ $section['id'] ] = [];
			}
		}

		foreach ( $sections as $section ) {
			$settings_fields[ $section['id'] ] = apply_filters( 'cbxpetition_global_' . $section['id'] . '_fields',
				$settings_builtin_fields[ $section['id'] ] );
		}

		return apply_filters( 'cbxpetition_global_fields', $settings_fields ); //final filter if need
	}//end method get_settings_fields

	/**
	 * Most needed common strings needed in js throughout the plugin
	 *
	 * @return array
	 */
	public static function global_translation_strings() {
		$global_translation = [
			'is_user_logged_in' => is_user_logged_in() ? 1 : 0,
			'ajax'              => [
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'rest_url'   => esc_url_raw( rest_url() ),
				'ajax_fail'  => esc_html__( 'Request failed, please reload the page.', 'cbxpetition' ),
				'ajax_nonce' => wp_create_nonce( 'cbxpetition_nonce' ),
				'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			],
			'teeny_setting'     => [
				'teeny'         => true,
				'media_buttons' => true,
				'editor_class'  => '',
				'textarea_rows' => 5,
				'quicktags'     => false,
				'menubar'       => false,
			],
			'copycmds'          => [
				'copy'       => esc_html__( 'Copy', 'cbxpetition' ),
				'copied'     => esc_html__( 'Copied', 'cbxpetition' ),
				'copy_tip'   => esc_html__( 'Click to copy', 'cbxpetition' ),
				'copied_tip' => esc_html__( 'Copied to clipboard', 'cbxpetition' ),
			],
			'delete_dialog'     => [
				'ok'                       => esc_attr_x( 'Ok', 'cbxpetition-dialog', 'cbxpetition' ),
				'cancel'                   => esc_attr_x( 'Cancel', 'cbxpetition-dialog', 'cbxpetition' ),
				'delete'                   => esc_attr_x( 'Delete', 'cbxpetition-dialog', 'cbxpetition' ),
				'are_you_sure_global'      => esc_html__( 'Are you sure?', 'cbxpetition' ),
				'are_you_sure_delete_desc' => esc_html__( 'Once you delete, it\'s gone forever. You can not revert it back.', 'cbxpetition' ),
			],
			'pickr_i18n'        => [
				// Strings visible in the UI
				'ui:dialog'       => esc_html__( 'color picker dialog', 'cbxpetition' ),
				'btn:toggle'      => esc_html__( 'toggle color picker dialog', 'cbxpetition' ),
				'btn:swatch'      => esc_html__( 'color swatch', 'cbxpetition' ),
				'btn:last-color'  => esc_html__( 'use previous color', 'cbxpetition' ),
				'btn:save'        => esc_html__( 'Save', 'cbxpetition' ),
				'btn:cancel'      => esc_html__( 'Cancel', 'cbxpetition' ),
				'btn:clear'       => esc_html__( 'Clear', 'cbxpetition' ),

				// Strings used for aria-labels
				'aria:btn:save'   => esc_html__( 'save and close', 'cbxpetition' ),
				'aria:btn:cancel' => esc_html__( 'cancel and close', 'cbxpetition' ),
				'aria:btn:clear'  => esc_html__( 'clear and close', 'cbxpetition' ),
				'aria:input'      => esc_html__( 'color input field', 'cbxpetition' ),
				'aria:palette'    => esc_html__( 'color selection area', 'cbxpetition' ),
				'aria:hue'        => esc_html__( 'hue selection slider', 'cbxpetition' ),
				'aria:opacity'    => esc_html__( 'selection slider', 'cbxpetition' ),
			],
			'awn_options'       => [
				'tip'           => esc_html__( 'Tip', 'cbxpetition' ),
				'info'          => esc_html__( 'Info', 'cbxpetition' ),
				'success'       => esc_html__( 'Success', 'cbxpetition' ),
				'warning'       => esc_html__( 'Attention', 'cbxpetition' ),
				'alert'         => esc_html__( 'Error', 'cbxpetition' ),
				'async'         => esc_html__( 'Loading', 'cbxpetition' ),
				'confirm'       => esc_html__( 'Confirmation', 'cbxpetition' ),
				'confirmOk'     => esc_html__( 'OK', 'cbxpetition' ),
				'confirmCancel' => esc_html__( 'Cancel', 'cbxpetition' )
			],
			'validation'        => [
				'required'    => esc_html__( 'This field is required.', 'cbxpetition' ),
				'remote'      => esc_html__( 'Please fix this field.', 'cbxpetition' ),
				'email'       => esc_html__( 'Please enter a valid email address.', 'cbxpetition' ),
				'url'         => esc_html__( 'Please enter a valid URL.', 'cbxpetition' ),
				'date'        => esc_html__( 'Please enter a valid date.', 'cbxpetition' ),
				'dateISO'     => esc_html__( 'Please enter a valid date ( ISO ).', 'cbxpetition' ),
				'number'      => esc_html__( 'Please enter a valid number.', 'cbxpetition' ),
				'digits'      => esc_html__( 'Please enter only digits.', 'cbxpetition' ),
				'equalTo'     => esc_html__( 'Please enter the same value again.', 'cbxpetition' ),
				'maxlength'   => esc_html__( 'Please enter no more than {0} characters.', 'cbxpetition' ),
				'minlength'   => esc_html__( 'Please enter at least {0} characters.', 'cbxpetition' ),
				'rangelength' => esc_html__( 'Please enter a value between {0} and {1} characters long.', 'cbxpetition' ),
				'range'       => esc_html__( 'Please enter a value between {0} and {1}.', 'cbxpetition' ),
				'max'         => esc_html__( 'Please enter a value less than or equal to {0}.', 'cbxpetition' ),
				'min'         => esc_html__( 'Please enter a value greater than or equal to {0}.', 'cbxpetition' ),
				'recaptcha'   => esc_html__( 'Please check the captcha.', 'cbxpetition' ),
			],
			'placeholder'       => [
				'select' => esc_html__( 'Please Select', 'cbxpetition' ),
				'search' => esc_html__( 'Search...', 'cbxpetition' ),
			],
			'upload'            => [
				'upload_btn'   => esc_html__( 'Upload', 'cbxpetition' ),
				'upload_title' => esc_html__( 'Select Media', 'cbxpetition' ),
			],
			'lang'              => get_user_locale(),
			'file_preview'      => [
				'browse'        => esc_attr__( 'Choose', 'cbxpetition' ),
				'chooseFile'    => esc_attr__( 'Take your pick...', 'cbxpetition' ),
				'label'         => esc_attr__( 'Choose Files to Upload', 'cbxpetition' ),
				'selectedCount' => esc_attr__( 'files selected', 'cbxpetition' )
			],
			'select_recipients' => esc_html__( 'Please select recipients first', 'cbxpetition' ),
		];

		return apply_filters( 'cbxpetition_global_translation', $global_translation );
	}//end method global_translation_strings

	/**
	 * Determines if a post, identified by the specified ID, exist
	 * within the WordPress database.
	 *
	 * Note that this function uses the 'acme_' prefix to serve as an
	 * example for how to use the function within a theme. If this were
	 * to be within a class, then the prefix would not be necessary.
	 *
	 * @param  int  $id  The ID of the post to check
	 *
	 * @return   bool          True if the post exists; otherwise, false.
	 * @since    1.0.0
	 */
	public static function post_exists( $id ) {
		return is_string( get_post_status( $id ) );
	}//end method post_exists

	/**
	 * Get order keys
	 *
	 * @return string[]
	 */
	public static function get_order_keys() {
		return [ 'ASC', 'DESC' ];
	}//end method get_order_keys

	public static function signature_get_sortable_keys() {
		$sortable_keys = [
			'id'          => 'id', //true means it's already sorted
			'petition_id' => 'petition_id',
			'f_name'      => 'f_name',
			'l_name'      => 'l_name',
			'email'       => 'email',
			'state'       => 'state'
		];

		return apply_filters( 'cbxpetition_signature_listing_sortable_keys', $sortable_keys );
	}//end method signature_get_sortable_keys

	/**
	 * Load reset option table html
	 *
	 * @return string
	 */
	public static function setting_reset_html_table() {
		$option_values = self::getAllOptionNames();


		$table_html = '<p style="margin-bottom: 10px;" class="grouped gapless grouped_buttons" id="cbxpetition_setting_options_check_actions"><a href="#" class="button primary cbxpetition_setting_options_check_action_call">' . esc_html__( 'Check All',
				'cbxpetition' ) . '</a><a href="#" class="button outline cbxpetition_setting_options_check_action_ucall">' . esc_html__( 'Uncheck All',
				'cbxpetition' ) . '</a></p>';
		$table_html .= '<table class="widefat widethin cbxpetition_table_data" id="cbxpetition_setting_options_table">
                        <thead>
                        <tr>
                            <th class="row-title">' . esc_attr__( 'Option Name', 'cbxpetition' ) . '</th>
                            <th>' . esc_attr__( 'Option ID', 'cbxpetition' ) . '</th>		
                        </tr>
                    </thead>';

		$table_html .= '<tbody>';

		$i = 0;
		foreach ( $option_values as $key => $value ) {
			$alternate_class = ( $i % 2 == 0 ) ? 'alternate' : '';
			$i ++;
			$table_html .= '<tr class="' . esc_attr( $alternate_class ) . '">
                                <td class="row-title"><input checked class="magic-checkbox reset_options" type="checkbox" name="reset_options[' . $value['option_name'] . ']" id="reset_options_' . esc_attr( $value['option_name'] ) . '" value="' . $value['option_name'] . '" />
                                    <label for="reset_options_' . esc_attr( $value['option_name'] ) . '">' . esc_attr( $value['option_name'] ) . '</td>
                                <td>' . esc_attr( $value['option_id'] ) . '</td>									
                            </tr>';
		}

		$table_html .= '</tbody>';
		$table_html .= '<tfoot>
                <tr>
                    <th class="row-title">' . esc_attr__( 'Option Name', 'cbxpetition' ) . '</th>
                    <th>' . esc_attr__( 'Option ID', 'cbxpetition' ) . '</th>				
                </tr>
                </tfoot>
            </table>';

		return $table_html;
	} //end method setting_reset_html_table

	/**
	 * get single petition signature count
	 *
	 */
	public static function getMonthlySignatureCounts( $year = null, $petition_id = 0 ) {
		// Initialize array with month names and count set to 0
		$monthly_counts = array_fill_keys(
			[ 'jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec' ],
			0
		);

		$year = intval( $year ) ?: intval( gmdate( 'Y' ) ); // Default to current year if $year is null

		if ( $year > 0 ) {
			global $wpdb;
			$signature_table = $wpdb->prefix . 'cbxpetition_signs';

			// Base SQL query
			$sql = "SELECT MONTH(add_date) as month, COUNT(*) as count 
					FROM $signature_table 
					WHERE state = %s AND YEAR(add_date) = %d";

			// Modify SQL query to include petition ID filter if it's greater than 0
			if ( $petition_id > 0 ) {
				$sql   .= " AND petition_id = %d";
				$sql   .= " GROUP BY MONTH(add_date)";
				$query = $wpdb->prepare( $sql, 'approved', $year, $petition_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			} else {
				$sql   .= " GROUP BY MONTH(add_date)";
				$query = $wpdb->prepare( $sql, 'approved', $year ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			// Execute the query
			$results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter

			// Populate the monthly_counts array with the results
			foreach ( $results as $result ) {
				$month_number                  = intval( $result->month );
				$month_name                    = strtolower( gmdate( 'M', mktime( 0, 0, 0, $month_number, 10 ) ) );
				$monthly_counts[ $month_name ] = intval( $result->count );
			}
		}

		return $monthly_counts;
	}//end function getMonthlySignatureCounts

	/**
	 * Get single petition signature count for each day of the current week
	 */
	public static function getWeeklySignatureCounts( $petition_id = 0 ) {
		// Initialize array with day names and count set to 0
		$weekly_counts = array_fill_keys(
			[ 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat' ],
			0
		);

		global $wpdb;
		$signature_table = $wpdb->prefix . 'cbxpetition_signs';

		// Base SQL query
		$sql = "SELECT DAYOFWEEK(add_date) as day, COUNT(*) as count 
				FROM $signature_table 
				WHERE state = %s AND YEARWEEK(add_date, 1) = YEARWEEK(CURDATE(), 1)";

		// Modify SQL query to include petition ID filter if it's greater than 0
		if ( $petition_id > 0 ) {
			$sql   .= " AND petition_id = %d";
			$sql   .= " GROUP BY DAYOFWEEK(add_date)";
			$query = $wpdb->prepare( $sql, 'approved', $petition_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		} else {
			$sql   .= " GROUP BY DAYOFWEEK(add_date)";
			$query = $wpdb->prepare( $sql, 'approved' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		// Execute the query
		$results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

		// Populate the weekly_counts array with the results
		foreach ( $results as $result ) {
			$day_number                 = intval( $result->day ) - 1; // DAYOFWEEK returns 1 for Sunday, 2 for Monday, etc.
			$day_name                   = strtolower( gmdate( 'D', strtotime( "Sunday +{$day_number} days" ) ) );
			$weekly_counts[ $day_name ] = intval( $result->count );
		}

		return $weekly_counts;
	}// end function getWeeklySignatureCounts

	/**
	 * Get human readable format post status by post id
	 *
	 * @param $post_id
	 *
	 * @return string
	 */
	public static function get_human_readable_post_status( $post_id ) {
		$post_status = get_post_status( $post_id );

		if ( ! $post_status ) {
			return 'Unknown Status';
		}

		$status_object = get_post_status_object( $post_status );

		return $status_object ? $status_object->label : ucfirst( $post_status );
	}//end method get_human_readable_post_status

	/**
	 * Get any plugin version number
	 *
	 * @param $plugin_slug
	 *
	 * @return mixed|string
	 */
	public static function get_any_plugin_version( $plugin_slug = '' ) {
		if ( $plugin_slug == '' ) {
			return '';
		}

		// Ensure the required file is loaded
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Get all installed plugins
		$all_plugins = get_plugins();

		// Check if the plugin exists
		if ( isset( $all_plugins[ $plugin_slug ] ) ) {
			return $all_plugins[ $plugin_slug ]['Version'];
		}

		// Return false if the plugin is not found
		return '';
	}//end method get_pro_addon_version

	/**
	 * Returns codeboxr news feeds using transient cache
	 *
	 * @return false|mixed|\SimplePie\Item[]|null
	 */
	public static function codeboxr_news_feed() {
		$cache_key   = 'codeboxr_news_feed_cache';
		$cached_feed = get_transient( $cache_key );

		$news = false;

		if ( false === $cached_feed ) {
			include_once ABSPATH . WPINC . '/feed.php'; // Ensure feed functions are available
			$feed = fetch_feed( 'https://codeboxr.com/feed?post_type=post' );

			if ( is_wp_error( $feed ) ) {
				return false; // Return false if there's an error
			}

			$feed->init();

			$feed->set_output_encoding( 'UTF-8' );                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        // this is the encoding parameter, and can be left unchanged in almost every case
			$feed->handle_content_type();                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                // this double-checks the encoding type
			$feed->set_cache_duration( 21600 );                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          // 21,600 seconds is six hours
			$limit  = $feed->get_item_quantity( 10 );                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     // fetches the 18 most recent RSS feed stories
			$items  = $feed->get_items( 0, $limit );
			$blocks = array_slice( $items, 0, 10 );

			$news = [];
			foreach ( $blocks as $block ) {
				$url   = $block->get_permalink();
				$url   = PetitionHelper::url_utmy( esc_url( $url ) );
				$title = $block->get_title();

				$news[] = [ 'url' => $url, 'title' => $title ];
			}

			set_transient( $cache_key, $news, HOUR_IN_SECONDS * 6 ); // Cache for 6 hours
		} else {
			$news = $cached_feed;
		}

		return $news;
	}//end method codeboxr_news_feed

	/**
	 * Load mailer
	 *
	 * @since 2.0.0
	 */
	public static function load_mailer() {
		cbxpetition_mailer();
	}//end method load_mailer

	/**
	 * Petition default  categories
	 *
	 * @return mixed|null
	 */
	public static function default_categories() {
		$default_categories = [
			[
				'title'         => 'Government & Politics',
				'slug'          => 'government-politics',
				'subcategories' => [
					[ 'title' => 'Policy Reform', 'slug' => 'policy-reform' ],
					[ 'title' => 'Electoral Transparency', 'slug' => 'electoral-transparency' ],
					[ 'title' => 'Human Rights & Justice', 'slug' => 'human-rights-justice' ],
					[ 'title' => 'Public Safety & Law Enforcement', 'slug' => 'public-safety-law-enforcement' ],
					[ 'title' => 'Anti-Corruption', 'slug' => 'anti-corruption' ],
				]
			],
			[
				'title'         => 'Environment & Sustainability',
				'slug'          => 'environment-sustainability',
				'subcategories' => [
					[ 'title' => 'Climate Action', 'slug' => 'climate-action' ],
					[ 'title' => 'Wildlife Protection', 'slug' => 'wildlife-protection' ],
					[ 'title' => 'Pollution & Waste Management', 'slug' => 'pollution-waste-management' ],
					[ 'title' => 'Forests & Natural Resources', 'slug' => 'forests-natural-resources' ],
					[ 'title' => 'Sustainable Development', 'slug' => 'sustainable-development' ],
				]
			],
			[
				'title'         => 'Society & Culture',
				'slug'          => 'society-culture',
				'subcategories' => [
					[ 'title' => 'Education & Schools', 'slug' => 'education-schools' ],
					[ 'title' => 'Health & Wellbeing', 'slug' => 'health-wellbeing' ],
					[ 'title' => 'Gender Equality', 'slug' => 'gender-equality' ],
					[ 'title' => 'Religious Freedom', 'slug' => 'religious-freedom' ],
					[ 'title' => 'LGBTQ+ Rights', 'slug' => 'lgbtq-rights' ],
				]
			],
			[
				'title'         => 'Children & Youth',
				'slug'          => 'children-youth',
				'subcategories' => [
					[ 'title' => 'Child Protection', 'slug' => 'child-protection' ],
					[ 'title' => 'Youth Empowerment', 'slug' => 'youth-empowerment' ],
					[ 'title' => 'Educational Access', 'slug' => 'educational-access' ],
					[ 'title' => 'School Safety', 'slug' => 'school-safety' ],
				]
			],
			[
				'title'         => 'Animals',
				'slug'          => 'animals',
				'subcategories' => [
					[ 'title' => 'Animal Welfare', 'slug' => 'animal-welfare' ],
					[ 'title' => 'Endangered Species', 'slug' => 'endangered-species' ],
					[ 'title' => 'Ban Animal Cruelty', 'slug' => 'ban-animal-cruelty' ],
					[ 'title' => 'Animal Rights Legislation', 'slug' => 'animal-rights-legislation' ],
				]
			],
			[
				'title'         => 'Business & Consumer Rights',
				'slug'          => 'business-consumer-rights',
				'subcategories' => [
					[ 'title' => 'Corporate Accountability', 'slug' => 'corporate-accountability' ],
					[ 'title' => 'Fair Trade & Labor', 'slug' => 'fair-trade-labor' ],
					[ 'title' => 'Digital Privacy', 'slug' => 'digital-privacy' ],
					[ 'title' => 'Product Safety', 'slug' => 'product-safety' ],
				]
			],
			[
				'title'         => 'Local & Community Issues',
				'slug'          => 'local-community-issues',
				'subcategories' => [
					[ 'title' => 'Neighborhood Safety', 'slug' => 'neighborhood-safety' ],
					[ 'title' => 'Local Infrastructure', 'slug' => 'local-infrastructure' ],
					[ 'title' => 'Community Events', 'slug' => 'community-events' ],
					[ 'title' => 'Zoning & Urban Planning', 'slug' => 'zoning-urban-planning' ],
				]
			],
			[
				'title'         => 'Human Rights & Equality',
				'slug'          => 'human-rights-equality',
				'subcategories' => [
					[ 'title' => 'Racial Justice', 'slug' => 'racial-justice' ],
					[ 'title' => 'Refugee Support', 'slug' => 'refugee-support' ],
					[ 'title' => 'Workers\' Rights', 'slug' => 'workers-rights' ],
					[ 'title' => 'Disability Inclusion', 'slug' => 'disability-inclusion' ],
				]
			],
			[
				'title'         => 'Technology & Internet',
				'slug'          => 'technology-internet',
				'subcategories' => [
					[ 'title' => 'Digital Freedoms', 'slug' => 'digital-freedoms' ],
					[ 'title' => 'Data Privacy', 'slug' => 'data-privacy' ],
					[ 'title' => 'AI & Automation Ethics', 'slug' => 'ai-automation-ethics' ],
					[ 'title' => 'Cybersecurity Awareness', 'slug' => 'cybersecurity-awareness' ],
				]
			],
			[
				'title'         => 'Education & Academia',
				'slug'          => 'education-academia',
				'subcategories' => [
					[ 'title' => 'Student Rights', 'slug' => 'student-rights' ],
					[ 'title' => 'Curriculum Reform', 'slug' => 'curriculum-reform' ],
					[ 'title' => 'Affordable Education', 'slug' => 'affordable-education' ],
					[ 'title' => 'Teacher Support', 'slug' => 'teacher-support' ],
				]
			]
		];

		return apply_filters( 'cbxpetition_default_categories', $default_categories );
	}//end method default_categories

	/**
	 * Create default categories
	 *
	 * @return void
	 *
	 * @since 2.0.3
	 */
	public static function create_default_categories() {
		$saved_version = get_option( 'cbxpetition_version' );
		$count         = cbxpetition_category_count();

		if ( $saved_version === false || $count == 0 ) {
			$categories = PetitionHelper::default_categories();

			$taxonomy = 'cbxpetition_cat';

			foreach ( $categories as $cat ) {
				// Check if parent term exists by slug
				$parent_term = get_term_by( 'slug', $cat['slug'], $taxonomy );

				// If not, insert it
				if ( ! $parent_term ) {
					$parent_term_id = wp_insert_term(
						$cat['title'],
						$taxonomy,
						[
							'slug' => $cat['slug'],
						]
					);


					if ( ! is_wp_error( $parent_term_id ) ) {
						$parent_term = get_term( $parent_term_id['term_id'], $taxonomy );
					}
				}

				$parent_id = $parent_term ? $parent_term->term_id : 0;

				// Insert subcategories
				if ( ! empty( $cat['subcategories'] ) && is_array( $cat['subcategories'] ) ) {
					foreach ( $cat['subcategories'] as $subcat ) {
						$sub_term = get_term_by( 'slug', $subcat['slug'], $taxonomy );

						if ( ! $sub_term ) {
							wp_insert_term(
								$subcat['title'],
								$taxonomy,
								[
									'slug'   => $subcat['slug'],
									'parent' => $parent_id
								]
							);
						}
					}
				}
			}
		}
	}//end method create_default_categories
}//end class PetitionHelper