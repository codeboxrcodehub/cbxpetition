<?php

namespace Cbx\Petition;

use Cbx\Petition\CBXSetting;
use Cbx\Petition\Helpers\PetitionHelper;
use Intervention\Image\ImageManager;

/**
 * Class Admin
 * @package Cbx\Petition\Admin
 */
class CBXPetitionAdmin {
	/**
	 * @var CBXSetting
	 */
	private $settings;

	private $version;

	public function __construct() {
		$this->settings = new CBXSetting();
		$this->version  = CBXPETITION_PLUGIN_VERSION;

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$this->version = current_time( 'timestamp' ); //for development time only
		}
	}//end of constructor

	/**
	 * Create petition custom post type, taxonomies
	 * @since 1.0.0
	 */
	public function post_type_init() {
		PetitionHelper::create_cbxpetition_post_type();

		// Check the option we set on activation.
		if ( get_transient( 'cbxpetition_flush_rewrite_rules' )) {
			flush_rewrite_rules();
			delete_transient( 'cbxpetition_flush_rewrite_rules' );
		}
	}//end method post_type_init

	public function setting_init() {
		//set the settings
		$this->settings->set_sections( $this->get_settings_sections() );
		$this->settings->set_fields( $this->get_settings_fields() );

		//initialize settings
		$this->settings->admin_init();
	}//end method setting_init

	/**
	 * Global Setting Sections and titles
	 *
	 * @return type
	 * @since 1.0.0
	 */
	public function get_settings_sections() {
		return PetitionHelper::get_settings_sections();
	}//end method get_settings_sections

	/**
	 * Global Setting Fields
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_settings_fields() {
		return PetitionHelper::get_settings_fields();
	}//end method get_settings_fields


	/**
	 * Add Admin menu
	 * @since 1.0.0
	 */
	public function admin_menus() {

		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		//petition sign listing
		$sign_listing_page_hook = add_submenu_page( 'edit.php?post_type=cbxpetition',
			esc_html__( 'Signs Listing', 'cbxpetition' ),
			esc_html__( 'Signatures', 'cbxpetition' ),
			'manage_options',
			'cbxpetition-signatures', [
				$this,
				'display_signatures',
			] );


		//add screen option save option
		if ( $page == 'cbxpetition-signatures' && ! isset( $_REQUEST['view'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_action( "load-$sign_listing_page_hook", [ $this, 'display_signatures_screen' ] );
		}

		//add email menu for this plugin
		add_submenu_page( 'edit.php?post_type=cbxpetition',
			esc_html__( 'CBX Petition: Email Manager', 'cbxpetition' ),
			esc_html__( 'Emails', 'cbxpetition' ),
			'manage_options',
			'cbxpetition-emails',
			[ $this, 'admin_menu_display_emails' ], 8
		);

		//add settings for this plugin
		add_submenu_page( 'edit.php?post_type=cbxpetition',
			esc_html__( 'Global Settings', 'cbxpetition' ),
			esc_html__( 'Settings', 'cbxpetition' ),
			'manage_options',
			'cbxpetition-settings',
			[ $this, 'display_settings' ] );

		//add settings for this plugin
		add_submenu_page( 'edit.php?post_type=cbxpetition',
			esc_html__( 'Helps & Updates', 'cbxpetition' ),
			esc_html__( 'Helps & Updates', 'cbxpetition' ),
			'manage_options',
			'cbxpetition-doc',
			[ $this, 'display_support' ] );

	}//end method admin_menus

	/**
	 * Petition sign listing page
	 * @since 1.0.0
	 */
	public function display_signatures() {
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$settings = $this->settings;

		if ( isset( $_GET['view'] ) && sanitize_text_field( wp_unslash( $_GET['view'] ) ) == 'addedit' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			global $wpdb;
			$log_id = ( isset( $_GET['id'] ) && absint( $_GET['id'] ) > 0 ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( $log_id == 0 ) {
				echo esc_html__( 'Invalid signature', 'cbxpetition' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			$sign_info = null;
			if ( $log_id > 0 ) {
				global $wpdb;
				$petition_signature_table = $wpdb->prefix . 'cbxpetition_signs';

				//$sign_info = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $petition_signature_table WHERE id = %d", $log_id ), ARRAY_A );
				$sign_info = PetitionHelper::petitionSignInfo( $log_id );

				if ( ! is_null( $sign_info ) && is_array( $sign_info ) ) {
					$petition_id = isset( $sign_info['petition_id'] ) ? absint( $sign_info['petition_id'] ) : 0;
					$comment     = isset( $sign_info['comment'] ) ? wp_unslash( $sign_info['comment'] ) : '';
					$state       = isset( $sign_info['state'] ) ? $sign_info['state'] : '';

					$state_arr = PetitionHelper::getPetitionSignStates();

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo cbxpetition_get_template_html( 'admin/admin-sign-edit.php',
						[
							'settings'    => $settings,
							'log_id'      => $log_id,
							'petition_id' => $petition_id,
							'sign_info'   => $sign_info,
							'comment'     => $comment,
							'state'       => $state,
							'state_arr'   => $state_arr
						]
					);
				} else {
					echo esc_html__( 'Invalid signature or not found', 'cbxpetition' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}


		} else {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo cbxpetition_get_template_html( 'admin/admin-signs-listing.php', [
				'settings' => $settings
			] );
		}
		//require_once( $template_name );
	}//end method display_signatures

	/**
	 * Add screen option for sign listing
	 * @since 1.0.0
	 */
	public function display_signatures_screen() {
		$option = 'per_page';
		$args   = [
			'label'   => esc_html__( 'Number of signs per page:', 'cbxpetition' ),
			'default' => 50,
			'option'  => 'cbxpetition_sign_results_per_page',
		];
		add_screen_option( $option, $args );
	}//end method display_signatures_screen

	/**
	 * Loads emails menu template
	 *
	 * @since 2.0.0
	 */
	public function admin_menu_display_emails() {
		$settings = $this->settings;

		$mail_helper = cbxpetition_mailer();
		$emails      = $mail_helper->emails;

		$template_data = [ 'settings' => $settings, 'emails' => $emails, 'edit' => 0 ];

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['edit'] ) && $_REQUEST['edit'] != '' ) {
			$email_id              = sanitize_text_field( wp_unslash( $_REQUEST['edit'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$template_data['edit'] = 1;
			$template_data['id']   = $email_id;
		}

		echo cbxpetition_get_template_html( 'admin/email_manager.php', $template_data );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method admin_menu_display_emails

	/**
	 * Display settings
	 * @global type $wpdb
	 * @since 1.0.0
	 */
	public function display_settings() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo cbxpetition_get_template_html( 'admin/settings.php',
			[
				'admin_ref' => $this,
				'settings'  => $this->settings
			]
		);
	}//end method display_settings

	/**
	 * Display settings
	 * @global type $wpdb
	 * @since 1.0.0
	 */
	public function display_support() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo cbxpetition_get_template_html( 'admin/support.php',
			[
				'admin_ref' => $this,
				'settings'  => $this->settings
			] );
	}//end method display_settings

	/**
	 * Tasks or hooks initialize those needed on 'admin_init' hook
	 *
	 * @return void
	 */
	public function admin_init_misc() {
		add_action( 'delete_post', [ $this, 'signature_delete_after_delete_post' ], 10 );
	}//end method admin_init_misc

	/**
	 * Delete signatures on post delete
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	public function signature_delete_after_delete_post( $post_id ) {
		global $wpdb;
		$signatures = PetitionHelper::getSignListingData( '', $post_id, 0, 'all', 'DESC', 'id', - 1 );

		if ( is_array( $signatures ) && count( $signatures ) > 0 ) {
			$signature_table = $wpdb->prefix . 'cbxpetition_signs';


			foreach ( $signatures as $signature ) {
				$signature_id = isset( $signature['id'] ) ? absint( $signature['id'] ) : 0;
				if ( $signature_id == 0 ) {
					continue;
				}

				$petition_id = isset( $signature['petition_id'] ) ? $signature['petition_id'] : 0;
				if ( $petition_id == 0 ) {
					continue;
				}


				//delete the signature
				do_action( 'cbxpetition_sign_delete_before', $signature, $signature_id, $petition_id );
				//now delete
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$sql = $wpdb->prepare( "DELETE FROM $signature_table WHERE id=%d", $signature_id );
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$delete_status = $wpdb->query( $sql );

				if ( $delete_status !== false ) {

					do_action( 'cbxpetition_sign_delete_after', $signature, $signature_id, $petition_id );
				}
			}
		}
	}//end method signature_delete_after_delete_post

	/**
	 * Petition signature delete
	 *
	 * @return void
	 */
	public function petition_sign_delete() {
		$response = [
			'error' => 1
		];

		$errors = [];

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ?? '' ) ), 'cbxpetition_nonce' ) ) {
			$response['message']  = esc_html__( 'Security token verify failed, please refresh or reload.', 'cbxpetition' );
			$response['security'] = 1;
			wp_send_json( $response );
		}


		//$settings = $this->settings;

		$post_data    = wp_unslash( $_POST ); //all needed fields of $_POST is sanitized below
		$signature_id = isset( $post_data['signature_id'] ) ? absint( $post_data['signature_id'] ) : 0;
		$signature    = null;
		$petition_id  = 0;

		if ( ! current_user_can( 'manage_options' ) ) {
			$errors[] = esc_html__( 'Sorry! You are not authorized to delete signature.', 'cbxpetition' );
		} elseif ( $signature_id == 0 ) {
			$errors[] = esc_html__( 'Sorry! Invalid signature id', 'cbxpetition' );
		} else {
			$signature   = PetitionHelper::petitionSignInfo( $signature_id );
			$petition_id = isset( $signature['petition_id'] ) ? absint( $signature['petition_id'] ) : 0;

			if ( $petition_id == 0 ) {
				$errors[] = esc_html__( 'Invalid petition, petition doesn\'t exists or expired.', 'cbxpetition' );
			}
		}

		//validation error
		if ( sizeof( $errors ) > 0 ) {
			$response['errors'] = $errors;
			wp_send_json( $response );
		}


		//now delete the signature

		global $wpdb;
		$signature_table = $wpdb->prefix . 'cbxpetition_signs';

		do_action( 'cbxpetition_sign_delete_before', $signature, $signature_id, $petition_id );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = $wpdb->prepare( "DELETE FROM $signature_table WHERE id=%d", $signature_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$delete_status = $wpdb->query( $sql );

		if ( $delete_status !== false ) {
			do_action( 'cbxpetition_sign_delete_after', $signature, $signature_id, $petition_id );
			$response['message'] = esc_html__( 'Signature deleted successfully.', 'cbxpetition' );
			$response['error']   = 0;
		} else {
			$response['message'] = esc_html__( 'Sorry! Signature delete failed.', 'cbxpetition' );
		}

		wp_send_json( $response );
	}//end method petition_sign_delete

	/**
	 * Petition Signature Edit submit
	 * @since 1.0.0
	 */
	public function petition_sign_edit() {
		$validation_errors = [];

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cbxpetition_token'] ?? '' ) ), 'cbxpetition_nonce' ) ) {
			$validation_errors['message']  = esc_html__( 'Security token verify failed, please refresh or reload.', 'cbxpetition' );
			$validation_errors['security'] = 1;
			wp_send_json( $validation_errors );
		}

		global $wpdb;

		$settings        = $this->settings;
		$current_user    = wp_get_current_user();
		$current_user_id = absint( $current_user->ID );

		$signature_table = $wpdb->prefix . 'cbxpetition_signs';
		$state_arr       = array_keys( PetitionHelper::getPetitionSignStates() );
		$post_data       = wp_unslash( $_POST ); //all needed fields of $_POST is sanitized below

		// sanitization
		$signature_id = isset( $post_data['id'] ) ? absint( $post_data['id'] ) : 0;
		$comment      = isset( $post_data['comment'] ) ? sanitize_textarea_field( wp_unslash( $post_data['comment'] ) ) : '';
		$state        = isset( $post_data['state'] ) ? sanitize_text_field( wp_unslash( $post_data['state'] ) ) : '';

		$signature_url = esc_url( admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-signatures' ) );
		$page_url      = esc_url( add_query_arg( 'view', 'addedit', $signature_url ) );

		// validation

		$signature   = null;
		$petition_id = 0;

		if ( ! current_user_can( 'manage_options' ) ) {
			$validation_errors['top_errors'][] = esc_html__( 'Sorry! You are not authorized to edit signature.', 'cbxpetition' );
		} elseif ( $signature_id == 0 ) {
			$validation_errors['top_errors'][] = esc_html__( 'Sorry! Invalid signature id', 'cbxpetition' );
		} else {
			$signature   = PetitionHelper::petitionSignInfo( $signature_id );
			$petition_id = isset( $signature['petition_id'] ) ? absint( $signature['petition_id'] ) : 0;

			if ( $petition_id == 0 ) {
				$validation_errors['top_errors'][] = esc_html__( 'Invalid petition, petition doesn\'t exists or expired.', 'cbxpetition' );
			}

			if ( $state == '' ) {
				$validation_errors['top_errors'][] = esc_html__( 'Please provide signature state.', 'cbxpetition' );
			}

			if ( ! in_array( $state, $state_arr ) ) {
				$validation_errors['top_errors'][] = esc_html__( 'Unknown signature status', 'cbxpetition' );
			}
		}

		$validation_errors = apply_filters( 'cbxpetition_sign_edit_validation_errors',
			$validation_errors,
			$post_data,
			$signature_id,
			$petition_id );

		if ( sizeof( $validation_errors ) > 0 ) {
			$validation_errors_response['error'] = $validation_errors;
			wp_send_json( $validation_errors_response );
		}

		$data_safe['comment'] = $comment;
		$data_safe['state']   = $state;


		//update
		if ( $signature_id > 0 ) {

			$signature = $old_signature = ( $signature === null ) ? PetitionHelper::petitionSignInfo( $signature_id ) : $signature;

			$data_safe['mod_by']   = $current_user_id;
			$data_safe['mod_date'] = current_time( 'mysql' );

			if($state != 'unverified'){
				$data_safe['activation'] = '';
			}


			$data_safe = apply_filters( 'cbxpetition_sign_edit_before_update_data', $data_safe, $signature_id, $petition_id );

			$where = [
				'id' => $signature_id,
			];

			$where = apply_filters( 'cbxpetition_sign_edit_before_update_where', $where, $signature_id, $petition_id );

			$where_format = [ '%d' ];

			$data_format = [
				'%s', //comment
				'%s', //status
				'%d', //mod_by
				'%s'  //mod_date
			];

			if($state != 'unverified'){
				$data_format[] = '%s';
			}

			$data_format = apply_filters( 'cbxpetition_sign_edit_before_update_col_data_format', $data_format, $signature_id, $petition_id );


			$success_arr  = [];
			$error_arr    = [];
			$response_arr = [];

			do_action( 'cbxpetition_sign_edit_before_update', $signature_id, $petition_id, $data_safe, $data_format, $where, $where_format );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( $wpdb->update( $signature_table, $data_safe, $where, $data_format, $where_format ) !== false ) {

				$signature['comment']  = $data_safe['comment'];
				$signature['state']    = $data_safe['state'];
				$signature['mod_date'] = $data_safe['mod_date'];
				$signature['mod_by']   = $data_safe['mod_by'];


				do_action( 'cbxpetition_sign_edit_after_update', $petition_id, $signature_id, $signature );

				$single_message = [
					'text' => esc_html__( 'Signature updated successfully.', 'cbxpetition' ),
					'type' => 'success',
				];

				$success_arr[] = apply_filters( 'cbxpetition_sign_edit_insert_message', $single_message, $petition_id, $signature_id, $signature );;


				//if no status change then we skip sending any email
				$old_state = $old_signature['state'];
				$new_state = $state;

				if ( $old_state !== $new_state && $new_state == 'approved' ) {
					do_action( 'cbxpetition_sign_log_status_to_' . $new_state, $signature, $old_state, $new_state );
					do_action( 'cbxpetition_sign_log_status_from_' . $old_state . '_to_' . $new_state, $signature, $old_state, $new_state );
				}
				//signature approve event special care
				if ( $old_state !== $new_state && $new_state == 'approved' ) {
					do_action( 'cbxpetition_sign_approved', $signature, $old_state, $new_state );
				}//end signature approve

			} else {
				//update failed
				$single_message = [
					'text' => esc_html__( 'Sorry! Some problem during updating, please try again.', 'cbxpetition' ),
					'type' => 'danger',
				];

				$error_arr[] = $single_message;
			}
		}//end if log id is fine

		$success_arr = apply_filters( 'cbxpetition_sign_edit_success_messages', $success_arr, $signature_id, $petition_id );
		$error_arr   = apply_filters( 'cbxpetition_sign_edit_error_messages', $error_arr, $signature_id, $petition_id );

		$response_arr['success_arr']['messages'] = $success_arr;
		$response_arr['error_arr']['messages']   = $error_arr;

		wp_send_json( $response_arr );
	}//end method petition_sign_edit

	/**
	 * Hook custom meta box
	 *
	 * @since 1.0.0
	 */
	public function meta_boxes_display() {
		//add meta box in left side to show petition setting
		add_meta_box( 'petitioncustom_meta_box', esc_html__( 'CBX Petition Options', 'cbxpetition' ), [
			$this,
			'cbxpetition_metabox_display'
		], 'cbxpetition', 'normal', 'high' );

		//add meta box in right col to show the result
		add_meta_box( 'petitionresult_meta_box',
			esc_html__( 'Petition Result', 'cbxpetition' ),
			[
				$this,
				'metabox_result_display',
			],
			'cbxpetition',
			'side',
			'high' );

		//add meta box in right col to show the shortcode
		add_meta_box( 'petitionshortcode_meta_box',
			esc_html__( 'Shortcode', 'cbxpetition' ),
			[
				$this,
				'cbxpetition_metaboxshortcode_display',
			],
			'cbxpetition',
			'side',
			'high' );
	}//end method meta_boxes_display

	/**
	 * cbx_petition meta data save
	 *
	 * @param $post_id
	 * @param $post
	 *
	 * @since 1.0.0
	 */
	public function petition_meta_save( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// If this is just a revision, don't save
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$post_type = get_post_type( $post_id );
		if ( 'cbxpetition' != $post_type ) {
			return;
		}

		// Check the user's permissions.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing 
		if ( isset( $_POST['post_type'] ) && 'cbxpetition' == sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) ) {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		$petition_meta = isset( $_POST['cbxpetitionmeta'] ) ? wp_unslash( $_POST['cbxpetitionmeta'] ) : [];


		// get signature target of the post
		$signature_target = isset( $petition_meta['signature-target'] ) ? intval( $petition_meta['signature-target'] ) : 0;
		update_post_meta( $post_id, '_cbxpetition_signature_target', $signature_target );

		// get expire date of the post
		$expire_date = isset( $petition_meta['expire-date'] ) ? $petition_meta['expire-date'] : '';
		update_post_meta( $post_id, '_cbxpetition_expire_date', $expire_date );

		// media info
		$media_info = get_post_meta( $post_id, '_cbxpetition_media_info', true );
		if ( ! is_array( $media_info ) ) {
			$media_info = [];
		}

		//video meta informations
		$media_info['video-url']         = isset( $petition_meta['video-url'] ) ? sanitize_text_field( $petition_meta['video-url'] ) : '';
		$media_info['video-title']       = isset( $petition_meta['video-title'] ) ? sanitize_text_field( $petition_meta['video-title'] ) : '';
		$media_info['video-description'] = isset( $petition_meta['video-description'] ) ? wp_kses( $petition_meta['video-description'], PetitionHelper::allowedHtmlTags() ) : '';
		//$media_info['video-description'] = wp_kses( $video_description, PetitionHelper::allowedHtmlTags() );


		update_post_meta( $post_id, '_cbxpetition_media_info', $media_info );
		//end media info


		//petition letter of the post
		$letter = [];

		$petition_letter  = isset( $petition_meta['letter'] ) ? sanitize_textarea_field( $petition_meta['letter'] ) : '';
		$letter['letter'] = wp_kses( $petition_letter, PetitionHelper::allowedHtmlTags() );

		// get petition recipients
		$petition_recipient   = isset( $petition_meta['recipients'] ) ? $petition_meta['recipients'] : [];
		$letter['recipients'] = PetitionHelper::recipient_checkRecipient( $petition_recipient );

		update_post_meta( $post_id, '_cbxpetition_letter', $letter );
	}//end petition_meta_save

	/**
	 * Listing of incoming posts Column Header
	 *
	 * @param $columns
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function columns_header( $columns ) {
		//add cols
		//$columns['shortcode']          = esc_html__( 'Shortcode', 'cbxpetition' );
		$columns['signature_target']   = esc_html__( 'Target', 'cbxpetition' );
		$columns['expire_date']        = esc_html__( 'Expire', 'cbxpetition' );
		$columns['signature_received'] = esc_html__( 'Signatures', 'cbxpetition' );

		return $columns;
	}//end method columns_header

	/**
	 * Listing of form each row of post type.
	 *
	 * @param $column
	 * @param $post_id
	 *
	 * @since 1.0.0
	 */
	public function custom_column_row( $column, $post_id ) {
		$signature_target = get_post_meta( $post_id, '_cbxpetition_signature_target', true );
		$expire_date      = get_post_meta( $post_id, '_cbxpetition_expire_date', true );

		$signature_count = intval( PetitionHelper::petitionSignatureCount( $post_id ) );

		switch ( $column ) {
			/* case 'shortcode':
                echo '<span class="cbxpetitionshortcode">[cbxpetition petition_id="'.intval($post_id).'"]</span><span class="cbxpetition_ctp" aria-label="'.esc_html__('Click to copy', 'cbxpetition').'" data-balloon-pos="down">&nbsp;</span>';
                break;*/
			case 'signature_target':
				echo '<span class="column-signature_target_value">' . absint( $signature_target ) . '</span>';
				break;
			case 'signature_received':
				$signature_url = admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-signatures&petition_id=' . absint( $post_id ) );
				echo '<a class="button outline primary minsize rounded small column-signature_received_value" title="' . esc_attr__( 'View all signatures for this petition', 'cbxpetition' ) . '" href="' . esc_url( $signature_url ) . '">' . absint( $signature_count ) . '</a>';
				break;
			case 'expire_date':
				if ( $expire_date != '' ) {
					echo PetitionHelper::dateShowingFormat( $expire_date ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					esc_html_e( 'Not Set Yet', 'cbxpetition' );
				}
		}
	}//end method custom_column_row

	/**
	 * Sortable count column
	 *
	 * @param $columns
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function custom_column_sortable( $columns ) {
		$columns['signature_target'] = 'signature_target';
		$columns['expire_date']      = 'expire_date';

		return $columns;
	}//end method custom_column_sortable

	/**
	 * @param $actions
	 * @param $post
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function row_actions_petition_listing( $actions, $post ) {
		if ( $post->post_type === 'cbxpetition' ) {
			$post_id                      = intval( $post->ID );
			$signature_url                = admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-signatures&petition_id=' . absint( $post_id ) );
			$actions['cbxpetition_signs'] = '<a href="' . esc_url( $signature_url ) . '">' . esc_html__( 'Signatures', 'cbxpetition' ) . '</a>';
		}

		return $actions;
	}//end method row_actions_petition_listing

	/***
	 * Delete sign on user delete
	 *
	 * @param $user_id
	 *
	 * @since 1.0.0
	 */
	public function on_user_delete_sign_delete( $user_id ) {
		$user_id = absint( $user_id );

		do_action( 'cbxpetition_sign_delete_on_user_delete_before', $user_id );

		global $wpdb;
		$signature_table = $wpdb->prefix . 'cbxpetition_signs';

		//get all signature by this user
		$signatures = PetitionHelper::getSignListingData( '', 0, $user_id, 'all', 'DESC', 'id', - 1 );

		if ( $signatures !== null && is_array( $signatures ) && count( $signatures ) ) {

			foreach ( $signatures as $signature ) {

				$signature_id = absint( $signature['id'] );
				$petition_id  = absint( $signature['petition_id'] );

				do_action( 'cbxpetition_sign_delete_before', $signature, $signature_id, $petition_id );

				if ( $signature !== null && sizeof( $signature ) > 0 ) {
					//now delete
					$sql = $wpdb->prepare( "DELETE FROM $signature_table WHERE id=%d", $signature_id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
					$delete_status = $wpdb->query( $sql );

					if ( $delete_status !== false ) {
						do_action( 'cbxpetition_sign_delete_after', $signature, $signature_id, $petition_id );
					} else {
						do_action( 'cbxpetition_sign_delete_failed', $signature, $signature_id, $petition_id );
					}

				}
			}
		}//end if found data

		do_action( 'cbxpetition_sign_delete_on_user_delete_after', $user_id );
	}//end method on_user_delete_sign_delete

	/**
	 * Petition photo upload via ajax
	 * @since 1.0.0
	 */
	public function petition_admin_photo_upload() {
		$message = [
			'error' => 1,
			'msg'   => '',
			'name'  => null,
			'url'   => null,
		];

		//01. security check
		check_ajax_referer( 'cbxpetition_nonce', 'security' );

		//02. Check permission/capability
		if ( ! current_user_can( 'manage_cbxpetition' ) ) {
			$message['msg'] = esc_html__( 'Sorry, you don\'t have enough permission to upload photo', 'cbxpetition' );
			wp_send_json( $message );
		}

		$submit_data = wp_unslash( $_POST ); //all needed fields of $_POST has been sanitized below

		$setting = $this->settings;


		$petition_id = isset( $submit_data['petition_id'] ) ? absint( $submit_data['petition_id'] ) : 0;


		//03. if no petition id passed then invalid petition
		if ( $petition_id == 0 ) {
			$message['msg'] = esc_html__( 'Photo upload failed, invalid petition.', 'cbxpetition' );
			wp_send_json( $message );
		}

		//04. check if the petition exists or not
		if ( ! PetitionHelper::post_exists( $petition_id ) ) {
			$message['msg'] = esc_html__( 'Photo upload failed, invalid petition.', 'cbxpetition' );
			wp_send_json( $message );
		}


		//$enable_photo     = $setting->get_option( 'enable_photo', 'cbxpetition_general', 'on' );

		$photo_max_width  = intval( $setting->get_option( 'photo_max_width', 'cbxpetition_general', 800 ) );
		$photo_max_height = intval( $setting->get_option( 'photo_max_height', 'cbxpetition_general', 800 ) );

		$thumb_max_width  = intval( $setting->get_option( 'thumb_max_width', 'cbxpetition_general', 400 ) );
		$thumb_max_height = intval( $setting->get_option( 'thumb_max_height', 'cbxpetition_general', 400 ) );


		$photo_max_files     = absint( $setting->get_option( 'photo_max_files', 'cbxpetition_general', 6 ) );    //default maximum 6 photos
		$photo_max_file_size = absint( $setting->get_option( 'photo_max_file_size', 'cbxpetition_general', 2 ) );//mega bytes
		$photo_max_file_size = $photo_max_file_size * 1024 * 1024;

		$photo_file_exts = $setting->get_option( 'photo_allow_filexts', 'cbxpetition_general', [] );
		if ( ! is_array( $photo_file_exts ) ) {
			$photo_file_exts = [];
		}
		$photo_file_exts = array_filter( $photo_file_exts );

		$photo_ext_mimes = [];
		$img_mimes       = PetitionHelper::getImageExtMimes();


		if ( is_array( $photo_file_exts ) && sizeof( $photo_file_exts ) > 0 ) {
			foreach ( $photo_file_exts as $ext ) {
				$ext = strtolower( trim( $ext ) );
				if ( isset( $img_mimes[ $ext ] ) ) {
					$photo_ext_mimes[] = $img_mimes[ $ext ];
				}
			}
		}

		$photo = isset( $_FILES['images'] ) ? $_FILES['images'] : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( $photo ) {
			$file_type = $photo['type'];
			$file_size = $photo['size'];
			//$found_ext = '';

			//05. check if file type valid
			if ( ! in_array( $file_type, $photo_ext_mimes ) ) {
				$message['msg'] = esc_html__( 'Photo upload failed, file type not allowed.', 'cbxpetition' );
				wp_send_json( $message );
			}

			//06. check if file size valid
			if ( $file_size > $photo_max_file_size ) {
				$message['msg'] = esc_html__( 'Photo upload failed, file size is more than allowed.', 'cbxpetition' );
				wp_send_json( $message );
			}

			$img_mimes_flip = array_flip( $img_mimes );
			$found_ext      = $img_mimes_flip[ $file_type ];

			//if the upload dir for cbxpetition is not created then create it
			$dir_info = PetitionHelper::checkUploadDir( $petition_id . '/' );

			$media_info = get_post_meta( $petition_id, '_cbxpetition_media_info', true );
			if ( ! is_array( $media_info ) ) {
				$media_info = [];
			}

			$petition_photos = isset( $media_info['petition-photos'] ) ? $media_info['petition-photos'] : [];

			if ( ! is_array( $petition_photos ) ) {
				$petition_photos = [];
			}

			$media_info_old = $media_info;

			//$message['photo-maxcount'] = $photo_max_files;

			if ( sizeof( $petition_photos ) >= $photo_max_files ) {
				/* translators: %d: Maximum file number  */
				$message['msg']          = sprintf( esc_html__( 'Maximum photo upload limit %d crossed, you can not upload more before delete one.', 'cbxpetition' ), $photo_max_files );
				$message['photos_count'] = sizeof( $petition_photos );
				$message['photos']       = $petition_photos;
				wp_send_json( $message );
			}

			$name            = md5( gmdate( 'Y-m-d H:i:s:u' ) );
			$random_number   = str_pad( wp_rand( 0, 10 ), 2, '0' );
			$photo_file_name = $name . $random_number . '.' . $found_ext;

			global $wp_filesystem;

			// Initialize the WP_Filesystem if it's not already.
			if ( ! $wp_filesystem ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				WP_Filesystem();
			}

			$move_new_file = $wp_filesystem->move( $photo['tmp_name'], $dir_info['dir_part_base_dir'] . $photo_file_name, true );

			if ( $move_new_file ) {
				$petition_photos[]             = $photo_file_name;
				$media_info['petition-photos'] = $petition_photos;

				update_post_meta( $petition_id, '_cbxpetition_media_info', $media_info, $media_info_old );

				$editor = wp_get_image_editor( $dir_info['dir_part_base_dir'] . $photo_file_name, [] );
				if ( ! is_wp_error( $editor ) ) {
					// handle the problem however you deem necessary.

					$dimensions = $editor->get_size();
					$width      = $dimensions['width'];
					$height     = $dimensions['height'];

					if ( $width > $photo_max_width || $height > $photo_max_height ) {
						// Resize the image.
						$result = $editor->resize( $photo_max_width, $photo_max_height, false );
						// If there's no problem, save it; otherwise, print the problem.
						if ( ! is_wp_error( $result ) ) {
							$editor->save( $dir_info['dir_part_base_dir'] . $photo_file_name );

							//create thumb
							$thumbnail_result = $editor->resize( $thumb_max_width, $thumb_max_height, true );

							// If there's no problem, save it; otherwise, print the problem.
							if ( ! is_wp_error( $thumbnail_result ) ) {
								$editor->save( $dir_info['dir_part_base_dir'] . 'thumbnail/' . $photo_file_name );
							} else {
								// Handle the problem however you deem necessary.
							}
						} else {

							// Handle the problem however you deem necessary.
						}
					} else {

						$thumbnail_result = $editor->resize( $thumb_max_width, $thumb_max_height, true );

						// If there's no problem, save it; otherwise, print the problem.
						if ( ! is_wp_error( $thumbnail_result ) ) {
							$editor->save( $dir_info['dir_part_base_dir'] . 'thumbnail/' . $photo_file_name );
						} else {
							// Handle the problem however you deem necessary.
						}
					}

				}


				$message['error']        = 0;
				$message['msg']          = esc_html__( 'Photo uploaded successfully.', 'cbxpetition' );
				$message['url']          = $dir_info['cbxpetition_base_url'] . $petition_id . '/' . $photo_file_name . '?time=' . filemtime( $dir_info['cbxpetition_base_dir'] . $petition_id . '/' . $photo_file_name );
				$message['thumb_url']    = $dir_info['cbxpetition_base_url'] . $petition_id . '/thumbnail/' . $photo_file_name . '?time=' . filemtime( $dir_info['cbxpetition_base_dir'] . $petition_id . '/' . $photo_file_name );
				$message['name']         = $photo_file_name;
				$message['photos_count'] = sizeof( $petition_photos );
				$message['photos']       = $petition_photos;

				wp_send_json( $message );
			}

			$message['msg'] = esc_html__( 'Photo upload failed, no file received or invalid file.', 'cbxpetition' );
			wp_send_json( $message );
		}
	}//end method petition_admin_photo_upload

	/**
	 * Petition photo delete via ajax
	 * @since 1.0.0
	 */
	public function petition_admin_photo_delete() {
		$message = [
			'error'     => 1,
			'msg'       => '',
			'msg_thumb' => '',
		];

		//01. security check
		check_ajax_referer( 'cbxpetition_nonce', 'security' );

		//02. Check permission/capability
		if ( ! current_user_can( 'manage_cbxpetition' ) ) {
			$message['msg'] = esc_html__( 'Sorry, you don\'t have enough permission to delete photo', 'cbxpetition' );
			wp_send_json( $message );
		}

		$submit_data = wp_unslash( $_POST ); //all needed fields of $_POST has been sanitized below

		$setting = $this->settings;

		$petition_id = isset( $submit_data['petition_id'] ) ? absint( $submit_data['petition_id'] ) : 0;
		$filename    = isset( $submit_data['filename'] ) ? sanitize_text_field( $submit_data['filename'] ) : '';


		//03. if no petition id passed then invalid petition
		if ( $petition_id == 0 ) {
			$message['msg'] = esc_html__( 'Photo delete failed, invalid petition.', 'cbxpetition' );
			wp_send_json( $message );
		}

		//04. check if the petition exists or not
		if ( ! PetitionHelper::post_exists( $petition_id ) ) {
			$message['msg'] = esc_html__( 'Photo delete failed, invalid petition.', 'cbxpetition' );
			wp_send_json( $message );
		}

		//if photo enabled and user has capability to manage options then we will allow to delete

		$media_info = get_post_meta( $petition_id, '_cbxpetition_media_info', true );
		if ( ! is_array( $media_info ) ) {
			$media_info = [];
		}

		$media_info_old = $media_info;

		$petition_photos = isset( $media_info['petition-photos'] ) ? wp_unslash( $media_info['petition-photos'] ) : [];
		if ( ! is_array( $petition_photos ) ) {
			$petition_photos = [];
		}

		$message['photos_count'] = sizeof( $petition_photos );

		if ( in_array( $filename, $petition_photos ) ) {
			foreach ( array_keys( $petition_photos, $filename, true ) as $key ) {
				unset( $petition_photos[ $key ] );
			}

			$message['photos_count'] = sizeof( $petition_photos );

			//update post meta to delete photo from db
			$media_info['petition-photos'] = $petition_photos;
			update_post_meta( $petition_id, '_cbxpetition_media_info', $media_info, $media_info_old );

			$dir_info = PetitionHelper::checkUploadDir();

			// Ensure WordPress Filesystem API is loaded
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
			}

			// Initialize the WP_Filesystem
			if ( ! \WP_Filesystem() ) {
				$message['msg'] = esc_html__( 'Failed to initialize the filesystem.', 'cbxpetition' );
				wp_send_json( $message );
			}

			// Get an instance of the WP_Filesystem
			global $wp_filesystem;

			if ( $wp_filesystem->exists( $dir_info['cbxpetition_base_dir'] . $petition_id . '/' . $filename ) && $wp_filesystem->delete( $dir_info['cbxpetition_base_dir'] . $petition_id . '/' . $filename ) ) {
				$message['error'] = 0;
				$message['msg']   = esc_html__( 'Photo deleted successfully.', 'cbxpetition' );

				//delete photo thumb
				if ( $wp_filesystem->delete( $dir_info['cbxpetition_base_dir'] . $petition_id . '/thumbnail/' . $filename ) ) {
					$message['msg_thumb'] = esc_html__( 'Photo thumb deleted successfully.', 'cbxpetition' );
				} else {
					$message['msg_thumb'] = esc_html__( 'Photo thumb deleted failed.', 'cbxpetition' );
				}

				wp_send_json( $message );
			} else {
				$message['error'] = 0;
				$message['msg'] = esc_html__( 'File/photo delete failed, seems doesn\'t exists, removed from database.', 'cbxpetition' );
				wp_send_json( $message );
			}
		} else {
			$message['msg'] = esc_html__( 'Photo delete failed, unknown file name.', 'cbxpetition' );
			wp_send_json( $message );
		}

		wp_send_json( $message );
	}//end method petition_admin_photo_delete

	/**
	 * Delete all photos of a petition
	 *
	 * @return void
	 */
	public function petition_admin_photos_delete() {
		$message = [
			'error'       => 0,
			'success_arr' => [],
			'error_arr'   => [],
			'msg'         => '',
			'msg_thumb'   => ''
		];

		//01. security check
		check_ajax_referer( 'cbxpetition_nonce', 'security' );

		//02. Check permission/capability
		if ( ! current_user_can( 'manage_cbxpetition' ) ) {
			$message['error'] = 1;
			$message['msg']   = esc_html__( 'Sorry, you don\'t have enough permission to delete photos', 'cbxpetition' );
			wp_send_json( $message );
		}

		$submit_data = wp_unslash( $_POST ); //all needed fields of $_POST has been sanitized below

		$setting = $this->settings;

		$petition_id = isset( $submit_data['petition_id'] ) ? absint( $submit_data['petition_id'] ) : 0;


		//03. if no petition id passed then invalid petition
		if ( $petition_id == 0 ) {
			$message['error'] = 1;
			$message['msg']   = esc_html__( 'Photos delete failed, invalid petition.', 'cbxpetition' );
			wp_send_json( $message );
		}

		//04. check if the petition exists or not
		if ( ! PetitionHelper::post_exists( $petition_id ) ) {
			$message['error'] = 1;
			$message['msg']   = esc_html__( 'Photos delete failed, invalid petition.', 'cbxpetition' );
			wp_send_json( $message );
		}

		$media_info = get_post_meta( $petition_id, '_cbxpetition_media_info', true );
		if ( ! is_array( $media_info ) ) {
			$media_info = [];
		}

		$media_info_old = $media_info;

		$petition_photos = isset( $media_info['petition-photos'] ) ? wp_unslash( $media_info['petition-photos'] ) : [];
		if ( ! is_array( $petition_photos ) ) {
			$petition_photos = [];
		}

		$message['photos_count'] = sizeof( $petition_photos );

		//keep a backup of the main files
		$petition_photos_original = $petition_photos;

		$dir_info = PetitionHelper::checkUploadDir();

		// Ensure WordPress Filesystem API is loaded
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}

		// Initialize the WP_Filesystem
		if ( ! \WP_Filesystem() ) {
			$message['error'] = 1;
			$message['msg']   = esc_html__( 'Failed to initialize the filesystem. Photos delete paused.', 'cbxpetition' );
			wp_send_json( $message );
		}

		// Get an instance of the WP_Filesystem
		global $wp_filesystem;

		$error_arr   = [];
		$success_arr = [];

		//delete all photo files one by one
		foreach ( $petition_photos as $petition_photo ) {
			if ( ( $key = array_search( $petition_photo, $petition_photos_original ) ) !== false ) {
				unset( $petition_photos_original[ $key ] );
			}

			if ( $wp_filesystem->exists( $dir_info['cbxpetition_base_dir'] . $petition_id . '/' . $petition_photo ) && $wp_filesystem->delete( $dir_info['cbxpetition_base_dir'] . $petition_id . '/' . $petition_photo ) ) {

				/* translators: %s: Petition photo  */
				$success_arr[ $petition_photo ] = sprintf( esc_html__( 'Photo %s deleted successfully.', 'cbxpetition' ), $petition_photo );
			} else {
				/* translators: %s: Petition photo  */
				$error_arr[ $petition_photo ] = sprintf( esc_html__( 'Photo %s delete failed.', 'cbxpetition' ), $petition_photo );
			}
		}

		//delete thumbnail folder
		if ( $wp_filesystem->delete( $dir_info['cbxpetition_base_dir'] . $petition_id . '/thumbnail', true, 'd' ) ) {
			$message['msg_thumb'] = esc_html__( 'Petition photos thumbnail folder deleted.', 'cbxpetition' );
		} else {
			$message['msg_thumb'] = esc_html__( 'Petition photos thumbnail folder delete failed.', 'cbxpetition' );
		}


		//update meta
		$media_info['petition-photos'] = $petition_photos_original;
		$message['photos_count']       = sizeof( $petition_photos_original );
		update_post_meta( $petition_id, '_cbxpetition_media_info', $media_info, $media_info_old );

		$message['success_arr'] = $success_arr;
		$message['error_arr']   = $error_arr;
		wp_send_json( $message );
	}//end method petition_admin_photos_delete

	/**
	 * Petition banner upload via ajax
	 *
	 * @return void
	 */
	public function petition_admin_banner_upload() {
		$message = [
			'error' => 1,
			'msg'   => '',
			'name'  => null,
			'url'   => null,
		];

		//01. security check
		check_ajax_referer( 'cbxpetition_nonce', 'security' );

		//02. Check permission/capability
		if ( ! current_user_can( 'manage_cbxpetition' ) ) {
			$message['msg'] = esc_html__( 'Sorry, you don\'t have enough permission to upload banner', 'cbxpetition' );
			wp_send_json( $message );
		}

		$submit_data = wp_unslash( $_POST ); //all needed fields of $_POST has been sanitized below

		$setting = $this->settings;

		$petition_id = isset( $submit_data['petition_id'] ) ? absint( $submit_data['petition_id'] ) : 0;


		//03. if no petition id passed then invalid petition
		if ( $petition_id == 0 ) {
			$message['msg'] = esc_html__( 'Banner upload failed, invalid petition.', 'cbxpetition' );
			wp_send_json( $message );
		}

		//04. check if the petition exists or not
		if ( ! PetitionHelper::post_exists( $petition_id ) ) {
			$message['msg'] = esc_html__( 'Banner upload failed, invalid petition.', 'cbxpetition' );
			wp_send_json( $message );
		}


		//$enable_photo     = $setting->get_option( 'enable_photo', 'cbxpetition_general', 'on' );
		$max_width  = intval( $setting->get_option( 'banner_max_width', 'cbxpetition_general', 1500 ) );
		$max_height = intval( $setting->get_option( 'banner_max_height', 'cbxpetition_general', 400 ) );


		$banner_max_file_size = absint( $setting->get_option( 'banner_max_file_size', 'cbxpetition_general', 2 ) );//mega bytes
		$banner_max_file_size = $banner_max_file_size * 1024 * 1024;

		$banner_file_exts = $setting->get_option( 'banner_allow_filexts', 'cbxpetition_general', [] );
		if ( ! is_array( $banner_file_exts ) ) {
			$banner_file_exts = [];
		}
		$banner_file_exts = array_filter( $banner_file_exts );

		$banner_ext_mimes = [];
		$img_mimes        = PetitionHelper::getImageExtMimes();


		if ( is_array( $banner_file_exts ) && sizeof( $banner_file_exts ) > 0 ) {
			foreach ( $banner_file_exts as $ext ) {
				$ext = strtolower( trim( $ext ) );
				if ( isset( $img_mimes[ $ext ] ) ) {
					$banner_ext_mimes[] = $img_mimes[ $ext ];
				}
			}
		}


		$banner = isset( $_FILES['images'] ) ? $_FILES['images'] : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( $banner ) {
			$file_type = $banner['type'];
			$file_size = $banner['size'];
			//$found_ext = '';

			//05. check if file type valid
			if ( ! in_array( $file_type, $banner_ext_mimes ) ) {
				$message['msg'] = esc_html__( 'Banner upload failed, file type not allowed.', 'cbxpetition' );
				wp_send_json( $message );
			}

			//06. check if file size valid
			if ( $file_size > $banner_max_file_size ) {
				$message['msg'] = esc_html__( 'Banner upload failed, file size is more than allowed.', 'cbxpetition' );
				wp_send_json( $message );
			}

			$img_mimes_flip = array_flip( $img_mimes );
			$found_ext      = $img_mimes_flip[ $file_type ];

			//if the upload dir for cbxpetition is not created then create it
			$dir_info = PetitionHelper::checkUploadDir( $petition_id . '/' );

			//delete the previous image first
			$media_info = get_post_meta( $petition_id, '_cbxpetition_media_info', true );
			if ( ! is_array( $media_info ) ) {
				$media_info = [];
			}

			$media_info_old = $media_info;

			// Initialize the WP_Filesystem
			if ( ! \WP_Filesystem() ) {
				$message['msg'] = esc_html__( 'Failed to initialize the filesystem.', 'cbxpetition' );
				wp_send_json( $message );
			}

			// Get an instance of the WP_Filesystem
			global $wp_filesystem;

			$filename = isset($media_info['banner-image'])? $media_info['banner-image'] : '';
			if ( $filename != '' ) {

				//$deleted = @unlink( $dir_info['cbxpetition_base_dir'] .$review_id.'/'. $filename );
				//deleted = wp_delete_file($dir_info['cbxpetition_base_dir'].$petition_id.'/'.$filename);

				if ( $wp_filesystem->delete( $dir_info['cbxpetition_base_dir'] . $petition_id . '/' . $filename ) ) {
					//update meta
					$media_info['banner-image'] = '';
					update_post_meta( $petition_id, '_cbxpetition_media_info', $media_info, $media_info_old );

					$media_info_old = $media_info;
				} else {
					//previous image delete failed
				}
			}


			$name = md5( gmdate( 'Y-m-d H:i:s:u' ) );

			//$banner_file_name = $petition_id.'_banner.'.$found_ext;
			$banner_file_name = $name . '.' . $found_ext;

			global $wp_filesystem;

			// Initialize the WP_Filesystem if it's not already.
			if ( ! $wp_filesystem ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				WP_Filesystem();
			}

			$move_new_file = $wp_filesystem->move( $banner['tmp_name'], $dir_info['dir_part_base_dir'] . $banner_file_name, true );

			if ( $move_new_file ) {
				//resize image

				$media_info                 = get_post_meta( $petition_id, '_cbxpetition_media_info', true );
				$media_info['banner-image'] = $banner_file_name;
				update_post_meta( $petition_id, '_cbxpetition_media_info', $media_info, $media_info_old );


				$editor = wp_get_image_editor( $dir_info['dir_part_base_dir'] . $banner_file_name, [] );
				if ( ! is_wp_error( $editor ) ) {
					// handle the problem however you deem necessary.
					$dimensions = $editor->get_size();
					$width      = $dimensions['width'];
					$height     = $dimensions['height'];

					if ( $width > $max_width || $height > $max_height ) {
						// Resize the image.
						$result = $editor->resize( $max_width, $max_height, false );
						// If there's no problem, save it; otherwise, print the problem.
						if ( ! is_wp_error( $result ) ) {
							$editor->save( $dir_info['dir_part_base_dir'] . $banner_file_name );
						} else {
							// Handle the problem however you deem necessary.
						}
					}

				}

				$message['error'] = 0;
				$message['msg']   = esc_html__( 'Banner uploaded successfully.', 'cbxpetition' );
				$message['url']   = $dir_info['cbxpetition_base_url'] . $petition_id . '/' . $banner_file_name . '?time=' . filemtime( $dir_info['cbxpetition_base_dir'] . $petition_id . '/' . $banner_file_name );
				$message['name']  = $banner_file_name;

				wp_send_json( $message );
			}

		}

		$message['msg'] = esc_html__( 'Banner upload failed, no file received or invalid file.', 'cbxpetition' );
		wp_send_json( $message );
	}//end method petition_admin_banner_upload

	/**
	 * Review rating file delete via ajax from admin side
	 * @since 1.0.0
	 */
	public function petition_admin_banner_delete() {
		$message = [
			'error' => 1,
			'msg'   => esc_html__( 'Banner delete failed.', 'cbxpetition' )
		];

		//01. security check
		check_ajax_referer( 'cbxpetition_nonce', 'security' );

		//02. Check permission/capability
		if ( ! current_user_can( 'manage_cbxpetition' ) ) {
			$message['msg'] = esc_html__( 'Sorry, you don\'t have enough permission to delete banner', 'cbxpetition' );
			wp_send_json( $message );
		}

		$submit_data = wp_unslash( $_POST ); //all needed fields of $_POST has been sanitized below
		$setting     = $this->settings;
		$petition_id = isset( $submit_data['petition_id'] ) ? absint( $submit_data['petition_id'] ) : 0;


		//03. if no petition id passed then invalid petition
		if ( $petition_id == 0 ) {
			$message['msg'] = esc_html__( 'Banner delete failed, invalid petition.', 'cbxpetition' );
			wp_send_json( $message );
		}

		//04. check if the petition exists or not
		if ( ! PetitionHelper::post_exists( $petition_id ) ) {
			$message['msg'] = esc_html__( 'Banner delete failed, invalid petition.', 'cbxpetition' );
			wp_send_json( $message );
		}


		$media_info = get_post_meta( $petition_id, '_cbxpetition_media_info', true );
		if ( ! is_array( $media_info ) ) {
			$media_info = [];
		}

		$media_info_old = $media_info;
		$filename       = $media_info['banner-image'];

		//update meta
		$media_info['banner-image'] = '';
		update_post_meta( $petition_id, '_cbxpetition_media_info', $media_info, $media_info_old );


		//now delete the actual file
		$dir_info = PetitionHelper::checkUploadDir();

		// Ensure WordPress Filesystem API is loaded
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}

		// Initialize the WP_Filesystem
		if ( ! \WP_Filesystem() ) {
			//throw new \Exception( esc_html__( 'Failed to initialize the filesystem.', 'cbxresume' ) );
			$message['msg'] = esc_html__( 'Failed to initialize the filesystem.', 'cbxpetition' );
			wp_send_json( $message );
		}

		// Get an instance of the WP_Filesystem
		global $wp_filesystem;

		//$deleted = @unlink( $dir_info['cbxpetition_base_dir'] .$review_id.'/'. $filename );
		//$deleted = wp_delete_file( $dir_info['cbxpetition_base_dir'] . $petition_id . '/' . $filename );


		if ( $wp_filesystem->exists( $dir_info['cbxpetition_base_dir'] . $petition_id . '/' . $filename ) && $wp_filesystem->delete( $dir_info['cbxpetition_base_dir'] . $petition_id . '/' . $filename ) ) {
			$message['error'] = 0;
			$message['msg']   = esc_html__( 'Banner deleted successfully.', 'cbxpetition' );
			wp_send_json( $message );
		} else {
			$message['error'] = 0;
			$message['msg'] = esc_html__( 'File/photo delete failed, seems doesn\'t exists, removed from database.', 'cbxpetition' );
			wp_send_json( $message );
		}


		wp_send_json( $message );
	}//end method petition_admin_banner_delete

	/**
	 * Post delete hook init
	 * @since 1.0.0
	 */
	public function signature_delete_after_delete_post_init() {
		//add_action( 'delete_post', [ $this, 'signature_delete_after_delete_post' ], 10 );
	}//end method signature_delete_after_delete_post_init

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links.
	 *
	 * @return  array
	 * @since 1.0.0
	 */
	public function plugin_action_links( $links ) {
		$action_links = [
			'settings' => '<a style="color: #f44336 !important; font-weight: bold;" href="' . admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-settings' ) . '" aria-label="' . esc_attr__( 'View settings', 'cbxpetition' ) . '">' . esc_html__( 'Settings', 'cbxpetition' ) . '</a>',
		];

		return array_merge( $action_links, $links );
	}//end method plugin_action_links

	/**
	 * Filters the array of row meta for each/specific plugin in the Plugins list table.
	 * Appends additional links below each/specific plugin on the plugins page.
	 *
	 * @access  public
	 *
	 * @param array $links_array An array of the plugin's metadata
	 * @param string $plugin_file_name Path to the plugin file
	 * @param array $plugin_data An array of plugin data
	 * @param string $status Status of the plugin
	 *
	 * @return  array       $links_array
	 * @since 1.0.0
	 */
	public function plugin_row_meta( $links_array, $plugin_file_name, $plugin_data, $status ) {
		if ( strpos( $plugin_file_name, CBXPETITION_BASE_NAME ) !== false ) {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			$links_array[] = '<a target="_blank" style="color:#f44336 !important; font-weight: bold;" href="https://wordpress.org/support/plugin/cbxpetition/" aria-label="' . esc_attr__( 'Free Support', 'cbxpetition' ) . '">' . esc_html__( 'Free Support', 'cbxpetition' ) . '</a>';
			$links_array[] = '<a target="_blank" style="color:#f44336 !important; font-weight: bold;" href="https://wordpress.org/plugins/cbxpetition/#reviews" aria-label="' . esc_attr__( 'Reviews', 'cbxpetition' ) . '">' . esc_html__( 'Reviews', 'cbxpetition' ) . '</a>';
			$links_array[] = '<a target="_blank" style="color:#f44336 !important; font-weight: bold;" href="https://codeboxr.com/doc/cbxpetition-doc/" aria-label="' . esc_attr__( 'Documentation', 'cbxpetition' ) . '">' . esc_html__( 'Documentation', 'cbxpetition' ) . '</a>';


			if ( in_array( 'cbxpetitionproaddon/cbxpetitionproaddon.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || defined( 'CBXPETITIONPROADDON_PLUGIN_NAME' ) ) {
				//nothing here
			} else {
				$links_array[] = '<a target="_blank" style="color:#f44336 !important; font-weight: bold;" href="https://codeboxr.com/product/cbx-petition-for-wordpress/#downloadarea" aria-label="' . esc_attr__( 'Try Pro Addon', 'cbxpetition' ) . '">' . esc_html__( 'Try Pro Addon', 'cbxpetition' ) . '</a>';
			}
		}

		return $links_array;
	}//end plugin_row_meta

	/**
	 * If we need to do something in upgrader process is completed
	 *
	 */
	public function plugin_upgrader_process_complete() {
		$saved_version = get_option( 'cbxpetition_version' );

		if ( $saved_version === false || version_compare( $saved_version, CBXPETITION_PLUGIN_VERSION, '<' ) ) {
			PetitionHelper::role_cap_assignment();
			PetitionHelper::create_tables();


			add_action( 'init', [ $this, 'plugin_upgrader_process_complete_partial' ] );

			set_transient( 'cbxpetition_flush_rewrite_rules', 1 );
			set_transient( 'cbxpetition_upgraded_notice', 1 );
			update_option( 'cbxpetition_version', CBXPETITION_PLUGIN_VERSION );


			//create default categories
			//PetitionHelper::create_default_categories();
			set_transient( 'cbxpetition_create_cats', 1 );


			//pro addon compatibility
			$this->check_pro_addon();
		}
	}//end plugin_upgrader_process_complete

	/**
	 * Plugin upgrader partial functions
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function plugin_upgrader_process_complete_partial() {
		PetitionHelper::create_pages();
	}//end method plugin_upgrader_process_complete_partial


	/**
	 * Show a notice to anyone who has just installed the plugin for the first time
	 * This notice shouldn't display to anyone who has just updated this plugin
	 */
	public function plugin_activate_upgrade_notices() {
		$activation_notice_shown = false;

		$kiss_html_arr = [
			'strong' => [],
			'a'      => [
				'href'  => [],
				'class' => []
			]
		];

		// Check the transient to see if we've just activated the plugin
		if ( get_transient( 'cbxpetition_activated_notice' ) ) {
			echo '<div class="notice notice-success is-dismissible" style="border-color: #6648fe !important;">';

			echo '<p>';

			//phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
			echo '<img alt="icon" style="float: left; display: inline-block; margin-right: 20px;" src="' . esc_url( CBXPETITION_ROOT_URL . 'assets/images/petition_20.png' ) . '" />';

			/* translators: 1: plugin version 2. codeboxr website url  */
			echo sprintf( wp_kses( __( 'Thanks for installing/deactivating <strong>CBX Petition</strong> V%1$s - <a href="%2$s" target="_blank">Codeboxr Team</a>',
				'cbxpetition' ), $kiss_html_arr ), esc_attr( CBXPETITION_PLUGIN_VERSION ), 'https://codeboxr.com' );

			echo '</p>';

			/* translators: 1: Settings url 2. plugin url  */
			echo '<p>' . sprintf( wp_kses( __( 'Check Plugin <a href="%1$s">Setting</a> and <a href="%2$s" target="_blank"><span class="dashicons dashicons-external"></span> Documentation</a>',
					'cbxpetition' ), $kiss_html_arr ), esc_attr( admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-settings' ) ),
					'https://codeboxr.com/product/cbx-petition-for-wordpress/' ) . '</p>';
			echo '</div>';


			// Delete the transient so we don't keep displaying the activation message
			delete_transient( 'cbxpetition_activated_notice' );

			$this->pro_addon_compatibility_campaign();

			$activation_notice_shown = true;
		}

		// Check the transient to see if we've just activated the plugin
		if ( get_transient( 'cbxpetition_upgraded_notice' ) ) {
			if ( ! $activation_notice_shown ) {
				echo '<div class="notice notice-success is-dismissible" style="border-color: #6648fe !important;">';

				echo '<p>';

				//phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
				echo '<img alt="icon" style="float: left; display: inline-block;  margin-right: 20px;" src="' . esc_url( CBXPETITION_ROOT_URL . 'assets/images/petition_20.png' ) . '"/>';

				/* translators: 1: plugin version 2. team url  */
				echo sprintf( wp_kses( __( 'Thanks for upgrading <strong>CBX Petition</strong> V%1$s - <a href="%2$s" target="_blank">Codeboxr Team</a>',
					'cbxpetition' ), $kiss_html_arr ), esc_attr( CBXPETITION_PLUGIN_VERSION ), 'https://codeboxr.com' );

				echo '</p>';


				echo '<p>';

				/* translators: 1: Settings url 2. plugin url  */
				echo sprintf( wp_kses( __( 'Check Plugin <a href="%1$s">Setting</a> and <a href="%2$s" target="_blank"><span class="dashicons dashicons-external"></span> Documentation</a>',
					'cbxpetition' ), $kiss_html_arr ), esc_attr( admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-settings' ) ),
					'https://codeboxr.com/product/cbx-petition-for-wordpress/' );

				echo '</p>';

				echo '</div>';


				$this->pro_addon_compatibility_campaign();
			}


			// Delete the transient so we don't keep displaying the activation message
			delete_transient( 'cbxpetition_upgraded_notice' );
		}

		if ( get_transient( 'cbxpetition_proaddon_deactivated' ) ) {
			echo '<div class="notice notice-success is-dismissible" style="border-color: #6648fe !important;">';

			echo '<p>';

			//phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
			echo '<img alt="icon" style="float: left; display: inline-block; margin-right: 20px;" src="' . esc_url( CBXPETITION_ROOT_URL . 'assets/images/petition_20.png' ) . '" />';


			esc_html_e( 'Current version of  CBX Petition Pro Addon is not compatible with core  CBX Petition plugin and  CBX Petition Pro Addon is forced deactivate.', 'cbxpetition' );

			echo '</p>';
			echo '</div>';
			delete_transient( 'cbxpetition_proaddon_deactivated' );
		}
	}//end plugin_activate_upgrade_notices

	/**
	 * Check plugin compatibility and pro addon install campaign
	 */
	public function pro_addon_compatibility_campaign() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		//if the pro addon is active or installed
		if ( in_array( 'cbxpetitionproaddon/cbxpetitionproaddon.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || defined( 'CBXPETITIONPROADDON_PLUGIN_NAME' ) ) {
			//plugin is activated

			$plugin_version  = CBXPETITIONPROADDON_PLUGIN_NAME;
			$pro_min_version = '2.0.0';


			if ( version_compare( $plugin_version, $pro_min_version, '<' ) ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'CBX Petition Pro Addon current version is not compatible with the latest Petition core plugin. Please update CBX Petition Pro Addon to version 2.0.0 or later  - Codeboxr Team', 'cbxpetition' ) . '</p></div>';
			}
		} else {
			/* translators: %s: Plugin Link */
			$message = sprintf( __( 'CBX Petition Pro Addon has frontend petition submission features and more extra features, <a target="_blank" href="%s">try it</a> - Codeboxr Team', 'cbxpetition' ), esc_url( 'https://codeboxr.com/product/cbx-petition-for-wordpress/' ) );
			echo '<div class="notice notice-success is-dismissible"><p>' . wp_kses_post( $message ) . '</p></div>';
		}

	}//end method pro_addon_compatibility_campaign

	/**
	 * Show notice about pro addon deactivation
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function check_pro_addon() {
		cbxpetition_check_and_deactivate_plugin( 'cbxpetitionproaddon/cbxpetitionproaddon.php', '2.0.0', 'cbxpetition_proaddon_deactivated' );
	}//end method check_pro_addon

	/**
	 * Vote listing screen option columns
	 *
	 * @param $columns
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function signature_listing_screen_cols( $columns ) {
		$columns = [
			'id'          => esc_html__( 'ID', 'cbxpetition' ),
			'petition_id' => esc_html__( 'Petition', 'cbxpetition' ),
			'f_name'      => esc_html__( 'First Name', 'cbxpetition' ),
			'l_name'      => esc_html__( 'Last Name', 'cbxpetition' ),
			'email'       => esc_html__( 'Email', 'cbxpetition' ),
			'comment'     => esc_html__( 'Comment', 'cbxpetition' ),
			'state'       => esc_html__( 'State', 'cbxpetition' ),
		];

		return apply_filters( 'cbxpetition_signature_listing_hidden_columns', $columns );
	}//end signature_listing_screen_cols

	/**
	 * Set options sign log listing result
	 *
	 * @param $new_state
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function cbxpetition_sign_results_per_page( $new_state, $option, $value ) {
		if ( 'cbxpetition_sign_results_per_page' == $option ) {
			return $value;
		}

		return $new_state;
	}//end method cbxpetition_sign_results_per_page

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @param $hook
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles( $hook ) {
		global $post_type, $post;

		$page = isset( $_GET['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$ver  = $this->version;

		$css_url_part         = CBXPETITION_ROOT_URL . 'assets/css/';
		$css_url_part_vendors = CBXPETITION_ROOT_URL . 'assets/vendors/';
		$js_url_part_vendors  = CBXPETITION_ROOT_URL . 'assets/vendors/';

		wp_register_style( 'awesome-notifications', $css_url_part_vendors . 'awesome-notifications/style.css', [], $ver );
		wp_register_style( 'flatpickr', $css_url_part_vendors . 'flatpickr/flatpickr.min.css', [], $ver );

		wp_register_style( 'cbxpetition-admin', $css_url_part . 'cbxpetition-admin.css', [], $ver );

		wp_register_style( 'cbxpetition-email-manager', $css_url_part . 'cbxpetition-email-manager.css', [], $this->version, 'all' );


		if ( $page == 'cbxpetition-settings' ) {
			wp_register_style( 'select2', $css_url_part_vendors . 'select2/css/select2.min.css', [], $ver );
			wp_register_style( 'pickr', $css_url_part_vendors . 'pickr/themes/classic.min.css', [], $ver );


			wp_register_style( 'cbxpetition-setting', $css_url_part . 'cbxpetition-setting.css', [
				'pickr',
				'awesome-notifications',
				'select2',
				'cbxpetition-admin'
			], $ver );

			wp_enqueue_style( 'select2' );
			wp_enqueue_style( 'pickr' );
			wp_enqueue_style( 'awesome-notifications' );


			wp_enqueue_style( 'cbxpetition-admin' );
			wp_enqueue_style( 'cbxpetition-setting' );
		}//end style adding for setting page

		if ( $page == 'cbxpetition-doc' ) {
			wp_enqueue_style( 'cbxpetition-admin' );
		}//end style adding for doc page


		//$admin_slugs = PetitionHelper::admin_page_slugs();
		$admin_slugs_styles = [ 'cbxpetition-signatures', 'cbxpetition-settings', 'cbxpetition-doc' ];

		if ( ( $hook == 'post.php' || $hook == 'post-new.php' || $hook == 'edit.php' || $hook == 'edit-tags.php' || $hook == 'term.php' ) && $post_type == 'cbxpetition' || in_array( $page, $admin_slugs_styles ) ) {
			wp_enqueue_style( 'flatpickr' );
			wp_enqueue_style( 'awesome-notifications' );

			//upload style
			if ( ( $hook == 'post.php' || $hook == 'post-new.php' ) && $post_type == 'cbxpetition' ) {
				wp_register_style( 'cbxpetition-file-upload', $js_url_part_vendors . 'dm-uploader/css/jquery.dm-uploader.min.css', [], $ver );
				wp_enqueue_style( 'cbxpetition-file-upload' );
			}

			wp_enqueue_style( 'cbxpetition-admin' );
		}

		if ( $page == 'cbxpetition-emails' ) {
			wp_enqueue_style( 'cbxpetition-email-manager' );
		}
	}//end method enqueue_styles

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @param $hook
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		global $post_type, $post;


		//basic vars
		$page     = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$ver      = $this->version;
		$suffix   = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$settings = $this->settings;

		$plus_svg = cbxpetition_esc_svg( cbxpetition_load_svg( 'icon_plus' ) );

		//assets urls
		$js_url_part         = CBXPETITION_ROOT_URL . 'assets/js/';
		$js_url_part_vendors = CBXPETITION_ROOT_URL . 'assets/vendors/';
		$js_url_part_vanila  = CBXPETITION_ROOT_URL . 'assets/js/vanila/';
		$js_url_part_build   = CBXPETITION_ROOT_URL . 'assets/js/build/';

		$img_mimes = PetitionHelper::getImageExtMimes();


		//photo
		$photo_max_files     = $photo_max_size_mb = absint( $settings->get_field( 'photo_max_files', 'cbxpetition_general', 10 ) );
		$photo_max_file_size = absint( $settings->get_field( 'max_file_size', 'cbxpetition_general', 1 ) );        //in mega bytes
		$photo_file_max_mb   = $photo_max_file_size;
		$photo_max_file_size = $photo_max_file_size * 1024 * 1024;
		$photo_file_exts     = $settings->get_field( 'photo_allow_filexts', 'cbxpetition_general', [] );

		if ( ! is_array( $photo_file_exts ) ) {
			$photo_file_exts = [];
		}

		$photo_file_exts = array_keys( $photo_file_exts );
		$photo_file_exts = array_filter( $photo_file_exts );

		$photo_ext_mimes = 'image\/';
		$photo_ext_mimes .= implode( "|", $photo_file_exts );

		//end photo end


		//banner
		$banner_max_file_size = absint( $settings->get_field( 'banner_max_file_size', 'cbxpetition_general', 2 ) );//mega bytes
		$banner_file_max_mb   = $banner_max_file_size;
		$banner_max_file_size = $banner_max_file_size * 1024 * 1024;                                               //bytes

		$banner_file_exts = $settings->get_field( 'banner_allow_filexts', 'cbxpetition_general', [] );


		if ( ! is_array( $banner_file_exts ) ) {
			$banner_file_exts = [];
		}
		$banner_file_exts = array_filter( $banner_file_exts );


		$banner_ext_mimes = 'image\/';
		$banner_exts      = array_keys( $banner_file_exts );
		$banner_ext_mimes .= implode( "|", $banner_file_exts );
		//banner end


		//register vendors
		wp_register_script( 'awesome-notifications', $js_url_part_vendors . 'awesome-notifications/script.js', [], $ver, true );
		wp_register_script( 'select2', $js_url_part_vendors . 'select2/js/select2.full.min.js', [ 'jquery' ], $ver, true );
		wp_register_script( 'flatpickr', $js_url_part_vendors . 'flatpickr/flatpickr.min.js', [ 'jquery' ], $ver, true );
		wp_register_script( 'pickr', $js_url_part_vendors . 'pickr/pickr.min.js', [], $ver, true );
		wp_register_script( 'jquery-validate', $js_url_part_vendors . 'jquery-validation/jquery.validate.min.js', [ 'jquery' ], $ver, true );

		//end  register vendors

		$global_translation = PetitionHelper::global_translation_strings();

		//enqueue js for settings page
		if ( $page == 'cbxpetition-settings' ) {
			//setting js dependencies
			$setting_js_deps = [
				'jquery',
				'jquery-ui-sortable',
				'select2',
				'pickr',
				'awesome-notifications'
			];

			$setting_js_deps = apply_filters( 'cbxpetition_setting_js_deps', $setting_js_deps );

			$setting_js_vars = [
				'global_setting_link_html' => '<a href="' . esc_url( admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-settings' ) ) . '"  class="button outline primary pull-right">' . esc_html__( 'Global Settings', 'cbxpetition' ) . '</a>',
			];

			$setting_js_vars = apply_filters( 'cbxpetition_setting_js_vars', array_merge( $setting_js_vars, $global_translation ) );

			wp_register_script( 'cbxpetition-setting', $js_url_part_vanila . 'cbxpetition-admin-setting.js', $setting_js_deps, $ver, true );
			wp_localize_script( 'cbxpetition-setting', 'cbxpetition_setting_js_var', $setting_js_vars );

			//core
			wp_enqueue_script( 'jquery' );
			wp_enqueue_media();

			//vendors
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'pickr' );
			wp_enqueue_script( 'awesome-notifications' );

			//custom
			wp_enqueue_script( 'cbxpetition-setting' );
		}//end enqueue js for settings page

		//enqueue js for signature listing page
		if ( $page == 'cbxpetition-signatures' ) {
			//signature js dependencies
			$signatures_js_deps = [
				//core
				'jquery',
				//vendors
				'flatpickr',
				'select2',
				'pickr',
				'jquery-validate',
				'awesome-notifications'
			];

			$signatures_js_deps = apply_filters( 'cbxpetition_signatures_js_deps', $signatures_js_deps );

			// Localize the script with new data
			$signatures_js_vars = [];
			$signatures_js_vars = apply_filters( 'cbxpetition_signatures_js_vars', array_merge( $signatures_js_vars, $global_translation ) );

			wp_register_script( 'cbxpetition-admin-signatures', $js_url_part_vanila . 'cbxpetition-admin-signatures.js', $signatures_js_deps, $ver, true );
			wp_localize_script( 'cbxpetition-admin-signatures', 'cbxpetition_signatures_js_vars', $signatures_js_vars );


			//core
			wp_enqueue_script( 'jquery' );

			//vendors
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'flatpickr' );
			wp_enqueue_script( 'pickr' );
			wp_enqueue_script( 'jquery-validate' );
			wp_enqueue_script( 'awesome-notifications' );

			//custom
			wp_enqueue_script( 'cbxpetition-admin-signatures' );
		}//end enqueue js for signature listing page

		//enqueue js for tax page
		if ( ( $hook == 'edit-tags.php' || $hook == 'term.php' ) && $post_type == 'cbxpetition' && $page == '' ) {            //tax js dependencies
			$tax_js_deps = [
				//core
				'jquery',
				//vendors
				'jquery-validate'
			];

			$tax_js_deps = apply_filters( 'cbxpetition_tax_js_deps', $tax_js_deps );

			$new_petition_link_html = '<a href="' . esc_url( admin_url( 'edit.php?post_type=cbxpetition' ) ) . '" class="button secondary icon icon-right icon-inline mr-5"><i  class="cbx-icon">' . $plus_svg . '</i>' . esc_html__( 'New Petition',
					'cbxpetition' ) . '</a>';

			$global_setting_link_html = '<a href="' . esc_url( admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-settings' ) ) . '"  class="button outline primary">' . esc_html__( 'Global Settings', 'cbxpetition' ) . '</a>';

			$tax_js_vars =
				[
					'tax_title_prefix' => esc_html__( 'Petition:', 'cbxpetition' ) . ' ',
					'tags_title'       => esc_html__( 'Petition: Tags', 'cbxpetition' ),
					'category_title'   => esc_html__( 'Petition: Category', 'cbxpetition' ),
					//'new_petition_link_html'   => '<a href="' . esc_url( admin_url( 'edit.php?post_type=cbxpetition' ) ) . '" class="button primary icon icon-right icon-inline mr-5"><i  class="cbx-icon cbx-icon-plus-white"></i>' . esc_html__( 'New Petition', 'cbxpetition' ) . '</a>',
					//'global_setting_link_html' => '<a href="' . esc_url( admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-settings' ) ) . '"  class="button outline primary">' . esc_html__( 'Global Settings', 'cbxpetition' ) . '</a>',
					'tax_new_setting'  => '<div class="wp-heading-wrap-right pull-right">' . $new_petition_link_html . $global_setting_link_html . '</div>'
				];

			$tax_js_vars = apply_filters( 'cbxpetition_tax_js_vars', array_merge( $tax_js_vars, $global_translation ) );

			wp_register_script( 'cbxpetition-admin-tax', $js_url_part_vanila . 'cbxpetition-admin-tax.js', $tax_js_deps, $ver, true );
			wp_localize_script( 'cbxpetition-admin-tax', 'cbxpetition_tax', $tax_js_vars );


			//core
			wp_enqueue_script( 'jquery' );

			//vendors
			wp_enqueue_script( 'jquery-validate' );

			//custom
			wp_enqueue_script( 'cbxpetition-admin-tax' );
		}//end enqueue js for tax page

		//enqueue js petition listing or add/edit screen
		if ( ( $hook == 'post.php' || $hook == 'post-new.php' || $hook == 'edit.php' ) && $post_type == 'cbxpetition' && $page == '' ) {
			$petition_edit_mode = 0;
			$admin_js_deps      = [
				'jquery',
				'flatpickr',
				'select2',
				'pickr',
				'jquery-ui-sortable',
				'awesome-notifications'
			];


			//add/edit
			if ( $hook == 'post.php' || $hook == 'post-new.php' ) {
				$petition_edit_mode = 1;

				//scripts
				wp_register_script( 'mustache', $js_url_part_vendors . 'mustache/mustache.min.js', [ 'jquery' ], $ver, true );

				wp_register_script( 'cbxpetition-file-upload', $js_url_part_vendors . 'dm-uploader/js/jquery.dm-uploader.min.js', [ 'jquery' ], $ver, true );

				$petition_media_js_deps = [
					'cbxpetition-file-upload',
					'mustache'
				];

				$petition_media_js_deps = apply_filters( 'cbxpetition_media_js_deps', $petition_media_js_deps );
				$admin_js_deps          = array_merge( $admin_js_deps, $petition_media_js_deps );
			}


			// Localize the script with new data
			$admin_js_vars =
				[
					'petition_edit_mode'         => $petition_edit_mode,
					'delete_text'                => esc_html__( 'Delete', 'cbxpetition' ),
					'sort_text'                  => esc_html__( 'Sort', 'cbxpetition' ),
					'photo'                      => [
						'exists'                 => 0,
						'data'                   => '',
						'max_files'              => $photo_max_files,
						'file_types'             => $photo_ext_mimes,
						'file_exts'              => $photo_file_exts,
						'max_filesize'           => $photo_max_file_size,
						'error_wrong_file_count' => esc_html__( 'Photo upload failed, maximum allowed reached.', 'cbxpetition' ),
						'error_wrong_file_type'  => esc_html__( 'Photo upload failed, wrong file type.', 'cbxpetition' ),
						'error_wrong_file_ext'   => esc_html__( 'Photo upload failed, wrong file extension.', 'cbxpetition' ),
						/* translators: %d: photo maximum size in mb  */
						'error_wrong_file_size'  => sprintf( esc_html__( 'Photo upload failed, wrong file size(Max %d MB).', 'cbxpetition' ), $photo_max_size_mb ),
					],
					'banner'                     => [
						'exists'                => 0,
						'data'                  => '',
						'file_types'            => $banner_ext_mimes,
						'file_exts'             => $photo_file_exts,
						'max_filesize'          => $banner_max_file_size,
						'error_wrong_file_type' => esc_html__( 'Banner upload failed, wrong file type.', 'cbxpetition' ),
						'error_wrong_file_ext'  => esc_html__( 'Banner upload failed, wrong file extension.', 'cbxpetition' ),
						/* translators: %d: photo maximum size in mb  */
						'error_wrong_file_size' => sprintf( esc_html__( 'Banner upload failed, wrong file size(Max %d MB).', 'cbxpetition' ), $banner_file_max_mb ),
					],
					// phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
					'global_setting_link_html'   => '<a href="' . esc_url( admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-settings' ) ) . '"  class="button outline primary pull-right">' . esc_html__( 'Global Settings', 'cbxpetition' ) . '</a>',
					'tax_title_prefix'           => esc_html__( 'Petition:', 'cbxpetition' ) . ' ',
					'petition_title_label'       => esc_attr__( 'Petition Title', 'cbxpetition' ),
					'petition_title_placeholder' => esc_attr__( 'Petition title here', 'cbxpetition' )
				];

			$admin_js_vars = apply_filters( 'cbxpetition_admin_js_vars', array_merge( $admin_js_vars, $global_translation ) );
			$admin_js_deps = apply_filters( 'cbxpetition_admin_js_deps', $admin_js_deps );

			wp_register_script( 'cbxpetition-admin', $js_url_part_vanila . 'cbxpetition-admin.js', $admin_js_deps, $ver, true );
			wp_localize_script( 'cbxpetition-admin', 'cbxpetition_admin_js_vars', $admin_js_vars );

			//core
			wp_enqueue_script( 'jquery' );
			wp_enqueue_media();


			//vendors
			wp_enqueue_script( 'flatpickr' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			if ( $petition_edit_mode ) {
				foreach ( $petition_media_js_deps as $handle ) {
					wp_enqueue_script( $handle );
				}
			}

			//wp_enqueue_script('mustache');
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'pickr' );
			wp_enqueue_script( 'awesome-notifications' );


			//custom
			wp_enqueue_script( 'cbxpetition-admin' );
		}//end enqueue js petition listing or add/edit screen


	}//end method enqueue_scripts

	/**
	 * Renders metabox in right col to show result
	 */
	public function metabox_result_display() {
		global $post, $pagenow;

		$petition_output = '';
		if ( $pagenow == 'post.php' ) {
			$post_id                = intval( $post->ID );
			$signature_target       = cbxpetition_petitionSignatureTarget( $post_id );
			$signature_count        = cbxpetition_petitionSignatureCount( $post_id );
			$signature_target_ratio = cbxpetition_petitionSignatureTargetRatio( $post_id );
			$expire_date            = cbxpetition_petitionExpireDate( $post_id );

			/* translators: %1$d: signature count , %2$d: signature target, %3$s: signature target ratio */
			$petition_output .= '<p>' . sprintf( esc_html__( 'Signatures: %1$d of %2$d (%3$s)', 'cbxpetition' ),
					$signature_count,
					$signature_target,
					$signature_target_ratio . '%' ) . '</p>';
			/* translators: %s: Petition expire date  */
			$petition_output .= '<p>' . sprintf( esc_html__( 'Expiry Date: %s', 'cbxpetition' ), $expire_date ) . '</p>';
		}

		echo $petition_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		do_action( 'cbxpetition_metabox_result_display' );
	}//end method metabox_result_display

	/**
	 * Show cbxpetition meta box in petition edit screen
	 */
	public function cbxpetition_metabox_display() {
		global $post;

		$prefix = '_cbxpetition_';

		if ( isset( $post->ID ) && $post->ID > 0 ) {
			// include petition meta form
			$settings = $this->settings;

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo cbxpetition_get_template_html( 'admin/admin-metabox.php', [
				'settings' => $settings,
				'post'     => $post,
				'prefix'   => $prefix
			] );
		}
	}//end method cbxpetition_metabox_display

	/**
	 * Renders metabox in right col to show shortcode with copy to clipboard
	 */
	public function cbxpetition_metaboxshortcode_display() {
		global $post;
		$post_id = absint( $post->ID );

		//echo '<span class="cbxpetitionshortcode">[cbxpetition petition_id="' . absint( $post_id ) . '"]</span><span class="cbxpetition_ctp" aria-label="' . esc_html__( 'Click to copy', 'cbxpetition' ) . '" data-balloon-pos="down">&nbsp;</span>';


		echo '<div class="cbxshortcode-wrap">';
		echo '<span data-clipboard-text=\'[cbxpetition petition_id="' . absint( $post_id ) . '"]\' title="' . esc_attr__( 'Click to copy',
				'cbxpetition' ) . '" id="cbxpetitionshortcode-' . absint( $post_id ) . '" class="cbxshortcode cbxshortcode-edit cbxshortcode-' . absint( $post_id ) . '">[cbxpetition petition_id="' . absint( $post_id ) . '"]</span>';
		echo '<span class="cbxballon_ctp_btn cbxballon_ctp" aria-label="' . esc_attr__( 'Click to copy', 'cbxpetition' ) . '" data-balloon-pos="up"><i></i></span>';
		echo '</div>';


		echo '<div class="clear"></div>';
	}//end method cbxpetition_metaboxshortcode_display

	/**
	 * Load setting html
	 *
	 * @return void
	 */
	public function settings_reset_load() {
		//security check
		check_ajax_referer( 'cbxpetition_nonce', 'security' );

		$msg            = [];
		$msg['html']    = '';
		$msg['message'] = esc_html__( 'CBX Petition reset setting html loaded successfully', 'cbxpetition' );
		$msg['success'] = 1;

		if ( ! current_user_can( 'manage_options' ) ) {
			$msg['message'] = esc_html__( 'Sorry, you don\'t have enough permission', 'cbxpetition' );
			$msg['success'] = 0;
			wp_send_json( $msg );
		}

		$msg['html'] = PetitionHelper::setting_reset_html_table();

		wp_send_json( $msg );
	} //end method settings_reset_load

	/**
	 * Full plugin reset and redirect
	 */
	public function plugin_options_reset() {
		//security check
		check_ajax_referer( 'cbxpetition_nonce', 'security' );


		$url = admin_url( 'admin.php?page=cbxpetition-settings' );

		$msg            = [];
		$msg['message'] = esc_html__( 'CBX Petition setting options reset successfully', 'cbxpetition' );
		$msg['success'] = 1;
		$msg['url']     = $url;

		if ( ! current_user_can( 'manage_options' ) ) {
			$msg['message'] = esc_html__( 'Sorry, you don\'t have enough permission', 'cbxpetition' );
			$msg['success'] = 0;
			wp_send_json( $msg );
		}

		do_action( 'cbxpetition_plugin_reset_before' );

		$plugin_resets = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing

		//delete options
		$reset_options = isset( $plugin_resets['reset_options'] ) ? $plugin_resets['reset_options'] : [];
		$option_values = ( is_array( $reset_options ) && sizeof( $reset_options ) > 0 ) ? array_values( $reset_options ) : array_values( PetitionHelper::getAllOptionNames() );

		foreach ( $option_values as $key => $option ) {
			delete_option( $option );
		}

		do_action( 'cbxpetition_plugin_option_delete' );
		do_action( 'cbxpetition_plugin_reset_after' );
		do_action( 'cbxpetition_plugin_reset' );

		wp_send_json( $msg );
	} //end plugin_reset


	/**
	 * Permalink cache clear
	 *
	 * @return void
	 */
	public function permalink_cache_clear(): void {
		//security check
		check_ajax_referer( 'cbxpetition_nonce', 'security' );

		$msg            = [];
		$msg['message'] = esc_html__( 'Permalink cache cleared successfully', 'cbxpetition' );
		$msg['success'] = 1;

		if ( ! current_user_can( 'manage_options' ) ) {
			$msg['message'] = esc_html__( 'Sorry, you don\'t have enough permission', 'cbxpetition' );
			$msg['success'] = 0;
			wp_send_json( $msg );
		}

		flush_rewrite_rules();

		wp_send_json( $msg );
	} //end method permalink_cache_clear

	/**
	 * Save email/notification setting
	 *
	 * @return void
	 */
	public function save_email_setting() {
		if ( isset( $_REQUEST['cbxpetition_email_edit'] ) ) {
			$email_id = isset( $_POST['email_id'] ) ? sanitize_text_field( wp_unslash( $_POST['email_id'] ) ) : '';
			$nonce    = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $email_id != '' ) {
				if ( ! wp_verify_nonce( $nonce, 'cbxpetition_email_edit_' . $email_id ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					die( esc_html__( 'Security check failed!', 'cbxpetition' ) );
				} else {
					// Do stuff here.
					$admin_url    = admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-emails' );
					$redirect_url = add_query_arg( [ 'edit' => $email_id ], $admin_url );

					$mail_helper = cbxpetition_mailer();
					$emails      = $mail_helper->emails;
					$email       = $emails[ $email_id ];
					$form_fields = $email->form_fields;
					$settings    = $email->settings;

					foreach ( $form_fields as $field_key => $form_field ) {
						if ( isset( $_POST[ $field_key ] ) ) {
							$type = $form_field['type'];
							if ( $type == 'checkbox' ) {
								$settings[ $field_key ] = sanitize_text_field( wp_unslash( $_POST[ $field_key ] ) );
							} elseif ( $type == 'textarea' ) {
								$settings[ $field_key ] = sanitize_textarea_field( wp_unslash( $_POST[ $field_key ] ) );
							} else {
								$settings[ $field_key ] = sanitize_text_field( wp_unslash( $_POST[ $field_key ] ) );
							}
						} else {
							$settings[ $field_key ] = $form_field['default'];
						}
					}

					$email_options = get_option( 'cbxpetition_emails', [] );

					$email_options[ $email_id ] = $settings;
					update_option( 'cbxpetition_emails', $email_options );

					wp_safe_redirect( $redirect_url );
					exit;
				}
			} else {
				die( esc_html__( 'Sorry, invalid email id', 'cbxpetition' ) );
			}
		}
	}//end method save_email_setting

	/**
	 * Show plugin update
	 *
	 * @param $plugin_file
	 * @param $plugin_data
	 *
	 * @return void
	 */
	public function custom_message_after_plugin_row_proaddon($plugin_file, $plugin_data){
		if ( $plugin_file !== 'cbxpetitionproaddon/cbxpetitionproaddon.php' ) {
			return;
		}

		if(defined('CBXPETITIONPROADDON_PLUGIN_NAME')) return;

		$pro_addon_version = PetitionHelper::get_any_plugin_version('cbxpetitionproaddon/cbxpetitionproaddon.php');
		$pro_latest_version  = '2.0.2';

		if($pro_addon_version != '' && version_compare( $pro_addon_version, $pro_latest_version, '<' ) ){
			// Custom message to display

			//$plugin_setting_url = admin_url( 'admin.php?page=cbxpetition_settings#cbxpetition_licences' );
			$plugin_manual_update = 'https://codeboxr.com/manual-update-pro-addon/';


			/* translators:translators: %s: plugin setting url for licence */
			$custom_message     = wp_kses(sprintf( __( '<strong>Note:</strong> CBX Petition Pro Addon is custom plugin. This plugin can not be auto update from dashboard/plugin manager. For manual update please check <a target="_blank" href="%1$s">documentation</a>. <strong style="color: red;">It seems this plugin\'s current version is older than %2$s . To get the latest pro addon features, this plugin needs to upgrade to %2$s or later.</strong>', 'cbxpetition' ), esc_url( $plugin_manual_update ), $pro_latest_version ), ['strong' => ['style' => []],'a' => ['href' => [], 'target' => []]]);

			// Output a row with custom content
			echo '<tr class="plugin-update-tr">
            <td colspan="3" class="plugin-update colspanchange">
                <div class="notice notice-warning inline">
                    ' . wp_kses_post( $custom_message ) . '
                </div>
            </td>
          </tr>';
		}
	}//end method custom_message_after_plugin_row_proaddon

	/**
	 * Create default category on plugin activation
	 *
	 * @return void
	 */
	public function create_default_category() {
		if ( get_transient( 'cbxpetition_create_cats' ) ) {
			PetitionHelper::create_default_categories(); //from V2.0.3

			delete_transient('cbxpetition_create_cats');
		}
	}//end method create_default_category
}//end class Admin