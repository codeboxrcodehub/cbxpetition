<?php
use Cbx\Petition\Helpers\PetitionHelper;

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing petition letter
 *
 * @link       http://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXPetition
 * @subpackage CBXPetition/templates
 */


if ( ! defined( 'WPINC' ) ) {
	die;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

if ( is_array( $petition_letter ) && sizeof( $petition_letter ) > 0 ):
	do_action( 'cbxpetition_letter_before', $petition_id );

	//echo '<div class="cbx-chota">';
	echo '<div class="cbxpetition_letter_wrapper">';
	do_action( 'cbxpetition_letter_wrapper_start', $petition_id );

	$recipients = isset( $petition_letter['recipients'] ) ? $petition_letter['recipients'] : [];

	if ( is_array( $recipients ) && sizeof( $recipients ) > 0 ):

		echo '<h2 class="cbxpetition_section_heading cbxpetition_section_heading_note">' . esc_html__( 'The letter', 'cbxpetition' ) . '</h2>';
		echo '<div id="cbxpetition_letter_recipients">';

		foreach ( $recipients as $recipient ) {
			$name        = isset( $recipient['name'] ) ? $recipient['name'] : '';
			$designation = isset( $recipient['designation'] ) ? $recipient['designation'] : '';
			$email       = isset( $recipient['email'] ) ? $recipient['email'] : '';

			echo '<div class="cbxpetition_letter_recipient">';
			echo '<p class="recipient_name">' . esc_attr( $name ) . '</p>';
			echo '<p class="recipient_designation">' . esc_attr( $designation ) . '</p>';
			echo '</div>';
		}
		echo '</div>';
	endif;

	$letter = isset( $petition_letter['letter'] ) ? sanitize_textarea_field( $petition_letter['letter'] ) : '';
	if ( $letter != '' ) {
		echo '<div id="cbxpetition_letter">';
		echo wp_kses( wpautop( $letter ), PetitionHelper::allowedHtmlTags() );
		echo '</div>';
	}

	do_action( 'cbxpetition_letter_wrapper_end', $petition_id );
	echo '</div>'; //.cbxpetition_letter_wrapper
	//cho '</div>'; //.cbx-chota

	do_action( 'cbxpetition_letter_after', $petition_id );
endif;


// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound