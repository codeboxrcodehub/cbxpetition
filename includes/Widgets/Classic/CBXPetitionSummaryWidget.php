<?php
namespace Cbx\Petition\Widgets\Classic;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use WP_Widget;
use Cbx\Petition\Helpers\PetitionHelper;


/**
 * Class CBXPetition Summary Widget
 *
 * @since 1.0.2
 */
class CBXPetitionSummaryWidget extends WP_Widget {
	/**
	 * CBXPetitionSummaryWidget constructor.
	 */
	public function __construct() {
		parent::__construct( 'cbxpetition_summary',
			esc_html__( 'CBX Petition Summary', 'cbxpetition' ),
			[
				'description' => esc_html__( 'Single Petition Summary Widget for CBX Petition', 'cbxpetition' ),
			] );
	}//end of constructor method

	/**
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : esc_html__( 'Petition Summary', 'cbxpetition' );

		$default_sections     = apply_filters( 'cbxpetition_summary_shortcode_default_sections', 'title,content,stat,expire_date' );
		$default_sections_arr = explode( ',', $default_sections );

		$petition_id = isset( $instance['petition_id'] ) ? intval( $instance['petition_id'] ) : 0;
		$sections    = isset( $instance['sections'] ) ? $instance['sections'] : $default_sections_arr;
		if ( ! is_array( $sections ) ) {
			$sections = [];
		}


		?>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ) ?>"><?php echo esc_html__( 'Title:', 'cbxpetition' ) ?></label>
            <input type="text"  name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ) ?>"
                   value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'petition_id' ) ) ?>"><?php echo esc_html__( 'Petition ID:', 'cbxpetition' ) ?></label>
            <input type="text"  name="<?php echo esc_attr( $this->get_field_name( 'petition_id' ) ) ?>"
                   id="<?php echo esc_attr( $this->get_field_id( 'petition_id' ) ); ?>"
                   value="<?php echo intval( $petition_id ); ?>"/><br/>
			<?php
			if ( $petition_id > 0 && get_post( $petition_id ) !== null ) {
				/* translators: %1$s: Petition link , %2$s: Petition title  */
				echo sprintf( wp_kses( __( 'Showing petition for <a target="_blank" href="%1$s"><strong>%2$s</strong></a>', 'cbxpetition' ), [
					'a'      => [
						'href'   => [],
						'target' => []
					],
					'strong' => []
				] ), esc_url( get_permalink( $petition_id ) ), esc_attr( get_the_title( $petition_id ) ) );
			} else {
				echo wp_kses( __( 'Seems <strong>Petition ID</strong> doesn\'t belong to any valid Petition', 'cbxpetition' ), [ 'strong' => [] ] );
			}
			?>
        </p>
        <p><?php
			/* translators: %s: Petition Link  */
			echo sprintf( wp_kses( __( 'Click <a target="_blank" href="%s">here</a> to see all the petitions', 'cbxpetition' ), [
				'a' => [
					'href'   => [],
					'target' => []
				]
			] ), esc_url( admin_url( 'edit.php?post_type=cbxpetition' ) ) );
			?></p>
        <p>
            <strong><?php esc_html_e( 'Petition Summary Sections', 'cbxpetition' ); ?></strong>
        </p>
        <p>
			<?php
			foreach ( $default_sections_arr as $default_section ) {
				$default_section = trim( strtolower( $default_section ) );

				$checked = in_array( $default_section, $sections ) ? ' checked ' : '';
				?>

                <input <?php echo esc_attr( $checked ); ?> type="checkbox" 
                                                           name="<?php echo esc_attr( $this->get_field_name( 'sections' ) ) ?>[<?php echo esc_attr( $default_section ); ?>]"
                                                           id="<?php echo esc_attr( $this->get_field_id( 'sections' ) ); ?>-<?php echo esc_attr( $default_section ); ?>"
                                                           value="<?php echo esc_attr( $default_section ); ?>">
                <label for="<?php echo esc_attr( $this->get_field_id( 'sections' ) ); ?>-<?php echo esc_attr( $default_section ); ?>"><?php echo esc_attr( ucwords( $default_section ) ); ?></label>
                <br/>
				<?php
			}
			?>

        </p>


		<?php
	}// end of form method

	/**
	 * Update widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : esc_html__( 'Petition Summary', 'cbxpetition' );
		if ( isset( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$petition_id = isset( $instance['petition_id'] ) ? intval( $instance['petition_id'] ) : 0;
		$sections    = isset( $instance['sections'] ) ? $instance['sections'] : [];
		if ( ! is_array( $sections ) ) {
			$sections = [];
		}

		$sections = implode( ',', $sections );

		echo do_shortcode( '[cbxpetition_summary petition_id="' . $petition_id . '" sections="' . $sections . '" ]' );

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method widget

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

		$instance['title']       = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['petition_id'] = isset( $new_instance['petition_id'] ) ? intval( $new_instance['petition_id'] ) : 0;
		$instance['sections']    = isset( $new_instance['sections'] ) ? $new_instance['sections'] : [];

		if ( ! is_array( $instance['sections'] ) ) {
			$instance['sections'] = [];
		}

		return $instance;
	}// end of update method

}//end class CBXPetitionSummaryWidget