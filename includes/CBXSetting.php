<?php

namespace Cbx\Petition;

use Cbx\Petition\Helpers\PetitionHelper;

/**
 * weDevs Settings API wrapper class
 *
 * @version 1.1
 *
 * @author  Tareq Hasan <tareq@weDevs.com>
 * @link    http://tareq.weDevs.com Tareq's Planet
 * @example src/settings-api.php How to use the class
 * Further modified by codeboxr.com team
 */
class CBXSetting {

	/**
	 * settings sections array
	 *
	 * @var array
	 */
	private $settings_sections = [];

	/**
	 * Settings fields array
	 *
	 * @var array
	 */
	private $settings_fields = [];

	/**
	 * Singleton instance
	 *
	 * @var object
	 */
	private static $_instance;

	public function __construct() {

	}


	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}//end method instance

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __clone() {
		cbxpetition_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'cbxpetition' ), '2.0.0' );
	}//end method clone

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __wakeup() {
		cbxpetition_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'cbxpetition' ), '2.0.0' );
	}//end method wakeup

	/**
	 * Set settings sections
	 *
	 * @param array $sections setting sections array
	 */
	function set_sections( $sections ) {
		$this->settings_sections = $sections;

		return $this;
	}

	/**
	 * Add a single section
	 *
	 * @param array $section
	 */
	function add_section( $section ) {
		$this->settings_sections[] = $section;

		return $this;
	}

	/**
	 * Set settings fields
	 *
	 * @param array $fields settings fields array
	 */
	function set_fields( $fields ) {
		$this->settings_fields = $fields;

		return $this;
	}

	function add_field( $section, $field ) {
		$defaults = [
			'name'  => '',
			'label' => '',
			'desc'  => '',
			'type'  => 'text',
		];

		$arg                                 = wp_parse_args( $field, $defaults );
		$this->settings_fields[ $section ][] = $arg;

		return $this;
	}//end method add_field


	function admin_init() {
		//register settings sections
		foreach ( $this->settings_sections as $section ) {

			if ( false == get_option( $section['id'] ) ) {
				$section_default_value = $this->getDefaultValueBySection( $section['id'] );
				add_option( $section['id'], $section_default_value );
			} else {
				$section_default_value = $this->getMissingDefaultValueBySection( $section['id'] );
				update_option( $section['id'], $section_default_value );
			}

			if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
				$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
				//$callback        = create_function('', 'echo "' . str_replace('"', '\"', $section['desc']) . '";');
				$callback = function () use ( $section ) {
					echo str_replace( '"', '\"', $section['desc'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				};
			} elseif ( isset( $section['callback'] ) ) {
				$callback = $section['callback'];
			} else {
				$callback = null;
			}

			add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
		}

		//register settings fields
		foreach ( $this->settings_fields as $section => $field ) {
			foreach ( $field as $option ) {

				$name     = $option['name'];
				$type     = isset( $option['type'] ) ? $option['type'] : 'text';
				$label    = isset( $option['label'] ) ? $option['label'] : '';
				$callback = isset( $option['callback'] ) ? $option['callback'] : [
					$this,
					'callback_' . $type
				];

				$label_for = $this->settings_clean_label_for( "{$section}_{$option['name']}" );

				$args = [
					'id'                => $option['name'],
					'class'             => isset( $option['class'] ) ? $option['class'] : $name,
					'label_for'         => $label_for,
					'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
					'name'              => $label,
					'section'           => $section,
					'size'              => isset( $option['size'] ) ? $option['size'] : null,
					'min'               => isset( $option['min'] ) ? $option['min'] : '',
					'max'               => isset( $option['max'] ) ? $option['max'] : '',
					'step'              => isset( $option['step'] ) ? $option['step'] : '',
					'options'           => isset( $option['options'] ) ? $option['options'] : '',
					'default'           => isset( $option['default'] ) ? $option['default'] : '',
					'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
					'placeholder'       => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
					'type'              => $type,
					'optgroup'          => isset( $option['optgroup'] ) ? intval( $option['optgroup'] ) : 0,
					'multi'             => isset( $option['multi'] ) ? intval( $option['multi'] ) : 0,
					'fields'            => isset( $option['fields'] ) ? $option['fields'] : [],
					'sortable'          => isset( $option['sortable'] ) ? intval( $option['sortable'] ) : 0,
					'allow_new'         => isset( $option['allow_new'] ) ? intval( $option['allow_new'] ) : 0,
					//only works for repeatable
					'allow_clear'       => isset( $option['allow_clear'] ) ? intval( $option['allow_clear'] ) : 0,
					//only works for repeatable
					'check_content'     => isset( $option['check_content'] ) ? $option['check_content'] : '',
					'inline'            => isset( $option['inline'] ) ? absint( $option['inline'] ) : 1,
				];

				//add_settings_field($section . '[' . $option['name'] . ']', $option['label'], array($this, 'callback_' . $type), $section, $section, $args);
				add_settings_field( "{$section}[{$name}]", $label, $callback, $section, $section, $args );
			}
		}

		// creates our settings in the options table
		foreach ( $this->settings_sections as $section ) {
			// phpcs:ignore PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic
			register_setting( $section['id'], $section['id'], [ $this, 'sanitize_options' ] );
		}
	}//end method admin_init

	/**
	 * Prepares default values by section
	 *
	 * @param $section_id
	 *
	 * @return array
	 */
	function getDefaultValueBySection( $section_id ) {
		$default_values = [];

		$fields = $this->settings_fields[ $section_id ];
		foreach ( $fields as $field ) {
			$default_values[ $field['name'] ] = isset( $field['default'] ) ? $field['default'] : '';
		}

		return $default_values;
	}//end getDefaultValueBySection

	/**
	 * Prepares default values by section
	 *
	 * @param $section_id
	 *
	 * @return array
	 */
	function getMissingDefaultValueBySection( $section_id ) {
		$section_value = get_option( $section_id );
		$fields        = $this->settings_fields[ $section_id ];

		foreach ( $fields as $field ) {
			if ( ! isset( $section_value[ $field['name'] ] ) ) {
				$section_value[ $field['name'] ] = isset( $field['default'] ) ? $field['default'] : '';
			}

		}

		return $section_value;
	}//end getMissingDefaultValueBySection

	/**
	 * Get field description for display
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function get_field_description( $args ) {
		if ( ! empty( $args['desc'] ) ) {
			$desc = sprintf( '<p class="description">%s</p>', $args['desc'] );
		} else {
			$desc = '';
		}

		return $desc;
	}//end method get_field_description

	/**
	 * Displays heading field using h3
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function callback_heading( $args ) {
		$plus_svg  = cbxpetition_esc_svg( cbxpetition_load_svg( 'icon_plus' ) );
		$minus_svg = cbxpetition_esc_svg( cbxpetition_load_svg( 'icon_minus' ) );

		$html = '<h3 class="setting_heading"><span class="setting_heading_title">' . esc_html( $args['name'] ) . '</span><a title="' . esc_attr__( 'Click to show hide',
				'cbxpetition' ) . '" class="setting_heading_toggle button outline primary icon icon-only icon-inline" href="#"><i class="cbx-icon  setting_heading_toggle_plus">' . $plus_svg . '</i><i class="cbx-icon  setting_heading_toggle_minus">' . $minus_svg . '</i></a></h3>';
		$html .= $this->get_field_description( $args );

		echo $html;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_heading

	/**
	 * Displays sub heading field using h4
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	function callback_subheading( $args ) {
		$html = '<h4 class="setting_subheading">' . $args['name'] . '</h4>';
		$html .= $this->get_field_description( $args );

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_subheading


	/**
	 * Displays a text field for a settings field
	 *
	 * @param array $args
	 * @param $value
	 *
	 * @return void
	 */
	function callback_text( $args, $value = null ) {
		if ( $value === null ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['default'] ) );
		}
		$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$type = isset( $args['type'] ) ? $args['type'] : 'text';

		$html_id = "{$args['section']}_{$args['id']}";
		$html_id = $this->settings_clean_label_for( $html_id );

		$html = sprintf( '<input autocomplete="none" onfocus="this.removeAttribute(\'readonly\');" readonly type="%1$s" class="%2$s-text" id="%6$s" name="%3$s[%4$s]" value="%5$s"/>', $type, $size, $args['section'], $args['id'], $value, $html_id );
		$html .= $this->get_field_description( $args );

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end callback_text

	/**
	 * Displays an url field for a settings field
	 *
	 * @param array $args
	 * @param null $value
	 *
	 * @return void
	 */
	function callback_url( $args, $value = null ) {
		$this->callback_text( $args, $value );
	}//end method callback_url

	/**
	 * Displays a number field for a settings field
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	function callback_number( $args, $value = null ) {
		if ( $value === null ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['default'] ) );
		}

		$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$type        = isset( $args['type'] ) ? $args['type'] : 'number';
		$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
		$min         = empty( $args['min'] ) ? '' : ' min="' . $args['min'] . '"';
		$max         = empty( $args['max'] ) ? '' : ' max="' . $args['max'] . '"';
		$step        = empty( $args['max'] ) ? '' : ' step="' . $args['step'] . '"';

		$html_id = "{$args['section']}_{$args['id']}";
		$html_id = $this->settings_clean_label_for( $html_id );

		$html = sprintf( '<input type="%1$s" class="%2$s-number" id="%10$s" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder, $min, $max, $step, $html_id );
		$html .= $this->get_field_description( $args );
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_number

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array $args
	 * @param $value
	 *
	 * @return void
	 */
	function callback_radio( $args, $value = null ) {
		if ( $value === null ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['default'] );
		}

		$display_inline       = isset( $args['inline'] ) ? absint( $args['inline'] ) : 1;
		$display_inline_class = ( $display_inline ) ? 'radio_fields_inline' : '';

		$html = '<div class="radio_fields magic_radio_fields ' . esc_attr( $display_inline_class ) . '">';

		foreach ( $args['options'] as $key => $label ) {

			$html_id = "{$args['section']}_{$args['id']}_{$key}";
			$html_id = $this->settings_clean_label_for( $html_id );


			$html .= '<div class="magic-radio-field">';
			//$html .= sprintf( '<input type="radio" class="radio" id="wpuf-%5$s" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ), $html_id );
			$html .= sprintf( '<input type="radio" class="magic-radio" id="wpuf-%5$s" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ), $html_id );
			$html .= sprintf( '<label for="wpuf-%1$s">', $html_id );
			$html .= sprintf( '%1$s</label>', $label );
			$html .= '</div>';
		}

		$html .= '</div>';

		$html .= $this->get_field_description( $args );
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_radio

	/**
	 * Displays a checkbox for a settings field
	 *
	 * @param array $args
	 * @param $value
	 *
	 * @return void
	 */
	function callback_checkbox( $args, $value = null ) {
		if ( $value === null ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['default'] ) );
		}

		$html_id = "{$args['section']}_{$args['id']}";
		$html_id = $this->settings_clean_label_for( $html_id );

		$html = '<div class="checkbox_field magic_checkbox_field">';
		$html .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id'] );
		$html .= sprintf( '<input type="checkbox" class="magic-checkbox" id="wpuf-%4$s" name="%1$s[%2$s]" value="on" %3$s />', $args['section'], $args['id'], checked( $value, 'on', false ), $html_id );
		$html .= sprintf( '<label for="wpuf-%1$s">', $html_id );
		$html .= sprintf( '%1$s</label>', $args['desc'] );
		$html .= '</div>';

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_checkbox

	/**
	 * Displays a multicheckbox settings field
	 *
	 * @param array $args
	 * @param $value
	 *
	 * @return void
	 */
	function callback_multicheck( $args, $value = null ) {
		$sortable = isset( $args['sortable'] ) ? intval( $args['sortable'] ) : 0;

		if ( $value === null ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['default'] );
		}

		if ( ! is_array( $value ) ) {
			$value = [];
		}

		$options = $args['options'];//this can be regular array or associative array
		$value   = array_values( $value );

		$display_inline       = isset( $args['inline'] ) ? absint( $args['inline'] ) : 1;
		$display_inline_class = '';

		if ( $sortable ) {
			$display_inline = 0;
		}

		$display_inline_class .= ( $display_inline ) ? 'checkbox_fields_inline' : '';


		$sortable_class = ( $sortable ) ? 'checkbox_fields_sortable' : '';

		$html = '<p class="grouped gapless grouped_buttons checkbox_fields_check_actions"><a href="#" class="button primary checkbox_fields_check_action_call">' . esc_html__( 'Check All', 'cbxpetition' ) . '</a><a href="#" class="button outline checkbox_fields_check_action_ucall">' . esc_html__( 'Uncheck All', 'cbxpetition' ) . '</a></p>';
		$html .= '<div class="checkbox_fields magic_checkbox_fields ' . esc_attr( $sortable_class ) . ' ' . esc_attr( $display_inline_class ) . '">';

        if($sortable){
	        $options = $this->sort_options_by_saved_values($options, $value);
        }

		foreach ( $options as $key => $label ) {
			$checked = in_array( $key, $value ) ? ' checked="checked" ' : '';


			$html_id = "{$args['section']}_{$args['id']}_{$key}";
			$html_id = $this->settings_clean_label_for( $html_id );

			$html .= '<div class="checkbox_field magic_checkbox_field" data-key="' . esc_attr( $key ) . '">';
			if ( $sortable ) {
				$html .= '<span class="checkbox_field_handle" style="cursor: grab;"></span>';
			}

			$html .= sprintf( '<input type="hidden" name="%1$s[%2$s][%3$s]" value="" />', $args['section'], $args['id'], $key );
			$html .= sprintf( '<input type="checkbox" class="magic-checkbox" id="wpuf-%5$s" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, $checked, $html_id );
			$html .= sprintf( '<label for="wpuf-%1$s">', $html_id );
			$html .= sprintf( '%1$s</i></label>', $label );
			$html .= '</div>';
		}

		$html .= $this->get_field_description( $args );
		$html .= '</div>';

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_multicheck


	/**
	 * Displays a select box for a settings field
	 *
	 * @param $args
	 *
	 * @return void
	 */
	function callback_select( $args ) {
		$value = $this->get_option( $args['id'], $args['section'], $args['default'] );

		$multi      = isset( $args['multi'] ) ? intval( $args['multi'] ) : 0;
		$multi_name = ( $multi ) ? '[]' : '';
		$multi_attr = ( $multi ) ? 'multiple' : '';

		if ( $multi && ! is_array( $value ) ) {
			$value = [];
		}

		/*if ( ! is_array( $value ) ) {
			$value = [];
		}*/

		$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular selecttwo-select';

		if ( $args['placeholder'] == '' ) {
			$args['placeholder'] = esc_html__( 'Please Select', 'cbxpetition' );
		}

		$html = sprintf( '<input type="hidden" name="%1$s[%2$s][]" value="" />', $args['section'], $args['id'] );
		$html .= sprintf( '<div class="selecttwo-select-wrapper"><select ' . $multi_attr . ' class="%1$s" name="%2$s[%3$s]' . $multi_name . '" id="%2$s[%3$s]" style="min-width: 150px !important;"  placeholder="%4$s" data-placeholder="%4$s">', $size, $args['section'], $args['id'], $args['placeholder'] );

		if ( isset( $args['optgroup'] ) && $args['optgroup'] ) {
			foreach ( $args['options'] as $opt_grouplabel => $option_vals ) {
				$html .= '<optgroup label="' . $opt_grouplabel . '">';

				if ( ! is_array( $option_vals ) ) {
					$option_vals = [];
				}

				foreach ( $option_vals as $key => $val ) {
					$selected = in_array( $key, $value ) ? ' selected="selected" ' : '';
					$html     .= sprintf( '<option value="%s" ' . $selected . '>%s</option>', $key, $val );
				}
				$html .= '</optgroup>';
			}
		} else {
			$option_vals = $args['options'];

			foreach ( $option_vals as $key => $val ) {
				if ( $multi ) {
					$selected = in_array( $key, $value ) ? ' selected="selected" ' : '';
					$html     .= sprintf( '<option value="%s" ' . $selected . '>%s</option>', $key, $val );
				} else {
					$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $val );
				}
			}
		}

		$html .= '</select></div>';
		$html .= $this->get_field_description( $args );

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_select

	/**
	 * Displays a select box for a settings field
	 *
	 * @param $args
	 *
	 * @return void
	 */
	function callback_page( $args ) {
		$edit_svg     = cbxpetition_esc_svg( cbxpetition_load_svg( 'icon_edit' ) );
		$external_svg = cbxpetition_esc_svg( cbxpetition_load_svg( 'icon_external' ) );

		$value         = absint( $this->get_option( $args['id'], $args['section'], intval( $args['default'] ) ) );
		$size          = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular selecttwo-select';
		$check_content = isset( $args['check_content'] ) && ! is_null( $args['check_content'] ) ? $args['check_content'] : '';

		$allow_clear = isset( $args['allow_clear'] ) ? intval( $args['allow_clear'] ) : 0;

		$page_content          = '';
		$page_shortcode_note   = '';
		$shortcode_found_class = '';


		$html = '<div class="setting_pages_actions_wrapper">';

		$page_html = '<div class="setting_pages_actions">';
		if ( $value > 0 ) {
			if ( $check_content != '' ) {
				$page         = get_post( $value );
				$page_content = $page->post_content;
				if ( has_shortcode( $page_content, $check_content ) ) {
					$shortcode_found_class = 'description_note_on';
					/* translators: %s: content  */
					$page_shortcode_note = sprintf( esc_html__( 'This page has shortcode %s', 'cbxpetition' ), $check_content );
				} else {
					$shortcode_found_class = 'description_note_off';
					/* translators: %s: content  */
					$page_shortcode_note = sprintf( esc_html__( 'This page doesn\'t have shortcode %s', 'cbxpetition' ), $check_content );
				}
			}

			//edit
			if ( current_user_can( 'edit_post', $value ) ) {
				$page_html .= '<a class="setting_pages_action setting_pages_action_edit button primary icon icon-only small" target="_blank" title="' . esc_attr__( 'Edit', 'cbxpetition' ) . '" href="' . get_edit_post_link( $value ) . '"><i class="cbx-icon">' . $edit_svg . '</i></a>';
			}

			//view
			$page_html .= '<a class="setting_pages_action setting_pages_action_view button outline primary icon icon-only small" title="' . esc_attr__( 'View', 'cbxpetition' ) . '" target="_blank" href="' . get_the_permalink( $value ) . '"><i class="cbx-icon">' . $external_svg . '</i></a>';
		}

		$page_html .= '</div>';


		if ( $args['placeholder'] == '' ) {
			$placeholder = $args['placeholder'] = esc_html__( 'Please Select', 'cbxpetition' );
		} else {
			$placeholder = esc_attr( $args['placeholder'] );
		}


		$html .= sprintf( '<input type="hidden" name="%1$s[%2$s][]" value="" />', $args['section'], $args['id'] );
		$html .= sprintf( '<div class="selecttwo-select-wrapper" data-placeholder="' . $placeholder . '" data-allow-clear="' . $allow_clear . '"><select  class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]" style="min-width: 150px !important;" >', $size, $args['section'], $args['id'] );


        $option_vals = $args['options'];
        foreach ( $option_vals as $key => $val ) {
            $html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $val );

        }


		$html .= '</select></div>' . $page_html;
		$html .= '</div>'; //.setting_pages_actions_wrapper

		if ( $page_shortcode_note != '' ) {
			$html .= '<p class="description_note ' . esc_attr( $shortcode_found_class ) . '">' . $page_shortcode_note . '</p>';
		}

		$html .= $this->get_field_description( $args );

		echo $html;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_page


	/**
	 * Displays a textarea for a settings field
	 *
	 * @param array $args
	 * @param $value
	 *
	 * @return void
	 */
	function callback_textarea( $args, $value = null ) {
		if ( $value === null ) {
			$value = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['default'] ) );
		}
		$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		$html_id = "{$args['section']}_{$args['id']}";
		$html_id = $this->settings_clean_label_for( $html_id );

		$html = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%5$s" name="%2$s[%3$s]">%4$s</textarea>', $size, $args['section'], $args['id'], $value, $html_id );
		$html .= $this->get_field_description( $args );

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_textarea

	/**
	 * Displays a rich text textarea for a settings field
	 *
	 * @param array $args
	 * @param $value
	 *
	 * @return void
	 */
	function callback_wysiwyg( $args, $value = null ) {
		if ( $value === null ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['default'] );
		}
		$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';

		echo '<div style="max-width: ' . esc_attr( $size ) . ';">';

		$html_id = "{$args['section']}_{$args['id']}";
		$html_id = $this->settings_clean_label_for( $html_id );

		$editor_settings = [
			'teeny'         => true,
			'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
			'textarea_rows' => 10
		];
		if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
			$editor_settings = array_merge( $editor_settings, $args['options'] );
		}

		//wp_editor( $value, $args['section'] . '-' . $args['id'], $editor_settings );
		wp_editor( $value, $html_id, $editor_settings );

		echo '</div>';

		echo $this->get_field_description( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_wysiwyg

	/**
	 * Displays a file upload field for a settings field
	 *
	 * @param array $args settings field args
	 */
	function callback_file_old( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['default'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		//$id    = $args['section'] . '[' . $args['id'] . ']';
		$html_id = "{$args['section']}_{$args['id']}";
		$html_id = $this->settings_clean_label_for( $html_id );


		$label = isset( $args['options']['button_label'] ) ?
			$args['options']['button_label'] :
			esc_html__( 'Choose File', 'cbxpetition' );

		$html = '<div class="wpsa-browse-wrap">';
		$html .= sprintf( '<input type="text" class="chota-inline %1$s-text wpsa-url" id="%5$s" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value, $html_id );
		$html .= '<input type="button" class="button outline primary wpsa-browse" value="' . $label . '" />';
		$html .= '</div>';
		$html .= $this->get_field_description( $args );

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_file_old

    /**
     * Displays a file upload field for a settings field
     *
     * @param array $args settings field args
     */
    function callback_file( $args ) {
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['default'] ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $id    = $args['section'] . '[' . $args['id'] . ']';
        $label = isset( $args['options']['button_label'] ) ? $args['options']['button_label'] : esc_html__( 'Choose File', 'cbxpetition' );

        $html = '<div class="cbxchota-setting_input_file_wrap">';
        $html .= sprintf( '<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );

        $icon_extra_class  = '';
        $marker_icon       = '';
        $trash_extra_class = '';
        if ( $value === '' ) {
            $icon_extra_class  = 'cbxchota-setting_marker_hide';
            $file_picked_class = 'cbxchota-setting_left_space';
            $trash_extra_class = 'cbxchota-setting_trash_hide';
        } else {
            $marker_icon       = ' background-image: url(\'' . esc_url($value) . '\') ;';
            $file_picked_class = 'cbxchota-setting_filepicked';
        }
        $html .= '<span style="' . esc_attr($marker_icon) . '" class="cbxchota-setting_marker_preview ' . esc_attr( $icon_extra_class ) . '"></span>';

        $html .= '<input type="button" class="button cbxchota-setting_filepicker_btn wpsa-browse ' . esc_attr($file_picked_class) . '" value="' . esc_attr($label) . '" />';
        $html .= '<span class="cbxchota-setting_trash dashicons dashicons-no-alt ' . esc_attr( $trash_extra_class ) . '"></span>';
        $html .= '</div>';
        $html .= $this->get_field_description( $args );

        echo $html;// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }//end method callback_file

	/**
	 * Displays a color picker field for a settings field
	 *
	 * @param array $args
	 * @param $value
	 *
	 * @return void
	 */
	function callback_color( $args, $value = null ) {

		if ( $value === null ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['default'] ) );
		}

		$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		$html_id = "{$args['section']}_{$args['id']}";
		$html_id = $this->settings_clean_label_for( $html_id );

		$choose_color = esc_html__( 'Choose Color', 'cbxpetition' );

		$html = '<div class="setting-color-picker-wrapper">';
		$html .= sprintf( '<input type="hidden" class="%1$s-text setting-color-picker" id="%6$s" name="%2$s[%3$s]" value="%4$s" /><span data-current-color="%4$s"  class="button setting-color-picker-fire">%7$s</span>', $size, $args['section'], $args['id'], $value, $args['default'], $html_id, $choose_color );
		$html .= '</div>';

		$html .= $this->get_field_description( $args );

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end callback_color

	/**
	 * Displays a password field for a settings field
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	function callback_password( $args ) {
		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['default'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		$html = sprintf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
		$html .= $this->get_field_description( $args );

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_password


	/**
	 * Displays a email field for a settings field
	 *
	 * @param array $args settings field args
	 */
	function callback_email( $args ) {
		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['default'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$type  = isset( $args['type'] ) ? $args['type'] : 'text';

		$html_id = "{$args['section']}_{$args['id']}";
		$html_id = $this->settings_clean_label_for( $html_id );

		$html = sprintf( '<input  autocomplete="none" onfocus="this.removeAttribute(\'readonly\');" readonly type="%1$s" class="%2$s-text" id="%6$s" name="%3$s[%4$s]" value="%5$s"/>', $type, $size, $args['section'], $args['id'], $value, $html_id );
		$html .= $this->get_field_description( $args );

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_email

	/**
	 * Displays custom file extension checker checkbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	function callback_file_extensions_checker( $args ) {
		$stored_ext_arr = $this->get_option( $args['id'], $args['section'], $args['default'] );
		$image_ext_arr  = PetitionHelper::getImageExts();

		$html = sprintf( '<input type="hidden" name="%1$s[%2$s][]" value="0" />', $args['section'], $args['id'] );

		$html .= '<div class="checkbox_fields magic_checkbox_fields checkbox_fields_inline">';

		foreach ( $image_ext_arr as $index => $extension ) {
			// checkbox html
			$html .= '<div class="checkbox_field magic_checkbox_field">';
			$html .= sprintf( '  <input type="checkbox" class="magic-checkbox" 
  								id="%1$s[%2$s][%3$s]" name="%1$s[%2$s][]" value="%4$s" %5$s /><label for="%1$s[%2$s][%3$s]" class="checkbox-inline-extend">%4$s</label>',
				$args['section'],
				$args['id'],
				$index,
				ucfirst( $extension ),
				checked( ( is_array( $stored_ext_arr ) && in_array( $extension, $stored_ext_arr ) ), '1', false )
			);

			$html .= '</div>';
		}

		$html .= '</div>';

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method callback_file_extensions_checker

	/**
	 * Displays a text field for a settings field
	 *
	 * @param array $args settings field args
	 */
	function callback_slug( $args ) {
		$value = esc_attr( $this->get_field( $args['id'], $args['section'], $args['default'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		//$id    = $args['section'] . '[' . $args['id'] . ']';
		$html_id = "{$args['section']}_{$args['id']}";
		$html_id = $this->settings_clean_label_for( $html_id );


		$label = isset( $args['options']['button_label'] ) ?
			$args['options']['button_label'] :
			esc_html__( 'Clear Permalinks', 'cbxpetition' );

		$html = '<div class="permalink-wrap">';
		$html .= sprintf( '<input type="text" class="chota-inline %1$s-text permalink-slug" id="%5$s" name="%2$s[%3$s]" value="%4$s"/>',
			$size, $args['section'], $args['id'], $value, $html_id );
		$html .= '<a href="#" class="button outline primary clear-permalink ld-ext-right">' . esc_html( $label ) . '<span class="ld ld-spin ld-ring"></span></a>';
		$html .= '</div>';
		$html .= $this->get_field_description( $args );

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} //end method callback_slug


	/**
	 * Convert an array to associative if not
	 *
	 * @param $value
	 */
	/*private function convert_associate($value){
		if(!$this->is_associate($value) && sizeof($value) > 0){
			$new_value = array();
			foreach ($value as $val){
				$new_value[$val] = ucfirst($val);
			}
			return $new_value;
		}


		return $value;
	}*/

	/**
	 * Clean label_for or id tad
	 *
	 * @param $str
	 *
	 * @return string
	 */
	public function settings_clean_label_for( $str ) {
		$str = str_replace( '][', '_', $str );
		$str = str_replace( ']', '_', $str );

		return str_replace( '[', '_', $str );

		//return $str;
	}//end settings_clean_label_for


	/**
	 * check if any array is associative
	 *
	 * @param array $array
	 *
	 * @return bool
	 */
	private function is_associate( array $array ) {
		return count( array_filter( array_keys( $array ), 'is_string' ) ) > 0;
	}

	/**
	 * Sanitize callback for Settings API
	 */
	function sanitize_options( $options ) {
		foreach ( $options as $option_slug => $option_value ) {
			$sanitize_callback = $this->get_sanitize_callback( $option_slug );

			// If callback is set, call it
			if ( $sanitize_callback ) {
				$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
				continue;
			}
		}

		return $options;
	}

	/**
	 * Get sanitization callback for given option slug
	 *
	 * @param string $slug option slug
	 *
	 * @return mixed string or bool false
	 */
	function get_sanitize_callback( $slug = '' ) {
		if ( empty( $slug ) ) {
			return false;
		}

		// Iterate over registered fields and see if we can find proper callback
		foreach ( $this->settings_fields as $section => $options ) {
			foreach ( $options as $option ) {
				if ( $option['name'] != $slug ) {
					continue;
				}

				if ( ( $option['type'] == 'select' && isset( $option['multi'] ) && $option['multi'] ) || $option['type'] == 'multicheck' ) {
					$option['sanitize_callback'] = [ $this, 'sanitize_multi_select_check' ];
				}

				// Return the callback name
				return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
			}
		}

		return false;
	}//end get_sanitize_callback

	/**
	 * Remove empty values from multi select fields (multi select and multi checkbox)
	 *
	 * @param $option_value
	 *
	 * @return array
	 */
	public function sanitize_multi_select_check( $option_value ) {
		if ( is_array( $option_value ) ) {
			return array_filter( $option_value );
		}

		return $option_value;
	}

	/**
     * Sort options based on saved values
     *
	 * @param $options
	 * @param $saved_values
	 *
	 * @return array|mixed
	 */
	public function sort_options_by_saved_values($options, $saved_values) {
		// Ensure saved values is an array
		if (!is_array($saved_values)) {
			return $options;
		}

		// Filter out any saved keys that are not in options
		$saved_values = array_filter($saved_values, function ($key) use ($options) {
			return isset($options[$key]);
		});

		// Sort options based on saved values order, keeping others at the end
		$sorted_options = [];
		foreach ($saved_values as $key) {
			if (isset($options[$key])) {
				$sorted_options[$key] = $options[$key];
			}
		}

		// Append any missing options at the end
		foreach ($options as $key => $value) {
			if (!isset($sorted_options[$key])) {
				$sorted_options[$key] = $value;
			}
		}

		return $sorted_options;
	}//end method sort_options_by_saved_values


	/**
	 * Get the value of a settings field
	 *
	 * @param string $option settings field name
	 * @param string $section the section name this field belongs to
	 * @param string $default default text if it's not found
	 *
	 * @return string
	 */
	function get_option( $option, $section, $default = '' ) {

		$options = get_option( $section );

		if ( isset( $options[ $option ] ) && $options[ $option ] ) {
			return $options[ $option ];
		}

		return $default;
	}//end method get_option

	/**
	 * Get the value of a settings field
	 *
	 * @param string $option settings field name
	 * @param string $section the section name this field belongs to
	 * @param string $default default text if it's not found
	 *
	 * @return string
	 */
	function get_field( $option, $section, $default = '' ) {
		$options = get_option( $section );

		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return $default;
	}//end method get_option

	/**
	 * Show navigations as tab
	 *
	 * Shows all the settings section labels as tab
	 */
	function show_navigation() {
		$html = '<nav class="tabs setting-tabs setting-tabs-nav mb-0">';

		$i = 0;

		$mobile_navs = '<div  class="selecttwo-select-wrapper setting-select-wrapper"><select data-minimum-results-for-search="Infinity" class="setting-select setting-select-nav selecttwo-select">';

		foreach ( $this->settings_sections as $tab ) {
			$active_class  = ( $i === 0 ) ? 'active' : '';
			$active_select = ( $i === 0 ) ? ' selected ' : '';


			$html        .= sprintf( '<a data-tabid="' . $tab['id'] . '" href="#%1$s" class="%3$s" id="%1$s-tab">%2$s</a>',
				$tab['id'], $tab['title'], $active_class );
			$mobile_navs .= '<option ' . esc_attr( $active_select ) . ' value="' . $tab['id'] . '">' . esc_attr( $tab['title'] ) . '</option>';
			$i ++;
		}


		$mobile_navs .= '</select></div>';
		$html        .= '</nav>';

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $mobile_navs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method show_navigation

	/**
	 * Show the section settings forms
	 *
	 * This function displays every sections in a different form
	 */
	function show_forms() {
		?>
        <div id="setting-tabs-contents">
            <div id="global_setting_group_actions" class="mb-0">
				<?php do_action( 'cbxpetition_setting_group_actions_start' ); ?>
                <a class="button outline primary global_setting_group_action global_setting_group_action_open pull-right"
                   href="#"><?php esc_html_e( 'Toggle All Sections', 'cbxpetition' ); ?></a>
				<?php do_action( 'cbxpetition_setting_group_actions_end' ); ?>
                <div class="clear clearfix"></div>
            </div>
            <div class="metabox-holder">
				<?php
				$i = 0;
				foreach ( $this->settings_sections as $form ):
					$display_style = ( $i === 0 ) ? '' : 'display: none;';
					?>
                    <div id="<?php echo esc_attr( $form['id'] ); ?>" class="global_setting_group"
                         style="<?php echo $display_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					     ?>">
                        <form method="post" action="options.php" class="cbxpetition_setting_form">
							<?php
							do_action( 'cbxpetition_setting_form_start', $form );
							do_action( 'cbxpetition_setting_form_top_' . $form['id'], $form );

							settings_fields( $form['id'] );
							do_settings_sections( $form['id'] );

							do_action( 'cbxpetition_setting_form_bottom_' . $form['id'], $form );
							do_action( 'cbxchangelog_setting_form_end', $form );
							?>
                            <div class="global_setting_submit_buttons_wrap">
								<?php do_action( 'cbxpetition_setting_submit_buttons_start', $form['id'] ); ?>
								<?php submit_button( esc_html__( 'Save Settings', 'cbxpetition' ),
									'button primary submit_setting', 'submit', true,
									[ 'id' => 'submit_' . esc_attr( $form['id'] ) ] ); ?>
								<?php do_action( 'cbxpetition_setting_submit_buttons_end', $form['id'] ); ?>
                            </div>
                        </form>
                    </div>
					<?php
					$i ++;
				endforeach;
				?>
            </div>
        </div>
		<?php
	}//end show_forms

	/**
	 * Displays a textarea for a settings field
	 *
	 * @param array $args
	 * @param $value
	 *
	 * @return void
	 */
	function callback_html( $args, $value = null ) {
		echo $this->get_field_description( $args );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} //end method callback_html
}//end class CBXSetting