<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use \Cbx\Petition\Helpers\PetitionHelper;
use enshrined\svgSanitize\Sanitizer;

/**
 * Is_woocommerce - Returns true if on a page which uses WooCommerce templates (cart and checkout are standard pages with shortcodes and thus are not included).
 *
 * @return bool
 */
function is_cbxpetition() {
	return apply_filters( 'is_cbxpetition', is_petition_dashboard() || is_petitions() || is_petition_taxonomy() || is_petition() );
}

if ( ! function_exists( 'is_petition_dashboard' ) ) {

	/**
	 * Is_shop - Returns true when viewing the product type archive (shop).
	 *
	 * @return bool
	 */
	function is_petition_dashboard() {
		$settings            = new CBXPetition_Settings();
		$user_dashboard_page = intval( $settings->get_field( 'user_dashboard_page', 'cbxpetition_front_settings', 0 ) );

		return ( is_page( $user_dashboard_page ) );
	}
}

if ( ! function_exists( 'is_petitions' ) ) {

	/**
	 * is_petitions - Returns true when viewing the petition type archive.
	 *
	 * @return bool
	 */
	function is_petitions() {
		return ( is_post_type_archive( 'cbxpetition' ) );
	}//end method is_petitions
}

if ( ! function_exists( 'is_petition_taxonomy' ) ) {

	/**
	 * is_petition_taxonomy - Returns true when viewing a petition taxonomy archive.
	 *
	 * @return bool
	 */
	function is_petition_taxonomy() {
		return is_tax( get_object_taxonomies( 'cbxpetition' ) );
	}//end method is_petition_taxonomy
}

if ( ! function_exists( 'is_petition_category' ) ) {

	/**
	 * is_petition_category - Returns true when viewing a petition category.
	 *
	 * @param string $term (default: '') The term slug your checking for. Leave blank to return true on any.
	 *
	 * @return bool
	 */
	function is_petition_category( $term = '' ) {
		return is_tax( 'cbxproduct_cat', $term );
	}//end method is_petition_category
}

if ( ! function_exists( 'is_petition_tag' ) ) {

	/**
	 * is_petition_tag - Returns true when viewing a petition tag.
	 *
	 * @param string $term (default: '') The term slug your checking for. Leave blank to return true on any.
	 *
	 * @return bool
	 */
	function is_petition_tag( $term = '' ) {
		return is_tax( 'cbxproduct_tag', $term );
	}//end method is_petition_tag
}

if ( ! function_exists( 'is_petition' ) ) {

	/**
	 * Is_product - Returns true when viewing a single product.
	 *
	 * @return bool
	 */
	function is_petition() {
		return is_singular( [ 'cbxpetition' ] );
	}//end method is_petition
}
if ( ! function_exists( 'cbxpetition_petitionSignInfo' ) ) {
	function cbxpetition_petitionSignInfo( $petition_id = 0 ) {
		return PetitionHelper::petitionSignInfo( $petition_id );
	}//end method cbxpetition_petitionSignInfo
}

if(!function_exists('cbxpetition_petitionExpireDate')){
	/**
	 * get single petition expire date
	 *
	 * @param int $petition_id
	 *
	 * @return string
	 */
	function cbxpetition_petitionExpireDate( $petition_id = 0 ) {
		return PetitionHelper::petitionExpireDate( $petition_id );
	}//end function cbxpetition_petitionExpireDate
}

if(!function_exists('cbxpetition_petitionMediaInfo')){
	/**
	 * get single petition media info data arr
	 *
	 * @param int $petition_id
	 *
	 * @return array|mixed
	 */
	function cbxpetition_petitionMediaInfo( $petition_id = 0 ) {
		return PetitionHelper::petitionMediaInfo( $petition_id );
	}//end function cbxpetition_petitionMediaInfo
}

if(!function_exists('cbxpetition_petitionBannerImage')){
	/**
	 * get single petition banner image
	 *
	 * @param int $petition_id
	 *
	 * @return int|mixed|string
	 */
	function cbxpetition_petitionBannerImage( $petition_id = 0 ) {
		return PetitionHelper::petitionBannerImage( $petition_id );
	}//end function cbxpetition_petitionBannerImage
}

if(!function_exists('cbxpetition_petitionSignatureTarget')){
	/**
	 * get single petition signature target
	 *
	 * @param int $petition_id
	 *
	 * @return int|mixed|string
	 */
	function cbxpetition_petitionSignatureTarget( $petition_id = 0 ) {
		return PetitionHelper::petitionSignatureTarget( $petition_id );
	}//end function cbxpetition_petitionSignatureTarget
}

if(!function_exists('cbxpetition_petitionVideoInfo')){
	/**
	 * get single petition video info
	 *
	 * @param int $petition_id
	 *
	 * @return array
	 */
	function cbxpetition_petitionVideoInfo( $petition_id = 0 ) {
		return PetitionHelper::petitionVideoInfo( $petition_id );
	}//end function cbxpetition_petitionVideoInfo
}

if(!function_exists('cbxpetition_petitionPhotos')){
	/**
	 * get single petition photos
	 *
	 * @param int $petition_id
	 *
	 * @return array
	 */
	function cbxpetition_petitionPhotos( $petition_id = 0 ) {
		return PetitionHelper::petitionPhotos( $petition_id );
	}//end function cbxpetition_petitionPhotos
}

if(!function_exists('cbxpetition_petitionLetterInfo')){
	/**
	 * get single petition letter info
	 *
	 * @param int $petition_id
	 *
	 * @return array|mixed
	 */
	function cbxpetition_petitionLetterInfo( $petition_id = 0 ) {
		return PetitionHelper::petitionLetterInfo( $petition_id );
	}//end function cbxpetition_petitionLetterInfo
}

if(!function_exists('cbxpetition_petitionLetter')){
	/**
	 * get single petition letter
	 *
	 * @param int $petition_id
	 *
	 * @return mixed|string
	 */
	function cbxpetition_petitionLetter( $petition_id = 0 ) {
		return PetitionHelper::petitionLetter( $petition_id );
	}//end function cbxpetition_petitionLetter
}

if(!function_exists('cbxpetition_petitionRecipients')){
	/**
	 * get single petition recipients
	 *
	 * @param int $petition_id
	 *
	 * @return array
	 */
	function cbxpetition_petitionRecipients( $petition_id = 0 ) {
		return PetitionHelper::petitionRecipients( $petition_id );
	}//end function cbxpetition_petitionRecipients
}
if(!function_exists('cbxpetition_petitionSignatureCount')){
	/**
	 * get single petition signature count
	 *
	 * @param int $petition_id
	 *
	 * @return int
	 */
	function cbxpetition_petitionSignatureCount( $petition_id = 0 ) {
		return PetitionHelper::petitionSignatureCount( $petition_id );
	}//end function cbxpetition_petitionSignatureCount
}

if(!function_exists('cbxpetition_petitionSignatureTargetRatio')){
	/**
	 * get single petition signature to target ratio
	 *
	 * @param int $petition_id
	 *
	 * @return int
	 */
	function cbxpetition_petitionSignatureTargetRatio( $petition_id = 0 ) {
		return PetitionHelper::petitionSignatureTargetRatio( $petition_id );
	}//end method cbxpetition_petitionSignatureTargetRatio
}



if ( ! function_exists( 'cbxpetition_get_order_keys' ) ) {
	/**
	 * Get order keys
	 *
	 * @return string[]
	 */
	function cbxpetition_get_order_keys() {
		return PetitionHelper::get_order_keys();
	}//end method cbxpetition_get_order_keys
}

if ( ! function_exists( 'cbxpetition_signature_get_sortable_keys' ) ) {
	function cbxpetition_signature_get_sortable_keys() {
		return PetitionHelper::signature_get_sortable_keys();
	}//end method cbxpetition_signature_get_sortable_keys
}

if ( ! function_exists( 'cbxpetition_getMonthlySignatureCounts' ) ) {
	function cbxpetition_getMonthlySignatureCounts( $year = null, $petition_id = 0 ) {
		return PetitionHelper::getMonthlySignatureCounts( $year, $petition_id );
	}//end method cbxpetition_getMonthlySignatureCounts
}

if ( ! function_exists( 'cbxpetition_getWeeklySignatureCounts' ) ) {
	function cbxpetition_getWeeklySignatureCounts( $petition_id = 0 ) {
		return PetitionHelper::getWeeklySignatureCounts( $petition_id );
	}//end method cbxpetition_getWeeklySignatureCounts
}

if ( ! function_exists( 'cbxpetition_esc_svg' ) ) {
	/**
	 * SVG sanitizer
	 *
	 * @param string $svg_content The content of the SVG file
	 *
	 * @return string|false The SVG content if found, or false on failure.
	 * @since 1.0.0
	 */
	function cbxpetition_esc_svg( $svg_content = '' ) {
		// Create a new sanitizer instance
		$sanitizer = new Sanitizer();

		return $sanitizer->sanitize( $svg_content );
	}// end method cbxpetition_esc_svg
}


if ( ! function_exists( 'cbxpetition_load_svg' ) ) {
	/**
	 * Load an SVG file from a directory.
	 *
	 * @param string $svg_name The name of the SVG file (without the .svg extension).
	 * @param string $directory The directory where the SVG files are stored.
	 *
	 * @return string|false The SVG content if found, or false on failure.
	 * @since 1.0.0
	 */
	function cbxpetition_load_svg( $svg_name = '', $folder = '' ) {
		//note: code partially generated using chatgpt
		if ( $svg_name == '' ) {
			return '';
		}

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$credentials = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, null );
		if ( ! WP_Filesystem( $credentials ) ) {
			return; // Error handling here
		}

		global $wp_filesystem;

		$directory = cbxpetition_icon_path();

		// Sanitize the file name to prevent directory traversal attacks.
		$svg_name = sanitize_file_name( $svg_name );
		if ( $folder != '' ) {
			$folder = trailingslashit( $folder );
		}

		// Construct the full file path.
		$file_path = $directory . $folder . $svg_name . '.svg';

		$file_path = apply_filters( 'cbxpetition_svg_file_path', $file_path, $svg_name );

		// Check if the file exists.
		if ( $wp_filesystem->exists( $file_path ) && is_readable( $file_path ) ) {
			// Get the SVG file content.
			return $wp_filesystem->get_contents( $file_path );
		} else {
			// Return false if the file does not exist or is not readable.
			return '';
		}
	}//end method cbxpetition_load_svg
}

if ( ! function_exists( 'cbxpetition_icon_path' ) ) {
	/**
	 * Form icon path
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	function cbxpetition_icon_path() {
		$directory = trailingslashit( CBXPETITION_ROOT_PATH ) . 'assets/icons/';

		return apply_filters( 'cbxpetition_icon_path', $directory );
	}//end method cbxpetition_icon_path
}

if ( ! function_exists( 'cbxpetition_deprecated_function' ) ) {
	/**
	 * Wrapper for deprecated functions so we can apply some extra logic.
	 *
	 * @param string $function
	 * @param string $version
	 * @param string $replacement
	 *
	 * @since  2.0.5
	 *
	 */
	function cbxpetition_deprecated_function( $function, $version, $replacement = null ) {
		if ( defined( 'DOING_AJAX' ) ) {
			do_action( 'deprecated_function_run', $function, $replacement, $version );
			$log_string = "The {$function} function is deprecated since version {$version}."; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$log_string .= $replacement ? " Replace with {$replacement}." : '';               // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				error_log( $log_string );//phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		} else {
			_deprecated_function( $function, $version, $replacement ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}//end function cbxpetition_deprecated_function
}

if ( ! function_exists( 'cbxpetition_is_rest_api_request' ) ) {
	/**
	 * Check if doing rest request
	 *
	 * @return bool
	 */
	function cbxpetition_is_rest_api_request() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix = trailingslashit( rest_get_url_prefix() );

		return ( false !== strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $rest_prefix ) );
	}//end function cbxpetition_is_rest_api_request
}

if ( ! function_exists( 'cbxpetition_doing_it_wrong' ) ) {
	/**
	 * Wrapper for _doing_it_wrong().
	 *
	 * @param string $function Function used.
	 * @param string $message Message to log.
	 * @param string $version Version the message was added in.
	 *
	 * @since  1.0.0
	 */
	function cbxpetition_doing_it_wrong( $function, $message, $version ) {
		// @codingStandardsIgnoreStart
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

		if ( wp_doing_ajax() || cbxpetition_is_rest_api_request() ) {
			do_action( 'doing_it_wrong_run', $function, $message, $version );
			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
		} else {
			_doing_it_wrong( $function, $message, $version );
		}
		// @codingStandardsIgnoreEnd
	}//end function cbxpetition_doing_it_wrong
}

if ( ! function_exists( 'cbxpetition_login_url_with_redirect' ) ) {
	function cbxpetition_login_url_with_redirect() {
		//$login_url          = wp_login_url();
		//$redirect_url       = '';

		if ( is_singular() ) {
			$login_url = wp_login_url( get_permalink() );
			//$redirect_url = get_permalink();
		} else {
			global $wp;
			$login_url = wp_login_url( home_url( add_query_arg( [], $wp->request ) ) );
			//$redirect_url = home_url( add_query_arg( [], $wp->request ) );
		}

		return $login_url;
	}//end function cbxpetition_login_url_with_redirect
}

if ( ! function_exists( 'cbxpetition_mailer' ) ) {
	/**
	 * Init the cbxpetition_mailer
	 *
	 */
	function cbxpetition_mailer() {
		if ( ! class_exists( 'CBXPetitionEmails' ) ) {
			include_once __DIR__ . '/../CBXPetitionEmails.php';
		}

		return CBXPetitionEmails::instance();
	}//end method cbxpetition_mailer
}

if ( ! function_exists( 'cbxpetition_wp_kses_link' ) ) {
	function cbxpetition_wp_kses_link() {
		return [ 'a' => [ 'href' => [], 'target' => [], 'class' => [], 'style' => [] ] ];
	}//end function cbxpetition_wp_kses_link
}

if ( ! function_exists( 'cbxpetition_check_and_deactivate_plugin' ) ) {
	/**
	 * Check any plugin and if version less than
	 *
	 * @param string $plugin_slug plugin slug
	 * @param string $required_version required plugin version
	 * @param string $transient transient name
	 *
	 * @return bool|void
	 * @since 2.0.0
	 */
	function cbxpetition_check_and_deactivate_plugin( $plugin_slug = '', $required_version = '', $transient = '' ) {
		if ( $plugin_slug == '' ) {
			return;
		}

		if ( $required_version == '' ) {
			return;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		// Check if the plugin is active
		if ( is_plugin_active( $plugin_slug ) ) {
			// Get the plugin data
			$plugin_data    = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_slug );
			$plugin_version = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '';
			if ( $plugin_version == '' || is_null( $plugin_version ) ) {
				return;
			}

			// Compare the plugin version with the required version
			if ( version_compare( $plugin_version, $required_version, '<' ) ) {
				// Deactivate the plugin
				deactivate_plugins( $plugin_slug );
				if ( $transient != '' ) {
					set_transient( $transient, 1 );
				}
			}
		}

		//return false;
	}//end method check_and_deactivate_plugin
}

if ( ! function_exists( 'cbxpetition_is_current_user_petition_owner' ) ) {
	/**
	 * Check if current user owner of petition by petition id
	 *
	 * @param $petition_id
	 *
	 * @return bool
	 */
	function cbxpetition_is_current_user_petition_owner( $petition_id ) {
		if ( ! is_user_logged_in() ) {
			return false; // No user is logged in
		}

		$current_user_id = get_current_user_id(); // Get logged-in user ID
		$post_author_id  = get_post_field( 'post_author', $petition_id ); // Get post author ID

		return ( $current_user_id == $post_author_id );
	}//end function cbxpetition_is_current_user_petition_owner
}

if(!function_exists('cbxpetition_signature_count')){
	function cbxpetition_signature_count( $petition_id = 0 ) {
		$count = 0;

		$petition_id = absint( $petition_id );
		if ( $petition_id == 0 ) {
			return $count;
		}

		return PetitionHelper::petitionSignatureCount($petition_id);
	}//end function cbxpetition_signature_count
}