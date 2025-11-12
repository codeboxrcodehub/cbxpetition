<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing petition signature listing
 *
 * @link       http://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXPetition
 * @subpackage CBXPetition/templates
 */
?>

<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

if ( is_array( $petition_signs ) && sizeof( $petition_signs ) > 0 ) {
	do_action( 'cbxpetition_signature_before', $petition_id );

	//echo '<div class="cbx-chota" id="cbxpetition_signature_wrapper_'.absint($petition_id).'">';
	echo '<div id="cbxpetition_signature_wrapper_'.absint($petition_id).'">';
	echo '<div class="cbxpetition_signature_wrapper">';
	echo '<h2 class="cbxpetition_section_heading cbxpetition_section_heading_signatures">' . esc_html__( 'Reasons for signing', 'cbxpetition' ) . '</h2>';
	/* translators: %d: petition count  */
	echo '<p class="cbxpetition_signature_listing_total">' . sprintf( esc_html__( 'Total Signatures: %d', 'cbxpetition' ), absint( $petition_count ) ) . '</p>';

	do_action( 'cbxpetition_signature_items_before', $petition_id );
	echo '<div class="cbxpetition_signature_items">';
	foreach ( $petition_signs as $petition_sign ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo cbxpetition_get_template_html( 'petition/signature.php', [
				'petition_id'   => $petition_id,
				'petition_sign' => $petition_sign
			]
		);
	}
	echo '</div>';
	do_action( 'cbxpetition_signature_items_after', $petition_id );

	if ( $petition_count > $per_page ) {
		$max_pages = ceil( $petition_count / $per_page );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="cbxpetition_load_more_signs_wrap"><a href="#" class="cbxpetition_button secondary cbxpetition_load_more_signs ld-ext-right" data-busy="0" data-petition-id="' . $petition_id . '" data-perpage="' . intval( $per_page ) . '" data-order="DESC" data-orderby="id" data-page="1" data-maxpage="' . intval( $max_pages ) . '">' . esc_html__( 'Load More',
				'cbxpetition' ) . '<span class="ld ld-ring ld-spin"></span></a></div>';
	}

	echo '</div>';//.cbxpetition_signature_wrapper
	echo '</div>';//#cbxpetition_signature_wrapper_{petition_id}

	do_action( 'cbxpetition_signature_after', $petition_id );
}


// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound