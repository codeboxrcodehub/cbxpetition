<?php

namespace Cbx\Petition\Widgets\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * CBX Petition Signup form
 */
class CBXPetitionSignformDisplayElemWidget extends \Elementor\Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'cbxpetition_signform_display';
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
		return esc_html__( 'CBX Petition SignForm', 'cbxpetition' );
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
		return 'cbxpetition-signform-display-icon';
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
			'section_cbxpetition_signform_display',
			[
				'label' => esc_html__( 'CBXPetition Sign Form Display Widget Setting', 'cbxpetition' ),
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => esc_html__( 'Title', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Sign this Petition', 'cbxpetition' ),
				'label_block' => true,
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

		$this->add_control(
			'use_current',
			[
				'label'       => esc_html__( 'Display for Current Petition', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 0,
				'description' => esc_html__( 'In petition details page, ignore the petition id field and use the current/visiting petition.', 'cbxpetition' ),
				'options'     => [
					0 => esc_html__( 'No', 'cbxpetition' ),
					1 => esc_html__( 'Yes', 'cbxpetition' ),
				]
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

		$petition_id = isset( $settings['cbxpetition_id'] ) ? absint( $settings['cbxpetition_id'] ) : 0;
		$use_current = isset( $settings['use_current'] ) ? absint( $settings['use_current'] ) : 0;
		$title       = isset( $settings['title'] ) ? esc_html( $settings['title'] ) : '';



		if ( $use_current && is_singular( 'cbxpetition' ) ) {
			$petition_id = get_the_ID();
		}

		$attr = [];

		$attr['title'] = $title;
		//$attr['use_current']        = $use_current;
		$attr['petition_id'] = $petition_id;


		$attr = apply_filters( 'cbxpetition_elementor_shortcode_builder_attr', $attr, $settings, 'cbxpetition_signform' );

		$attr_html = '';

		foreach ( $attr as $key => $value ) {
			$attr_html .= ' ' . $key . '="' . $value . '" ';
		}

		echo '<div class="cbx-chota">';
		echo do_shortcode( '[cbxpetition_signform ' . $attr_html . ']' );
		echo '</div>';
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
}//end class CBXPetitionSignformDisplayElemWidget
