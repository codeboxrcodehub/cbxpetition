<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing petition banner
 *
 * @link       https://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXPetition
 * @subpackage CBXPetition/templates
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

if ( $cbxpetition_banner != '' ) {
	do_action( 'cbxpetition_banner_before', $petition_id );

	echo '<div class="cbxpetition_banner_wrapper" style="background-image:url( \'' . esc_url( $cbxpetition_banner ) . '\');">';
	do_action( 'cbxpetition_banner_wrap_start', $petition_id );
	//echo '<img src = "'.esc_url($cbxpetition_banner).'" alt = "petition-cover" />';
	do_action( 'cbxpetition_banner_wrap_end', $petition_id );
	echo '</div>';

	do_action( 'cbxpetition_banner_after', $petition_id );
}

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound