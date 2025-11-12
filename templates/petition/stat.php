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

echo '<div class="cbxpetition_stat_wrapper">';
if ( $show_count ) {
	/* translators: %1$d: Petition Target, %2$d: signature count  */
	echo '<p class="cbxpetition_stat_count">' . sprintf( wp_kses(__( '<span class="cbxpetition_stat_count_target">Target: %1$d</span> <span class="cbxpetition_stat_count_received">Received: %2$d</span>', 'cbxpetition' ),['span' => ['class'  => []]]), intval($target), intval($signature_count) ) . '</p>';
}

if ( $show_progress ) {
	echo '<div class="cbxpetition-progress-wrapper">
	            <div class="cbxpetition-progress">
	                <div class="cbxpetition-progress-value" style="width: ' . absint( $signature_ratio ) . '%;"></div>
	            </div>
	            <span class="cbxpetition-progress-ratio">' . esc_html( $signature_ratio ) . '%</span>
	            <div class="clear clearfix"></div>
	        </div>';
}

echo '</div>';

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound