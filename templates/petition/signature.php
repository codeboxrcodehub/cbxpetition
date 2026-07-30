<?php

use Cbx\Petition\Helpers\PetitionHelper;

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing petition single signature display
 *
 * @link       https://codeboxr.com
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

$signature_id = isset( $petition_sign['id'] ) ? absint( $petition_sign['id'] ) : 0;

// determine if current logged-in user owns this signature
$highlight_class = '';
$you_text = '';
$show_delete = false;
if ( is_user_logged_in() ) {
	$current_user = wp_get_current_user();
	$current_id   = isset( $current_user->ID ) ? absint( $current_user->ID ) : 0;

	$signature_add_by = isset( $petition_sign['add_by'] ) ? absint( $petition_sign['add_by'] ) : 0;

	if ( $current_id > 0 && $signature_add_by === $current_id ) {
		$show_delete = true;
		$highlight_class = 'cbxpetition_signature_item_me';
		$you_text = '<span>'.esc_html__( 'You', 'cbxpetition' ).'</span>';
	}
}

echo '<div class="cbxpetition_signature_item '.esc_attr($highlight_class).'">';
do_action( 'cbxpetition_signature_item_start', $petition_id, $petition_sign, $signature_id );

$name = '';
if ( $petition_sign['f_name'] != '' ) {
	$name = wp_unslash( $petition_sign['f_name'] );
	if ( $petition_sign['l_name'] != '' ) {
		$name .= ' ' . wp_unslash( $petition_sign['l_name'] );
	}
}


$signature_card_id = $signature_id > 0 ? 'id="cbxpetition_signature_item_' . esc_attr( $signature_id ) . '"' : '';
$location          = isset( $petition_sign['location'] ) ? $petition_sign['location'] : '';
$location_html     = ( $location != '' ) ? ' | ' . $location : '';

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo '<div class="signature-card" ' . $signature_card_id . '>	                
	                <div class="signature-info">
	                	<div class="signature-thumb-photo">' . get_avatar( sanitize_email( $petition_sign['email'] ), 60 ) . '</div>
						<div class="signature-person">
							<h3 class="signature-name">' . esc_attr( $name ) . wp_kses($you_text, ['span' => []]).'</h3>
		                    <span class="signature-date-time">' . esc_attr( PetitionHelper::dateShowingFormat( $petition_sign['add_date'] ) ) . esc_html__( ' at ',
		'cbxpetition' ) . esc_attr( PetitionHelper::timeShowingFormat( $petition_sign['add_date'] ) ) . $location_html . '</span>';//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
// delete button for logged in owner

echo '</div>';
if ( $show_delete ) {
	echo '<span type="button"
						        class="button outline error cbxpetition-sign-delete-btn"
						        data-busy="0"
						        data-petition-id="' . absint( $petition_id ) . '"
						        data-signature-id="' . absint( $signature_id ) . '">'
	     . esc_html__( 'Delete my signature', 'cbxpetition' ) .
	     '</span>';
}
echo '</div>
	                <div class="signature-message-wrap">
                        <div class="signature-message signature-message-readmore">' . wp_kses( wpautop( $petition_sign['comment'] ), PetitionHelper::allowedHtmlTags() ) . '</div>	                
					</div>';


echo '<div class="signature-actions">';
do_action( 'cbxpetition_signature_item_action_start', $petition_id, $petition_sign, $signature_id );


/*if ( $show_delete ) {
	echo '<span type="button"
						        class="button error signature-action cbxpetition-sign-delete-btn"
						        data-busy="0"
						        data-petition-id="' . absint( $petition_id ) . '"
						        data-signature-id="' . absint( $signature_id ) . '">'
	     . esc_html__( 'Delete my signature', 'cbxpetition' ) .
	     '</span>';
}*/
do_action( 'cbxpetition_signature_item_action_end', $petition_id, $petition_sign, $signature_id );
echo '</div>';


// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo '       <div class="clear clearfix"></div></div>';
do_action( 'cbxpetition_signature_item_end', $petition_id, $petition_sign, $signature_id );
echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped


// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound