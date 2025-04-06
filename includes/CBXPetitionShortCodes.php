<?php

namespace Cbx\Petition;

use Cbx\Petition\CBXSetting;
use Cbx\Petition\Helpers\PetitionHelper;

class CBXPetitionShortCodes {

	private $settings;

	public function __construct() {
		$this->settings = new CBXSetting();
	}

	/**
	 * Init all shortcodes
	 * @since 1.0.0
	 */
	public function init_shortcodes() {
		//add shortcode
		add_shortcode( 'cbxpetition', [ $this, 'cbxpetition_display' ] );

		add_shortcode( 'cbxpetition_summary', [ $this, 'cbxpetition_summary_display' ] );
		add_shortcode( 'cbxpetition_signform', [ $this, 'cbxpetition_signform_display' ] );

		add_shortcode( 'cbxpetition_video', [ $this, 'cbxpetition_video_display' ] );
		add_shortcode( 'cbxpetition_photos', [ $this, 'cbxpetition_photos_display' ] );
		add_shortcode( 'cbxpetition_letter', [ $this, 'cbxpetition_letter_display' ] );
		add_shortcode( 'cbxpetition_banner', [ $this, 'cbxpetition_banner_display' ] );
		add_shortcode( 'cbxpetition_signatures', [ $this, 'cbxpetition_signature_display' ] );
		add_shortcode( 'cbxpetition_stat', [ $this, 'cbxpetition_stat_display' ] );

		//extras
		add_shortcode( 'cbxpetition_latest', [ $this, 'cbxpetition_latest_display' ] );
	}//end method init_shortcodes

	/**
	 * Petition details shortcode callback function
	 *
	 * @param $atts
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function cbxpetition_display( $atts ) {
		global $post;

		$petition_id = 0;

		if ( isset( $post->ID ) ) {
			$petition_id = ( $post->post_type === 'cbxpetition' ) ? absint( $post->ID ) : 0;
		}

		$default_sections = PetitionHelper::petition_default_section_keys();

		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );
		$atts = shortcode_atts( [
			'petition_id' => $petition_id,
			'sections'    => implode( ',', $default_sections )
		], $atts, 'cbxpetition' );

		extract( $atts );

		$user_id     = absint( get_current_user_id() );
		$petition_id = absint( $petition_id );

		$output = '';

		if ( $petition_id == 0 ) {
			$output .= '<p class="cbxpetition-info cbxpetition-info-notfound">' . esc_html__( 'No valid petition id found.',
					'cbxpetition' ) . '</p>';
		} else {

			$sections = sanitize_text_field( $sections );
			$sections = explode( ',', $sections );

			if ( is_array( $sections ) && sizeof( $sections ) > 0 ) {
				foreach ( $sections as $section ) {
					$section = trim( strtolower( $section ) );
					$output  .= do_shortcode( '[' . $section . ' petition_id="' . $petition_id . '"]' );
				}
			}
		}

		return cbxpetition_get_template_html( 'petition/details-shortcode.php', [
			'output'      => $output,
			'petition_id' => $petition_id,
			'atts'        => $atts
		] );
	}//end method cbxpetition_display

	/**
	 * Petition Summary display
	 *
	 * @param $atts
	 *
	 * @return string
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function cbxpetition_summary_display( $atts ) {
		global $post;

		$petition_id = ( $post->post_type === 'cbxpetition' ) ? intval( $post->ID ) : 0;

		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		$atts = shortcode_atts( [
			'petition_id' => $petition_id,
			'sections'    => apply_filters( 'cbxpetition_summary_shortcode_default_sections', 'title,content,stat,expire_date' )
		], $atts, 'cbxpetition_summary' );

		extract( $atts );

		//$user_id = intval( get_current_user_id() );

		$petition_id = absint( $petition_id );
		$output      = '<div class="cbx-chota">';

		$output .= apply_filters( 'cbxpetition_summary_before', '', $petition_id, $atts );
		$output .= '<div class="cbxpetition_summary_wrap">';

		if ( $petition_id == 0 || get_post_type( $petition_id ) !== 'cbxpetition' ) {
			$output .= '<p class="cbxpetition-info cbxpetition-info-notfound cbxpetition-alert cbxpetition-alert-danger">' . esc_html__( 'Sorry, seems not a valid petition.',
					'cbxpetition' ) . '</p>';
		} else {
			$petition = get_post( $petition_id );

			if ( $petition !== null ) {
				$sections   = sanitize_text_field( wp_unslash( $sections ) );
				$sections_t = explode( ',', $sections );
				$sections   = [];

				foreach ( $sections_t as $section ) {
					$section    = trim( strtolower( $section ) );
					$sections[] = $section;
				}


				$output .= apply_filters( 'cbxpetition_summary_start', '', $petition_id, $atts );

				$post_title = get_the_title( $petition_id );
				$post_link  = get_permalink( $petition_id );

				if ( in_array( 'title', $sections ) ) {
					//title
					$output .= '<h2 class="cbxpetition_summary_title"><a href="' . esc_url( $post_link ) . '">' . esc_attr( $post_title ) . '</a></h2>';
				}

				if ( in_array( 'content', $sections ) ) {
					$post_content = $petition->post_content;

					$post_content = apply_filters( 'the_content', $post_content );
					$post_content = str_replace( ']]>', ']]&gt;', $post_content );

					//https://wordpress.stackexchange.com/questions/245046/format-content-value-from-db-outside-of-wordpress-filters/245057#245057
					$post_content = strip_shortcodes( $post_content );
					$post_content = wp_trim_words( $post_content );

					//description
					if ( $post_content != '' ) {
						$output .= '<div class="cbxpetition_summary_content">';
						$output .= wpautop( wptexturize( wp_kses_post( $post_content ) ) );
						$output .= '</div>';
					}
				}


				if ( in_array( 'stat', $sections ) ) {
					$target          = absint( PetitionHelper::petitionSignatureTarget( $petition_id ) );
					$signature_count = absint( PetitionHelper::petitionSignatureCount( $petition_id ) );
					$signature_ratio = floatval( PetitionHelper::petitionSignatureTargetRatio( $petition_id ) );


					//ob_start();
					$show_count    = 1;
					$show_progress = 1;


					$output .= cbxpetition_get_template_html( 'petition/stat.php', [
							'show_count'      => $show_count,
							'show_progress'   => $show_progress,
							'target'          => $target,
							'signature_count' => $signature_count,
							'signature_ratio' => $signature_ratio
						]
					);
				}


				if ( in_array( 'expire_date', $sections ) ) {
					$expire_date = get_post_meta( $petition_id, '_cbxpetition_expire_date', true );

					$expire_info = '';

					if ( $expire_date == '' ) {
						$expire_info = esc_html__( 'Sorry, Petition expire date is not set yet.', 'cbxpetition' );
					} elseif ( $expire_date != '' ) {
						$expire_date = new \DateTime( $expire_date );
						$now_date    = new \DateTime( 'now' );

						$date_format      = get_option( 'date_format' );
						$time_format      = get_option( 'time_format' );
						$date_time_format = $date_format;


						if ( $expire_date < $now_date ) {
							/* translators: %s: petition expire date  */
							$expire_info = sprintf( esc_html__( 'Sorry, petition already expired on %s', 'cbxpetition' ), $expire_date->format( 'Y-m-d H:i:s' ) );
						} else {
							$expire_info = $expire_date->format( apply_filters( '', $date_time_format, $date_format, $time_format ) );
						}
					}
					$output .= '<p class="cbxpetition_expire_wrapper">' . esc_html__( 'Expire Date', 'cbxpetition' ) . ' : ' . $expire_info . '</p>';
				}

				$output .= apply_filters( 'cbxpetition_summary_end', '', $petition_id, $atts );

			}

		}

		$output .= '</div>';
		$output .= apply_filters( 'cbxpetition_summary_after', '', $petition_id, $atts );
		$output .= '</div>';//.cbx-chota

		return $output;
	}//end method cbxpetition_summary_display

	/**
	 * Shortcode callback for petition sign form
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function cbxpetition_signform_display( $atts ) {
		global $post;

		$settings = $this->settings;
		//$terms_page     = absint($settings->get_field('terms_page', 'cbxpetition_general', 0));
		//$terms_page_url = ($terms_page > 0) ? get_permalink($terms_page) : '#';

		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		$petition_id = ( isset( $post->post_type ) && $post->post_type === 'cbxpetition' ) ? absint( $post->ID ) : 0;

		$atts = shortcode_atts( [
			'petition_id' => $petition_id,
			'title'       => esc_html__( 'Sign this Petition', 'cbxpetition' )
		], $atts, 'cbxpetition_signform' );

		$petition_id = isset( $atts['petition_id'] ) ? absint( $atts['petition_id'] ) : 0;
		$title       = isset( $atts['title'] ) ? sanitize_text_field( $atts['title'] ) : '';

		return cbxpetition_get_template_html( 'petition/sign-form.php', [
			'petition_id' => $petition_id,
			'title'       => $title,
			'settings'    => $settings,
			'atts'        => $atts
		] );
	}//end method cbxpetition_signform_display

	/**
	 * Shortcode callback for petition video display
	 *
	 * @param $atts
	 *
	 * @return false|string
	 * @since 1.0.0
	 */
	public function cbxpetition_video_display( $atts ) {
		global $post;
		$settings = $this->settings;

		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		$petition_id = ( $post->post_type === 'cbxpetition' ) ? intval( $post->ID ) : 0;

		$atts = shortcode_atts( [
			'petition_id' => $petition_id,
		],
			$atts,
			'cbxpetition_video' );


		extract( $atts );

		if ( $petition_id == 0 ) {
			return esc_html__( 'No valid petition id found.', 'cbxpetition' );
		}

		$videos = PetitionHelper::petitionVideoInfo( $petition_id );

		$output = cbxpetition_get_template_html( 'petition/video.php', [
			'settings'    => $settings,
			'petition_id' => $petition_id,
			'videos'      => $videos
		] );

		return $output;
	}//end method cbxpetition_video_display

	/**
	 * Shortcode call back for petition photos display
	 *
	 * @param $atts
	 *
	 * @return false|string
	 * @since 1.0.0
	 */
	public function cbxpetition_photos_display( $atts ) {
		global $post;

		$settings = $this->settings;

		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		$petition_id = ( $post->post_type === 'cbxpetition' ) ? intval( $post->ID ) : 0;

		$atts = shortcode_atts( [
			'petition_id' => $petition_id,
		],
			$atts,
			'cbxpetition_photos' );


		extract( $atts );

		if ( $petition_id == 0 ) {
			return esc_html__( 'No valid petition id found.', 'cbxpetition' );
		}

		$petition_photos = PetitionHelper::petitionPhotos( $petition_id );

		$output = cbxpetition_get_template_html( 'petition/photos.php', [
			'petition_id'     => $petition_id,
			'petition_photos' => $petition_photos,
			'settings'        => $settings
		] );

		return $output;
	}//end method cbxpetition_photos_display

	/**
	 * Shortcode call back for petition letter display
	 *
	 * @param $atts
	 *
	 * @return false|string
	 * @since 1.0.0
	 */
	public function cbxpetition_letter_display( $atts ) {
		global $post;

		$settings    = $this->settings;
		$petition_id = ( $post->post_type === 'cbxpetition' ) ? intval( $post->ID ) : 0;

		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );
		$atts = shortcode_atts( [
			'petition_id' => $petition_id,
		],
			$atts,
			'cbxpetition_letter' );


		extract( $atts );

		if ( $petition_id == 0 ) {
			return esc_html__( 'No valid petition id found.', 'cbxpetition' );
		}

		$petition_letter = PetitionHelper::petitionLetterInfo( $petition_id );

		$output = cbxpetition_get_template_html( 'petition/letter.php', [
			'petition_id'     => $petition_id,
			'petition_letter' => $petition_letter,
			'settings'        => $settings
		] );

		return $output;
	}//end method cbxpetition_letter_display

	/**
	 * Shortcode call back for petition banner display
	 *
	 * @param $atts
	 *
	 * @return false|string
	 * @since 1.0.0
	 */
	public function cbxpetition_banner_display( $atts ) {
		global $post;

		$settings = $this->settings;
		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		$petition_id = ( $post->post_type === 'cbxpetition' ) ? intval( $post->ID ) : 0;

		$atts = shortcode_atts( [
			'petition_id' => $petition_id,
		],
			$atts,
			'cbxpetition_banner' );


		extract( $atts );

		if ( $petition_id == 0 ) {
			return esc_html__( 'No valid petition id found.', 'cbxpetition' );
		}

		$cbxpetition_banner = cbxpetition_petitionBannerImage( $petition_id );


		$output = cbxpetition_get_template_html( 'petition/banner.php', [
			'petition_id'        => $petition_id,
			'cbxpetition_banner' => $cbxpetition_banner,
			'settings'           => $settings
		] );


		return $output;
	}//end method cbxpetition_banner_display

	/**
	 * Shortcode call back for petition signature display
	 *
	 * @param $atts
	 *
	 * @return false|string
	 * @since 1.0.0
	 */
	public function cbxpetition_signature_display( $atts ) {
		global $post;

		$settings = $this->settings;
		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );


		$per_page    = $settings->get_field( 'sign_limit', 'cbxpetition_general', 20 );
		$petition_id = ( $post->post_type === 'cbxpetition' ) ? absint( $post->ID ) : 0;

		$atts = shortcode_atts( [
			'petition_id' => $petition_id,
			'perpage'     => intval( $per_page ),
			'order'       => 'DESC',
			'orderby'     => 'id',
		],
			$atts,
			'cbxpetition_signatures' );


		extract( $atts );

		if ( $petition_id == 0 ) {
			return esc_html__( 'No valid petition id found.', 'cbxpetition' );
		}


		$page           = 1;
		$petition_signs = PetitionHelper::getSignListingData( '',
			$petition_id,
			0,
			'approved',
			'DESC',
			'id',
			$per_page,
			$page );

		$petition_count = PetitionHelper::getSignListingDataCount( '', $petition_id, 0, 'approved', $per_page, $page );

		return cbxpetition_get_template_html( 'petition/signatures.php', [
				'petition_id'    => $petition_id,
				'petition_signs' => $petition_signs,
				'settings'       => $settings,
				'petition_count' => $petition_count,
				'per_page'       => $per_page,
				'page'           => $page,
			]
		);
	}//end method cbxpetition_signature_display

	/**
	 * Shortcode call back for petition stat display
	 *
	 * @param $atts
	 *
	 * @return false|string
	 * @since 1.0.0
	 */
	public function cbxpetition_stat_display( $atts ) {
		global $post;
		if ( ! isset( $post->post_type ) ) {
			return '';
		}

		$settings = $this->settings;

		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );


		$petition_id = ( $post->post_type === 'cbxpetition' ) ? intval( $post->ID ) : 0;

		$atts = shortcode_atts( [
			'petition_id'   => $petition_id,
			'show_count'    => 1,
			'show_progress' => 1,
		],
			$atts,
			'cbxpetition_stat' );

		extract( $atts );

		if ( $petition_id == 0 ) {
			return esc_html__( 'No valid petition id found.', 'cbxpetition' );
		}

		$target          = intval( PetitionHelper::petitionSignatureTarget( $petition_id ) );
		$signature_count = intval( PetitionHelper::petitionSignatureCount( $petition_id ) );
		$signature_ratio = floatval( PetitionHelper::petitionSignatureTargetRatio( $petition_id ) );

		return cbxpetition_get_template_html( 'petition/stat.php', [
				'petition_id'     => $petition_id,
				'target'          => $target,
				'signature_count' => $signature_count,
				'signature_ratio' => $signature_ratio,
				'settings'        => $settings,
				'show_count'      => $show_count,
				'show_progress'   => $show_progress
			]
		);
	}//end method cbxpetition_stat_display

	/**
	 * Shortcode call back for petition latest display
	 *
	 * @param $atts
	 *
	 * @return void
	 */
	public function cbxpetition_latest_display( $atts ) {
		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		$atts = shortcode_atts( [
			'limit'      => 10,
			'order'      => 'DESC',
			'orderby'    => 'ID',
			'show_thumb' => 1,
			'show_title' => 1,
			'show_stat'  => 1
		], $atts, 'cbxpetition_latest' );

		extract( $atts );

		$limit      = $atts['limit'];
		$order_by   = $atts['orderby'];
		$order      = $atts['order'];
		$show_thumb = $atts['show_thumb'];
		$show_title = $atts['show_title'];
		$show_stat  = $atts['show_stat'];


		$args = [
			'post_type'      => 'cbxpetition',
			'orderby'        => $order_by,
			'order'          => $order,
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
		];

		$petition_posts = get_posts( $args );

		$latest_html = '<ul class="cbxpetition-latest-wrapper">';


		foreach ( $petition_posts as $post ) {
			$post_id = absint( $post->ID );

			$thumbnail_url_placeholder = CBXPETITION_ROOT_URL . 'assets/images/demo-150x150.png';
			$thumbnail_url             = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
			$petition_id               = ( $post->post_type === 'cbxpetition' ) ? absint( $post_id ) : 0;

			$latest_html .= '<li class="cbxpetition-latest">';
			if ( $show_thumb ) {
				if ( has_post_thumbnail( $post_id ) ) {
					// phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
					$latest_html .= '<div class="cbxpettition-latest-thumbnail"><a href="' . esc_url( get_the_permalink( $post_id ) ) . '"><img src="' . esc_url( $thumbnail_url ) . '" alt="petition_thumb" /></a></div>';
				} else {
					// phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
					$latest_html .= '<div class="cbxpettition-latest-thumbnail"><a href="' . esc_url( get_the_permalink( $post_id ) ) . '"><img src="' . esc_url( $thumbnail_url_placeholder ) . '" alt="petition_thumb" /></a></div>';
				}
			}

			if ( $show_title ) {
				$latest_html .= '<h2 class="cbxpettition-latest-title"><a href="' . esc_url( get_the_permalink( $post_id ) ) . '">' . esc_attr( get_the_title( $post_id ) ) . '</a></h2>';
			}

			if ( $show_stat ) {
				$latest_html .= '<div class="cbxpettition-latest-stat">';
				$latest_html .= do_shortcode( '[cbxpetition_stat petition_id="' . absint( $petition_id ) . '"]' );
				$latest_html .= '</div>';
			}

			$latest_html .= '</li>'; //.cbxpetition-latest
		}//end foreach

		$latest_html .= '</ul>';

		return $latest_html;
	}//end method cbxpetition_latest_display
}//end class ShortCode