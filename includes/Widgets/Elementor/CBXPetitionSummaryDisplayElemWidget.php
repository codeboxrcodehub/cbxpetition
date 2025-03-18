<?php

namespace Cbx\Petition\Widgets\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * CBX Petition Summary Display Widget
 */
class CBXPetitionSummaryDisplayElemWidget extends \Elementor\Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'cbxpetition_summary_display';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return esc_html__( 'CBX Petition Summary', 'cbxpetition' );
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the widget categories.
	 *
	 * @return array Widget categories.
	 * @since  1.0.10
	 * @access public
	 *
	 */
	public function get_categories() {
		return [ 'cbxpetition' ];
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'cbxpetition-summary-display-icon';
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'section_cbxpetition_summary_display',
			[
				'label' => esc_html__( 'CBXPetition Summary Display Widget Setting', 'cbxpetition' ),
			]
		);

		$this->add_control(
			'cbxpetition_id',
			[
				'label'       => esc_html__( 'Petition ID', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 0,
				/* translators: %s: Petition Link  */
				'description' => esc_html__( 'Choose from already created Petition.', 'cbxpetition' ) . ' ' . sprintf( __( 'Click <a target="_blank" href="%s">here</a> to see all the petitions', 'cbxpetition' ), esc_url( admin_url( 'edit.php?post_type=cbxpetition' ) ) ), 
				'label_block' => true,
			]
		);

		$summary_sections     = apply_filters( 'cbxpetition_summary_shortcode_default_sections', 'title,content,stat,expire_date' );
		$summary_sections_arr = explode( ',', $summary_sections );

		$this->add_control(
			'cbxpetition_summary_section',
			[
				'label'       => esc_html__( 'Petition Summary Sections', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'description' => esc_html__( 'Choose petition summary sections. By default, all section are selected', 'cbxpetition' ),
				'multiple'    => true,
				'default'     => $summary_sections_arr,
				'options'     => [
					'title'       => esc_html__( 'Title', 'cbxpetition' ),
					'content'     => esc_html__( 'Content', 'cbxpetition' ),
					'stat'        => esc_html__( 'Stat', 'cbxpetition' ),
					'expire_date' => esc_html__( 'Expire Date', 'cbxpetition' ),
				],
				'label_block' => true
			]
		);


		$this->end_controls_section();
	}//end method _register_controls

	/**
	 * Render google maps widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings();

		$id      = intval( $settings['cbxpetition_id'] );
		$section = $settings['cbxpetition_summary_section'];

		if ( is_array( $section ) ) {
			$section = array_filter( $section );
			$section = implode( ',', $section );
		} else {
			$section = '';
		}


		if ( intval( $id ) <= 0 && ( false !== get_post_status( $id ) ) ) {
			esc_html_e( 'Please select a petition or petition doesn\'t exists', 'cbxpetition' );
		} else {

			echo do_shortcode( '[cbxpetition_summary sections="' . $section . '" petition_id="' . $id . '"]' );
		}

	}//end method render

	/**
	 * Render google maps widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function _content_template() {
	}//end method _content_template
}//end class CBXPetitionSummaryDisplayElemWidget
