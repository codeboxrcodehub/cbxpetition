<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class CBXPetition Summary Widget
 *
 * @since 1.0.2
 */
class CBXPetitionLatestWidget extends WP_Widget {


	/**
	 * Unique identifier for your widget.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * widget file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $widget_slug = 'cbxpetition-widget'; //main parent plugin's language file

	/**
	 * CBXPetitionSignformWidget constructor.
	 */
	public function __construct() {
		parent::__construct(
			$this->get_widget_slug(),
			esc_html__( 'CBX Latest Petition', 'cbxpetition' ),
			[
				'classname'   => 'widget-cbxpetition',
				'description' => esc_html__( 'CBX latest Petition Widget', 'cbxpetition' )
			]
		);
	}//end of constructor method


	/**
	 * Return the widget slug.
	 *
	 * @return    Plugin slug variable.
	 * @since    1.0.0
	 *
	 */
	public function get_widget_slug() {
		return $this->widget_slug;
	}

	/**
	 * Update widget
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @throws Exception
	 */
	public function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );

		$widget_string = $before_widget;

		$title = apply_filters( 'widget_title',
			empty( $instance['title'] ) ? esc_html__( 'Latest Petitions',
				'cbxpetition' ) : $instance['title'], $instance, $this->id_base );
		// Defining the Widget Title
		if ( $title ) {
			$widget_string .= $args['before_title'] . $title . $args['after_title'];
		} else {
			$widget_string .= $args['before_title'] . $args['after_title'];
		}


		$atts = [];

		$limit      = $atts['limit'] = isset( $instance['limit'] ) ? absint( $instance['limit'] ) : 4;
		$order      = $atts['order'] = isset( $instance['order'] ) ? sanitize_text_field( wp_unslash( $instance['order'] ) ) : 'DESC';
		$order_by   = $atts['orderby'] = isset( $instance['orderby'] ) ? sanitize_text_field( wp_unslash( $instance['orderby'] ) ) : 'ID';
		$show_thumb = $atts['show_thumb'] = isset( $instance['show_thumb'] ) ? absint( $instance['show_thumb'] ) : 1;
		$show_title = $atts['show_title'] = isset( $instance['show_title'] ) ? absint( $instance['show_title'] ) : 1;
		$show_stat  = $atts['show_stat'] = isset( $instance['show_stat'] ) ? absint( $instance['show_stat'] ) : 1;

		extract( $instance, EXTR_SKIP );

		$attr_html = '';

		foreach ( $atts as $key => $value ) {
			$attr_html .= ' ' . $key . '="' . $value . '" ';
		}

		$content = do_shortcode( '[cbxpetition_latest ' . $attr_html . ']' );

		$widget_string .= $content;
		$widget_string .= $after_widget;

		echo $widget_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method widget


	/**
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		$title      = isset( $instance['title'] ) ? $instance['title'] : esc_html__( 'Latest Petitions', 'cbxpetition' );
		$limit      = isset( $instance['limit'] ) ? absint( $instance['limit'] ) : 4;
		$order      = isset( $instance['order'] ) ? sanitize_text_field( $instance['order'] ) : 'DESC';
		$order_by   = isset( $instance['orderby'] ) ? sanitize_text_field( $instance['orderby'] ) : 'ID';
		$show_thumb = isset( $instance['show_thumb'] ) ? absint( $instance['show_thumb'] ) : 1;
		$show_title = isset( $instance['show_title'] ) ? absint( $instance['show_title'] ) : 1;
		$show_stat  = isset( $instance['show_stat'] ) ? absint( $instance['show_stat'] ) : 1;
		?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ) ?>"><?php echo esc_html__( 'Title:', 'cbxpetition' ) ?></label>
            <input class="widefat" type="text" class=""
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ) ?>"
                   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ) ?>"
                   value="<?php echo esc_attr( $title ); ?>">
        </p>
        <!-- Display Limit -->
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
				<?php esc_html_e( 'Number of Petition', "cbxpetition" ); ?>
            </label>

            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="number"
                   value="<?php echo intval( $limit ); ?>"/>
        </p>
        <!-- Display Order -->
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>">
				<?php esc_html_e( 'Petition Display Order By ASC/DESC', 'cbxpetition' ); ?>
            </label>

            <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>"
                    id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>">
                <option value="DESC" <?php selected( $order, 'DESC' ); ?> ><?php esc_html_e( 'Descending', 'cbxpetition' ); ?></option>
                <option value="ASC" <?php selected( $order, 'ASC' ); ?> ><?php esc_html_e( 'Ascending', 'cbxpetition' ); ?></option>
            </select>
        </p>
        <!-- Display OrdeBy -->
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>">
				<?php esc_html_e( 'Petition Display Order By ID/Page', 'cbxpetition' ); ?>
            </label>

            <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>"
                    id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>">
                <option value="ID" <?php selected( $order_by, 'ID' ); ?> ><?php esc_html_e( 'ID', 'cbxpetition' ); ?></option>
                <option value="page" <?php selected( $order_by, 'page' ); ?> ><?php esc_html_e( 'Page', 'cbxpetition' ); ?></option>
            </select>
        </p>

        <!-- Show Thumbnail -->
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_thumb' ) ); ?>">
				<?php esc_html_e( 'Show Petition Thumbnail', 'cbxpetition' ); ?>
            </label>

            <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'show_thumb' ) ); ?>"
                    id="<?php echo esc_attr( $this->get_field_id( 'show_thumb' ) ); ?>">
                <option value="1" <?php selected( $show_thumb, 1 ); ?> ><?php esc_html_e( 'Yes', 'cbxpetition' ); ?></option>
                <option value="0" <?php selected( $show_thumb, 0 ); ?> ><?php esc_html_e( 'No', 'cbxpetition' ); ?></option>
            </select>
        </p>

        <!-- Show Title -->
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>">
				<?php esc_html_e( 'Show Petition Title', 'cbxpetition' ); ?>
            </label>

            <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'show_title' ) ); ?>"
                    id="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>">
                <option value="1" <?php selected( $show_title, 1 ); ?> ><?php esc_html_e( 'Yes', 'cbxpetition' ); ?></option>
                <option value="0" <?php selected( $show_title, 0 ); ?> ><?php esc_html_e( 'No', 'cbxpetition' ); ?></option>
            </select>
        </p>

        <!-- Show Stat -->
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_stat' ) ); ?>">
				<?php esc_html_e( 'Show Petition Stat', 'cbxpetition' ); ?>
            </label>

            <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'show_stat' ) ); ?>"
                    id="<?php echo esc_attr( $this->get_field_id( 'show_stat' ) ); ?>">
                <option value="1" <?php selected( $show_stat, 1 ); ?> ><?php esc_html_e( 'Yes', 'cbxpetition' ); ?></option>
                <option value="0" <?php selected( $show_stat, 0 ); ?> ><?php esc_html_e( 'No', 'cbxpetition' ); ?></option>
            </select>
        </p>
		<?php
	}// end of form method


	/**
	 * Update Widget
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']      = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['limit']      = isset( $new_instance['limit'] ) ? intval( $new_instance['limit'] ) : 4;
		$instance['order']      = isset( $new_instance['order'] ) ? sanitize_text_field( wp_unslash( $new_instance['order'] ) ) : 'DESC';
		$instance['orderby']    = isset( $new_instance['orderby'] ) ? sanitize_text_field( wp_unslash( $new_instance['orderby'] ) ) : 'ID';
		$instance['show_thumb'] = isset( $new_instance['show_thumb'] ) ? intval( $new_instance['show_thumb'] ) : 1;
		$instance['show_title'] = isset( $new_instance['show_title'] ) ? intval( $new_instance['show_title'] ) : 1;
		$instance['show_stat']  = isset( $new_instance['show_stat'] ) ? intval( $new_instance['show_stat'] ) : 1;

		return $instance;
	}// end of update method

}//end class CBXPetitionLatestWidget