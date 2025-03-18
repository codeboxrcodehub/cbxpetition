<?php

namespace Cbx\Petition\Widgets\Elementor;

use Cbx\Petition\Helpers\PetitionHelper;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Petition Display Widget
 */
class CBXPetitionDisplayElemWidget extends \Elementor\Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'cbxpetition_display';
	}//end method get_name

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return esc_html__( 'CBX Petition Full', 'cbxpetition' );
	}//end method get_title

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
	}//end method get_categories

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'cbxpetition-display-icon';
	}//end method get_icon

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
			'section_cbxpetition_display',
			[
				'label' => esc_html__( 'CBXPetition Display Widget Setting', 'cbxpetition' ),
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

		$default_sections     = apply_filters( 'cbxpetition_shortcode_default_sections', 'cbxpetition_banner,cbxpetition_stat,cbxpetition_video,cbxpetition_photos,cbxpetition_letter,cbxpetition_signform,cbxpetition_signatures' );
		$default_sections_arr = explode( ',', $default_sections );

		$this->add_control(
			'cbxpetition_section',
			[
				'label'       => esc_html__( 'Petition Sections', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'description' => esc_html__( 'Choose petition sections. By default, all section are selected', 'cbxpetition' ),
				'multiple'    => true,
				'default'     => $default_sections_arr,
				'options'     => PetitionHelper::petition_default_sections(),
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
		$section = $settings['cbxpetition_section'];

		if ( is_array( $section ) ) {
			$section = array_filter( $section );
			$section = implode( ',', $section );
		} else {
			$section = '';
		}


		if ( absint( $id ) <= 0 && ( false !== get_post_status( $id ) ) ) {
			echo '<p>'.esc_html__( 'Please select a petition or petition doesn\'t exists', 'cbxpetition' ).'</p>';
		} else {
			echo do_shortcode( '[cbxpetition sections="' . $section . '" petition_id="' . $id . '"]' );
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
}//end method CBXPetitionDisplayElemWidget
