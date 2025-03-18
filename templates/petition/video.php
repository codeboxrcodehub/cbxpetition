<?php

use Cbx\Petition\Helpers\PetitionHelper;

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing petition video
 *
 * @link       http://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXPetition
 * @subpackage CBXPetition/templates
 */
?>

<?php
/**
 * Provide a public view for the plugin
 *
 * This file is used to markup the public facing form
 *
 * @link       http://codeboxr.com
 * @since      1.0.0
 *
 * @package    cbxpetition
 * @subpackage cbxpetition/templates
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<?php if ( isset( $videos['video-url'] ) && $videos['video-url'] != '' ): ?>
<!--    <div class="cbx-chota">-->
        <div class="cbxpetition_video_wrapper">
            <div class="cbxpetition_col cbxpetition_video_embed">

                <div class="cbxpetition_responsive_video">
				    <?php
				    global $wp_embed;
				    echo $wp_embed->run_shortcode( '[embed]' . esc_url( $videos['video-url'] ) . '[/embed]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				    ?>
                </div>


            </div>
            <div class="cbxpetition_col cbxpetition_video_story">
			    <?php
			    if ( isset( $videos['video-title'] ) && $videos['video-title'] != '' ):
				    echo '<h2 class="cbxpetition_video_title">' . esc_attr( $videos['video-title'] ) . '</h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			    endif;
			    ?>

			    <?php
			    if ( isset( $videos['video-description'] ) && $videos['video-description'] != '' ):
				    echo '<div class="cbxpetition_video_content">';

				    echo wpautop( wptexturize(wp_kses( $videos['video-description'], PetitionHelper::allowedHtmlTags() ))); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				    echo '</div>';
			    endif;
			    ?>
            </div>
        </div>
<!--    </div>-->
<?php endif;