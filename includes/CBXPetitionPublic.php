<?php

namespace Cbx\Petition;

use Cbx\Petition\CBXSetting;
use Cbx\Petition\Helpers\PetitionHelper;

//classic widgets
use Cbx\Petition\Widgets\Classic\CBXPetitionLatestWidget;
use Cbx\Petition\Widgets\Classic\CBXPetitionSignformWidget;
use Cbx\Petition\Widgets\Classic\CBXPetitionSummaryWidget;

/**
 * Public Class
 */
class CBXPetitionPublic {

	private $settings;
	private $version;

	public function __construct() {
		$this->settings = new CBXSetting();
		$this->version  = CBXPETITION_PLUGIN_VERSION;

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$this->version = current_time( 'timestamp' ); //for development time only
		}
	}//end constructor

	/**
	 * @param $content
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function auto_integration( $content ) {
		if ( is_admin() ) {
			return $content;
		}

		if ( in_array( 'get_the_excerpt', $GLOBALS['wp_current_filter'] ) ) {
			return $content;
		}

		global $post;

		$post_type = isset( $post->post_type ) ? $post->post_type : '';
		if ( $post_type !== 'cbxpetition' ) {
			return $content;
		}

		$settings = $this->settings;

		$before_content = '';
		$after_content  = '';
		$petition_id    = absint( $post->ID );

		$enable_auto_integration = $settings->get_field( 'enable_auto_integration', 'cbxpetition_general', 'on' );

		if ( $enable_auto_integration == 'on' && is_singular( 'cbxpetition' ) ) {

			$auto_integration_before = $settings->get_field( 'auto_integration_before', 'cbxpetition_general', [] );
			$auto_integration_after  = $settings->get_field( 'auto_integration_after', 'cbxpetition_general', [] );

			//$before_content .= do_shortcode( '[cbxpetition_banner]' );
			//$before_content .= do_shortcode( '[cbxpetition_stat]' );
			if ( is_array( $auto_integration_before ) && sizeof( $auto_integration_before ) > 0 ) {
				foreach ( $auto_integration_before as $short_key ) {

					$before_content .= do_shortcode( '[' . esc_html( $short_key ) . ']' );
				}
			}


			/*$after_content .= do_shortcode( '[cbxpetition_signform]' );
			$after_content .= do_shortcode( '[cbxpetition_video]' );
			$after_content .= do_shortcode( '[cbxpetition_photos]' );
			$after_content .= do_shortcode( '[cbxpetition_letter]' );
			$after_content .= do_shortcode( '[cbxpetition_signatures]' );*/

			if ( is_array( $auto_integration_after ) && sizeof( $auto_integration_after ) > 0 ) {
				foreach ( $auto_integration_after as $short_key ) {
					if ( in_array( $short_key, $auto_integration_before ) ) {
						continue;
					}

					$after_content .= do_shortcode( '[' . esc_html( $short_key ) . ']' );
				}
			}
		}

		$content_details_before = '<div class="cbxpetition_content_details cbxpetition_content_details_' . absint($petition_id) . '" id="cbxpetition_content_details_' . absint($petition_id) . '">';
		$content_details_after  = '</div>';

		$content = apply_filters( 'cbxpetition_content_details_before', $content_details_before, $petition_id ) . $content . apply_filters( 'cbxpetition_content_details_after', $content_details_after, $petition_id );

		return $before_content . $content . $after_content;
	}//end method auto_integration;

	/**
	 * Register Classic Widgets
	 *
	 * @since 1.0.0
	 */
	public function init_widgets() {
		register_widget( \Cbx\Petition\Widgets\Classic\CBXPetitionSummaryWidget::class );  //petition summary widget
		register_widget( \Cbx\Petition\Widgets\Classic\CBXPetitionSignformWidget::class ); //petition sign form widget
		register_widget( \Cbx\Petition\Widgets\Classic\CBXPetitionLatestWidget::class );   //petition slider widget
	}//end method init_widgets

	/**
	 * Extra query vars adding for dynamic url
	 *
	 * @param array $vars
	 *
	 * @return array
	 * @since 1.0.4
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'cbxpetitionsign_verification';

		return $vars;
	}//end method add_query_vars

	/**
	 * Rewrite query url rules
	 *
	 * @since 1.0.0
	 */
	public function rewrite_rules() {
		add_rewrite_rule( 'petition-verify' . '/([^/]*)/?', 'index.php?cbxpetitionsign_verification=$matches[1]', 'top' );
	}//end method rewrite_rules

	/**
	 * public template_redirect callback to process guest email activation
	 * @since 1.0.0
	 */
	public function guest_email_validation() {
		global $wp_query;

		if ( array_key_exists( 'cbxpetitionsign_verification', $wp_query->query_vars ) ) {
			//Guest email verification: if guest email user redirect back to site by clicking activation link
			//if ( get_query_var( 'cbxpetitionsign_verification' ) ) {
			PetitionHelper::cbxpetition_public_styles();

			$settings = $this->settings;
			global $wpdb;
			$signature_table = $wpdb->prefix . 'cbxpetition_signs';

			$activation_code = sanitize_text_field( wp_unslash( get_query_var( 'cbxpetitionsign_verification' ) ) );

			if ( $activation_code == '' ) {
				//activation code empty
				$confirmation_message = '<div class="cbxpetition-alert cbxpetition-alert-danger">';
				$confirmation_message .= '<p>' . esc_html__( 'Sorry, no activation code found. Please follow correct url from your email notification.', 'cbxpetition' ) . '</p>';
				$confirmation_message .= '<p><a class="button primary" href="' . esc_url( home_url() ) . '">' . esc_html__( 'Click to go home', 'cbxpetition' ) . '</a></p>';
				$confirmation_message .= '</div>';

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo cbxpetition_get_template_html( 'verification.php', [ 'confirmation_message' => $confirmation_message ] );
				exit();
			}

			if ( is_user_logged_in() ) {
				//guest should verify but found a logged in user
				$confirmation_message = '<div class="cbxpetition-alert cbxpetition-alert-danger">';
				$confirmation_message .= '<p>' . esc_html__( 'Sorry, seems you are currently logged in as system user but this verification process is for guest user only.', 'cbxpetition' ) . '</p>';
				$confirmation_message .= '<p><a class="button primary" href="' . esc_url( home_url() ) . '">' . esc_html__( 'Click to go home', 'cbxpetition' ) . '</a></p>';
				$confirmation_message .= '</div>';

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo cbxpetition_get_template_html( 'verification.php', [ 'confirmation_message' => $confirmation_message ] );
				exit();
			}


			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sign_info = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $signature_table WHERE activation = %s", $activation_code ) );

			$confirmation_message = '';

			//if sign log found
			if ( $sign_info !== null ) {

				$log_id      = absint( $sign_info->id );
				$petition_id = absint( $sign_info->petition_id );


				$state_on_verify = $settings->get_field( 'state_on_verify', 'cbxpetition_general', 'approved' );

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
				$update_status = $wpdb->update(
					$signature_table,
					[
						'activation' => '',
						'state'      => $state_on_verify,
						'mod_date'   => current_time( 'mysql' ),
					],
					[
						'activation' => $activation_code,
						'id'         => $log_id
					],
					[
						'%s',
						'%s',
						'%s',
					],
					[
						'%s',
						'%d',
					]
				);

				$petition_url = esc_url( get_permalink( $petition_id ) );

				//sign log found and updated
				if ( $update_status !== false && intval( $update_status ) > 0 ) {
					$confirmation_message = '<div class="cbxpetition-alert cbxpetition-alert-success">';
					$confirmation_message .= '<p>' . esc_html__( 'Signature validated successfully. No email will be sent to inform this. Site admin will check your request and signature confirmation will be set as per system setting.', 'cbxpetition' ) . '</p>';
					$confirmation_message .= '<p><a class="button primary" href="' . esc_url( $petition_url ) . '">' . esc_html__( 'Click to go petition page', 'cbxpetition' ) . '</a></p>';
					$confirmation_message .= '</div>';

				} else {
					//failed to update sign log
					$confirmation_message = '<div class="cbxpetition-alert cbxpetition-alert-warning">';
					$confirmation_message .= '<p>' . esc_html__( 'Sorry, signature found but validation failed.', 'cbxpetition' ) . '</p>';
					$confirmation_message .= '<p><a class="button primary" href="' . esc_url( $petition_url ) . '">' . esc_html__( 'Click to go petition page', 'cbxpetition' ) . '</a></p>';
					$confirmation_message .= '</div>';

				}

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo cbxpetition_get_template_html( 'verification.php', [ 'confirmation_message' => $confirmation_message ] );

				exit();

			} else {
				//sign log not found or already activated
				$confirmation_message = '<div class="cbxpetition-alert cbxpetition-alert-info">';
				$confirmation_message .= '<p>' . esc_html__( 'Sorry, signature not found or already validated.', 'cbxpetition' ) . '</p>';
				$confirmation_message .= '<p><a class="button primary" href="' . esc_url( home_url() ) . '">' . esc_html__( 'Click to go home', 'cbxpetition' ) . '</a></p>';
				$confirmation_message .= '</div>';

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo cbxpetition_get_template_html( 'verification.php', [ 'confirmation_message' => $confirmation_message ] );
				exit();
			}
			//}
		}
	}//end method guest_email_validation

	/**
	 * Store petition sign by ajax request
	 * @since 1.0.0
	 */
	public function petition_sign_submit() {
		//if frontend sign submit and also nonce verified then go
		// phpcs:ignore WordPress.Security.NonceVerification.Missing 
		if ( ( isset( $_POST['cbxpetition_sign_submit'] ) && absint( $_POST['cbxpetition_sign_submit'] ) == 1 ) ) {
			$validation_errors = [];

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cbxpetition_token'] ?? '' ) ), 'cbxpetition_nonce' ) ) {
				$validation_errors['message']  = esc_html__( 'Security token verify failed, please refresh or reload.', 'cbxpetition' );
				$validation_errors['security'] = 1;
				wp_send_json( $validation_errors );
			}

			$settings         = $this->settings;
			$allow_guest_sign = $settings->get_field( 'allow_guest_sign', 'cbxpetition_general', 'yes' );

			if ( ! is_user_logged_in() && $allow_guest_sign != 'yes' ) {
				$validation_errors['message']  = esc_html__( 'Sorry guest user not allowed to vote', 'cbxpetition' );
				$validation_errors['security'] = 1;
				wp_send_json( $validation_errors );
			}

			global $wpdb;

			$signature_table = $wpdb->prefix . 'cbxpetition_signs';
			$post_data       = wp_unslash( $_POST ); //data is sanitized later below using $post_data

			$user_id = 0;
			$guest   = true;

			if ( is_user_logged_in() ) {
				$guest = false;

				$current_user = wp_get_current_user();
				$user_id      = absint( $current_user->ID );

				$first_name = isset( $current_user->first_name ) ? $current_user->first_name : '';
				$last_name  = isset( $current_user->last_name ) ? $current_user->last_name : '';

				if ( $first_name == '' && $last_name == '' ) {
					$first_name = isset( $current_user->display_name ) ? $current_user->display_name : '';
				}

				$email = $current_user->user_email;

			} else {
				$first_name = isset( $post_data['cbxpetition-fname'] ) ? sanitize_text_field( wp_unslash( $post_data['cbxpetition-fname'] ) ) : '';
				$last_name  = isset( $post_data['cbxpetition-lname'] ) ? sanitize_text_field( wp_unslash( $post_data['cbxpetition-lname'] ) ) : '';
				$email      = isset( $post_data['cbxpetition-email'] ) ? sanitize_email( $post_data['cbxpetition-email'] ) : '';
			}

			$privacy = isset( $post_data['cbxpetition-privacy'] ) ? absint( $post_data['cbxpetition-privacy'] ) : 0;

			// sanitization
			$petition_id = isset( $post_data['cbxpetition-id'] ) ? absint( $post_data['cbxpetition-id'] ) : 0;
			$comment     = isset( $post_data['cbxpetition-comment'] ) ? sanitize_textarea_field( $post_data['cbxpetition-comment'] ) : '';

			$page_url = home_url( add_query_arg( null, null ) );


			if ( $petition_id == 0 ) {
				$validation_errors['top_errors'][] = esc_html__( 'Invalid petition, petition doesn\'t exists or expired.', 'cbxpetition' );
			} else {
				if ( $guest ) {
					if ( strlen( $first_name ) < 2 ) {
						$validation_errors['cbxpetition-fname'] = esc_html__( 'First name is required and needs at least 3 characters.', 'cbxpetition' );
					}

					if ( strlen( $last_name ) < 2 ) {
						$validation_errors['cbxpetition-lname'] = esc_html__( 'Last name is required and needs at least 3 characters.', 'cbxpetition' );
					}
				}


				if ( ! is_email( $email ) ) {
					$validation_errors['cbxpetition-email'] = esc_html__( 'Email is required and needs valid email address', 'cbxpetition' );
				} elseif ( ! is_user_logged_in() ) {
					if ( email_exists( $email ) ) {
						$validation_errors['cbxpetition-email'] = esc_html__( 'Email already exists to any registered user. Either login or try with different email address.', 'cbxpetition' );
					} elseif ( PetitionHelper::isPetitionSignedByGuest( $petition_id, $email ) ) {
						$validation_errors['cbxpetition-email'] = esc_html__( 'This petition has been signed using this email.', 'cbxpetition' );
					}
				}


				if ( $privacy == 0 ) {
					$validation_errors['cbxpetition-privacy'] = esc_html__( 'You must agree to privacy terms and conditions.', 'cbxpetition' );
				}
			}

			$validation_errors = apply_filters( 'cbxpetition_sign_submit_validation_errors',
				$validation_errors,
				$post_data,
				$petition_id );

			if ( sizeof( $validation_errors ) > 0 ) {
				$response_validation_errors['error'] = $validation_errors;
				wp_send_json( $response_validation_errors );
			}

			//data validated and now good to add/update

			$data_safe['petition_id'] = $petition_id;
			$data_safe['f_name']      = $first_name;
			$data_safe['l_name']      = $last_name;
			$data_safe['email']       = $email;
			$data_safe['comment']     = $comment;

			$default_state    = $settings->get_field( 'default_state', 'cbxpetition_general', 'approved' );
			$guest_activation = $settings->get_field( 'guest_activation', 'cbxpetition_general', '' );

			$activation_code = null;

			if ( $guest_activation == 'on' && $user_id == 0 ) {
				$default_state   = 'unverified';
				$activation_code = wp_generate_password( $length = 12,
					false,
					false );//used for email activation, if email activation enabled then we use it, after activation we delete this value like password activation

			}

			$data_safe['state']      = $default_state;
			$data_safe['activation'] = $activation_code;

			//insert
			$data_safe['add_by']   = $user_id;
			$data_safe['add_date'] = current_time( 'mysql' );

			$data_safe = apply_filters( 'cbxpetition_sign_submit_before_insert_data', $data_safe, $petition_id );

			$data_format = [
				'%d', //petition_id
				'%s', //f_name
				'%s', //l_name
				'%s', //email
				'%s', //comment
				'%s', //state
				'%s', //activation
				'%d', //add_by
				'%s'  //add_date
			];

			$data_format = apply_filters( 'cbxpetition_sign_submit_before_insert_col_data_format',
				$data_format,
				$data_safe,
				$petition_id );

			$success_arr  = [];
			$error_arr    = [];
			$response_arr = [];

			do_action( 'cbxpetition_sign_submit_before_insert', $petition_id, $data_safe, $data_format );

			//$show_form = 1;

			$log_data = null;

			if ( $wpdb->insert( $signature_table, $data_safe, $data_format ) !== false ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				//$show_form = 0;

				$log_id          = $wpdb->insert_id;
				$data_safe['id'] = $log_id;

				//$log_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $signature_table WHERE id = %d", $sign_id ), ARRAY_A );
				$log_data = ( $log_data === null ) ? PetitionHelper::petitionSignInfo( $log_id ) : $log_data;

				do_action( 'cbxpetition_sign_submit_after_insert', $petition_id, $log_id, $log_data );

				$single_message = [
					'text' => esc_html__( 'Your signature request stored successfully. Thank you!', 'cbxpetition' ),
					'type' => 'success',
				];

				$success_arr[]  = apply_filters( 'cbxpetition_sign_submit_insert_message', $single_message, $petition_id, $log_id, $log_data );


			} else {
				//failed to insert
				$single_message = [
					'text' => esc_html__( 'Sorry! Problem during signing request, please check again and try again.',
						'cbxpetition' ),
					'type' => 'danger',
				];
				$error_arr[]    = $single_message;
			}

			$success_arr = apply_filters( 'cbxpetition_sign_submit_success_messages', $success_arr, $petition_id );
			$error_arr   = apply_filters( 'cbxpetition_sign_submit_error_messages', $error_arr, $petition_id );

			$response_arr['success_arr']['messages'] = $success_arr;
			$response_arr['error_arr']['messages']   = $error_arr;


			wp_send_json( $response_arr );
		}
	}//end method petition_sign_submit

	/**
	 * Signature ajax listing load more
	 *
	 * @since 1.0.0
	 */
	public function petition_load_more_signs() {
		check_ajax_referer( 'cbxpetition_nonce', 'security' );

		$settings = $this->settings;
		$per_page = $settings->get_field( 'sign_limit', 'cbxpetition_general', 20 );

		$submit_data = wp_unslash( $_REQUEST ); //all fields are sanitized below

		$petition_id = isset( $submit_data['petition_id'] ) ? absint( $submit_data['petition_id'] ) : 0;
		$page        = isset( $submit_data['page'] ) ? absint( $submit_data['page'] ) : 1;
		$per_page    = isset( $submit_data['perpage'] ) ? absint( $submit_data['perpage'] ) : $per_page;
		$order       = isset( $submit_data['order'] ) ? sanitize_text_field( wp_unslash( $submit_data['order'] ) ) : 'DESC';
		$order_by    = isset( $submit_data['orderby'] ) ? sanitize_text_field( wp_unslash( $submit_data['orderby'] ) ) : 'id';

		$order = strtoupper( $order );

		if ( ! in_array( $order, [ 'DESC', 'ASC' ] ) ) {
			$order = 'DESC';
		}

		$output = '';

		if ( $petition_id > 0 && $page > 1 ) {
			$petition_signs = PetitionHelper::getSignListingData( '',
				$petition_id,
				0,
				'approved',
				$order,
				$order_by,
				$per_page,
				$page );
			if ( is_array( $petition_signs ) && sizeof( $petition_signs ) > 0 ) {
				foreach ( $petition_signs as $petition_sign ) {
					$output .= cbxpetition_get_template_html( 'petition/signature.php', [
							'petition_id'   => $petition_id,
							'petition_sign' => $petition_sign,
							'order'         => $order,
							'orderby'       => $order_by,
							'per_page'       => $per_page,
							'page'          => $page
						]
					);
				}
			}
		}

		$response = [
			'listing' => $output,
		];

		echo json_encode( $response );
		wp_die();
	}//end method petition_load_more_signs

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		PetitionHelper::cbxpetition_public_styles();
	}//end method enqueue_styles

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		PetitionHelper::cbxpetition_public_scripts();
	}//end method enqueue_scripts

	/**
	 * Include custom template file
	 *
	 * @param $template
	 *
	 * @return mixed|string
	 * @since 2.0.0
	 */
	public function include_custom_templates($template) {
		$settings             = $this->settings;
		$load_custom_template = esc_attr( $settings->get_field( 'custom_template', 'cbxpetition_basic', 'on' ) );


		if ( $load_custom_template == 'on' ) {
			if ( is_singular( 'cbxpetition' ) ) {
				$theme_template = locate_template('single-cbxpetition.php');
				if ($theme_template) {
					return $theme_template;
				}

				return CBXPETITION_ROOT_PATH . 'templates/single-cbxpetition.php';
			}
			else if(is_post_type_archive('cbxpetition')){
				$theme_template = locate_template('archive-cbxpetition.php');
				if ($theme_template) {
					return $theme_template;
				}

				return CBXPETITION_ROOT_PATH . 'templates/archive-cbxpetition.php';
			}
			else if(is_tax('cbxpetition_cat') || is_tax('cbxpetition_tag')){
				$theme_template = locate_template('taxonomy-cbxpetition.php');
				if ($theme_template) {
					return $theme_template;
				}

				return CBXPETITION_ROOT_PATH . 'templates/taxonomy-cbxpetition.php';
			}
		}

		return $template;
	}//end method include_custom_templates

	/**
	 * Display petition category after title in details page
	 *
	 * @return void
	 */
	public function category_display_after_title() {
		$settings = $this->settings;
		$cat_enable       = $settings->get_field( 'cat_enable', 'cbxpetition_basic', 'on' );
		if($cat_enable == 'on'){
			// Get the current post ID
			$post_id = get_the_ID();

			// Specify the custom taxonomy name
			$taxonomy = 'cbxpetition_cat';

			// Get the terms associated with the post
			$terms = get_the_terms($post_id, $taxonomy);

			if ($terms && !is_wp_error($terms)) {
				echo '<ul class="tags petition-taxonomy-links petition-category-links">';
				foreach ($terms as $term) {
					// Get the term link
					$term_link = get_term_link($term);
					if (!is_wp_error($term_link)) {
						// Display the term link
						echo '<li class="tag tag-petition"><a href="' . esc_url($term_link) . '">' . esc_html($term->name) . '</a></li>';
					}
				}
				echo '</ul>';
			}
		}
	}//end method category_display_after_title

	/**
	 * Display petition category after title in details page
	 *
	 * @return void
	 */
	public function tag_display_after_title() {
		$settings = $this->settings;
		$tag_enable       = $settings->get_field( 'tag_enable', 'cbxpetition_basic', 'on' );
		if($tag_enable == 'on'){
			// Get the current post ID
			$post_id = get_the_ID();

			// Specify the custom taxonomy name
			$taxonomy = 'cbxpetition_tag';

			// Get the terms associated with the post
			$terms = get_the_terms($post_id, $taxonomy);

			if ($terms && !is_wp_error($terms)) {
				echo '<ul class="tags petition-taxonomy-links petition-tag-links">';
				foreach ($terms as $term) {
					// Get the term link
					$term_link = get_term_link($term);
					if (!is_wp_error($term_link)) {
						// Display the term link
						echo '<li class="tag tag-petition"><a href="' . esc_url($term_link) . '">' . esc_html($term->name) . '</a></li>';
					}
				}
				echo '</ul>';
			}
		}
	}//end method tag_display_after_title
}//end class CBXPetitionPublic