<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing petition stat display
 *
 * @link       https://codeboxr.com
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
 * @link       https://codeboxr.com
 * @since      1.0.0
 *
 * @package    cbxpetition
 * @subpackage cbxpetition/templates
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$progress_ratio_class = ($signature_ratio >= 50)? 'cbxpetition-progress-ratio cbxpetition-progress-ratio-green' : 'cbxpetition-progress-ratio';
$progress_value_class = ($signature_ratio >= 50)? 'cbxpetition-progress-value cbxpetition-progress-value-green' : 'cbxpetition-progress-value';

echo '<div class="cbxpetition_stat_wrapper_2">';
if ( $show_count ) {
	echo '<p class="cbxpetition_stat_count">';
	/* translators: %1$d: Petition Target, %2$d: signature count  */
    echo '<span class="cbxpetition_stat_count_target">'.sprintf( wp_kses( __( '<i>%1$d</i> / <i>%2$d</i>', 'cbxpetition' ), [ 'span' => [ 'class' => [] ], 'i' => [] ] ), intval( $target ), intval( $signature_count ) ).'</span>';
    echo '<span class="'.esc_attr($progress_ratio_class).'">' . esc_html( $signature_ratio ) . '%</span>';
    echo '</p>';
}

if ( $show_progress ) {
	echo '<div class="cbxpetition-progress-wrapper">
	            <div class="cbxpetition-progress">
	                <div class="'.esc_attr($progress_value_class).'" style="width: ' . absint( $signature_ratio ) . '%;"></div>
	            </div>	            
	            <div class="clear clearfix"></div>
	        </div>';
}
echo '</div>';
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound