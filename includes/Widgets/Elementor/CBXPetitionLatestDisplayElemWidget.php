<?php

namespace Cbx\Petition\Widgets\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * CBX Petition Slider Display Widget
 */
class CBXPetitionLatestDisplayElemWidget extends \Elementor\Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'cbxpetition_latest_display';
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
		return esc_html__( 'CBX Latest Petitions', 'cbxpetition' );
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
		return 'cbxpetition-latest-display-icon';
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
			'section_cbxpetition_latest_display',
			[
				'label' => esc_html__( 'CBX latest Petition Display Widget Setting', 'cbxpetition' ),
			]
		);

		$this->add_control(
			'cbxpetition_limit',
			[
				'label'       => esc_html__( 'Limit', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'Default Latest Petition Display limit', 'cbxpetition' ),
				'default'     => 10,
				'label_block' => true
			]
		);
		$this->add_control(
			'cbxpetition_order',
			[
				'label'       => esc_html__( 'Order', 'cbxpetition' ),
				'description' => esc_html__( 'Display Latest Petitions Order By ASC/DESC', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'DESC',
				'options'     => [
					'ASC'  => esc_html__( 'Ascending', 'cbxpetition' ),
					'DESC' => esc_html__( 'Descending', 'cbxpetition' ),
				],
				'label_block' => true
			]
		);
		$this->add_control(
			'cbxpetition_orderby',
			[
				'label'       => esc_html__( 'OrderBy', 'cbxpetition' ),
				'description' => esc_html__( 'Display Latest Petitions OrderBy ID/Page', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'ID',
				'options'     => [
					'ID'   => esc_html__( 'ID', 'cbxpetition' ),
					'page' => esc_html__( 'Page', 'cbxpetition' ),
				],
				'label_block' => true
			]
		);

		$this->add_control(
			'cbxpetition_show_thumb',
			[
				'label'        => __( 'Show Thumbnail', 'cbxpetition' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'description'  => esc_html__( 'Display Latest Petitions Thumbnail', 'cbxpetition' ),
				'label_on'     => __( 'Yes', 'cbxpetition' ),
				'label_off'    => __( 'No', 'cbxpetition' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
		$this->add_control(
			'cbxpetition_show_title',
			[
				'label'        => __( 'Show Title', 'cbxpetition' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'description'  => esc_html__( 'Display Latest Petitions Title', 'cbxpetition' ),
				'label_on'     => __( 'Yes', 'cbxpetition' ),
				'label_off'    => __( 'No', 'cbxpetition' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
		$this->add_control(
			'cbxpetition_show_stat',
			[
				'label'        => __( 'Show Stat', 'cbxpetition' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'description'  => esc_html__( 'Display Latest Petitions Stat', 'cbxpetition' ),
				'label_on'     => __( 'Yes', 'cbxpetition' ),
				'label_off'    => __( 'No', 'cbxpetition' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'cbxpetition_mode',
			[
				'label'       => esc_html__( 'Display Mode', 'cbxpetition' ),
				'description' => esc_html__( 'Display Latest Petitions By Slider, List & Grid', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'slider',
				'options'     => [
					'slider' => esc_html__( 'Slider', 'cbxpetition' ),
					'list'   => esc_html__( 'List', 'cbxpetition' ),
					'grid'   => esc_html__( 'Grid', 'cbxpetition' ),
				],
				'label_block' => true
			]
		);

		$this->add_control(
			'cbxpetition_show_arrow',
			[
				'label'        => __( 'Show Arrow', 'cbxpetition' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'description'  => esc_html__( 'Show Slider Arrow', 'cbxpetition' ),
				'label_on'     => __( 'Yes', 'cbxpetition' ),
				'label_off'    => __( 'No', 'cbxpetition' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'cbxpetition_mode' => 'slider',
				],
			]
		);
		$this->add_control(
			'cbxpetition_show_dots',
			[
				'label'        => __( 'Show Dots', 'cbxpetition' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'description'  => esc_html__( 'Show Slider Dots', 'cbxpetition' ),
				'label_on'     => __( 'Yes', 'cbxpetition' ),
				'label_off'    => __( 'No', 'cbxpetition' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'cbxpetition_mode' => 'slider',
				],
			]
		);
		$this->add_control(
			'cbxpetition_slides_per_row',
			[
				'label'       => esc_html__( 'Slides Per Row', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'Number of Slides in Latest Petitions Slider', 'cbxpetition' ),
				'default'     => 3,
				'label_block' => true,
				'condition'   => [
					'cbxpetition_mode' => 'slider',
				],
			]
		);
		$this->add_control(
			'cbxpetition_number_of_rows',
			[
				'label'       => esc_html__( 'Number of Row', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'Display Number of Row in Latest Petitions Sliders', 'cbxpetition' ),
				'default'     => 1,
				'label_block' => true,
				'condition'   => [
					'cbxpetition_mode' => 'slider',
				],

			]
		);
		$this->add_control(
			'cbxpetition_vertical',
			[
				'label'       => esc_html__( 'Display Horizontal/Vertical', 'cbxpetition' ),
				'description' => esc_html__( 'Display Latest Petitions Slider Horizontal/Vertical', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 0,
				'options'     => [
					1 => esc_html__( 'Vertical', 'cbxpetition' ),
					0 => esc_html__( 'Horizontal', 'cbxpetition' ),
				],
				'label_block' => true,
				'condition'   => [
					'cbxpetition_mode' => 'slider',
				],
			]
		);
		$this->add_control(
			'cbxpetition_number_of_items_in_desktop',
			[
				'label'       => esc_html__( 'Number of Items in Desktop', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'Display Latest Petitions in Desktop', 'cbxpetition' ),
				'default'     => 3,
				'label_block' => true,
				'condition'   => [
					'cbxpetition_mode' => 'slider',
				],
			]
		);
		$this->add_control(
			'cbxpetition_number_of_items_in_tablet',
			[
				'label'       => esc_html__( 'Number of Items in Tablet', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'Display Latest Petitions in Tablet', 'cbxpetition' ),
				'default'     => 2,
				'label_block' => true,
				'condition'   => [
					'cbxpetition_mode' => 'slider',
				],
			]
		);
		$this->add_control(
			'cbxpetition_number_of_items_in_mobile',
			[
				'label'       => esc_html__( 'Number of Items in Mobile', 'cbxpetition' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'Display Latest Petitions in Mobile', 'cbxpetition' ),
				'default'     => 1,
				'label_block' => true,
				'condition'   => [
					'cbxpetition_mode' => 'slider',
				],
			]
		);

		$this->end_controls_section();
	}//end method _register_controls

	/**
	 * @param int $value
	 *
	 * @return int
	 */
	private function yes_no_to_10( $value = 0 ) {
		if ( $value === 'yes' ) {
			return 1;
		}

		return 0;
	}

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

		$limit                      = isset( $settings['cbxpetition_limit'] ) ? intval( $settings['cbxpetition_limit'] ) : 10;
		$order                      = isset( $settings['cbxpetition_order'] ) ? sanitize_text_field( $settings['cbxpetition_order'] ) : 'DESC';
		$order_by                   = isset( $settings['cbxpetition_orderby'] ) ? sanitize_text_field( $settings['cbxpetition_orderby'] ) : 'ID';
		$thumb                      = isset( $settings['cbxpetition_show_thumb'] ) ? $this->yes_no_to_10( $settings['cbxpetition_show_thumb'] ) : 1;
		$title                      = isset( $settings['cbxpetition_show_title'] ) ? $this->yes_no_to_10( $settings['cbxpetition_show_title'] ) : 1;
		$stat                       = isset( $settings['cbxpetition_show_stat'] ) ? $this->yes_no_to_10( $settings['cbxpetition_show_stat'] ) : 1;
		$mode                       = isset( $settings['cbxpetition_mode'] ) ? sanitize_text_field( $settings['cbxpetition_mode'] ) : 'slider';
		$arrow                      = isset( $settings['cbxpetition_show_arrow'] ) ? $this->yes_no_to_10( $settings['cbxpetition_show_arrow'] ) : 1;
		$dots                       = isset( $settings['cbxpetition_show_dots'] ) ? $this->yes_no_to_10( $settings['cbxpetition_show_dots'] ) : 1;
		$slides_per_row             = isset( $settings['cbxpetition_slides_per_row'] ) ? intval( $settings['cbxpetition_slides_per_row'] ) : 3;
		$number_of_rows             = isset( $settings['cbxpetition_number_of_rows'] ) ? intval( $settings['cbxpetition_number_of_rows'] ) : 1;
		$vertical                   = isset( $settings['cbxpetition_vertical'] ) ? intval( $settings['cbxpetition_vertical'] ) : 0;
		$number_of_items_in_desktop = isset( $settings['cbxpetition_number_of_items_in_desktop'] ) ? intval( $settings['cbxpetition_number_of_items_in_desktop'] ) : 1;
		$number_of_items_in_tablet  = isset( $settings['cbxpetition_number_of_items_in_tablet'] ) ? intval( $settings['cbxpetition_number_of_items_in_tablet'] ) : 1;
		$number_of_items_in_mobile  = isset( $settings['cbxpetition_number_of_items_in_mobile'] ) ? intval( $settings['cbxpetition_number_of_items_in_mobile'] ) : 1;

		echo do_shortcode( '[cbxpetition_latest limit="' . $limit . '" order="' . $order . '" orderby="' . $order_by . '"
		 show_thumb="' . $thumb . '" show_title="' . $title . '" show_stat="' . $stat . '" mode="' . $mode . '" show_arrow="' . $arrow . '" 
		 show_dots="' . $dots . '" slides_per_row="' . $slides_per_row . '" number_of_rows="' . $number_of_rows . '" vertical="' . $vertical . '"
		  number_of_items_in_desktop="' . $number_of_items_in_desktop . '" number_of_items_in_tablet="' . $number_of_items_in_tablet . '" 
		  number_of_items_in_mobile="' . $number_of_items_in_mobile . '"]' );

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
}//end class CBXPetitionLatestDisplayElemWidget
