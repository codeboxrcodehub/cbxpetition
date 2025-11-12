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

echo '<div class="cbxpetition_signature_item">';
do_action( 'cbxpetition_signature_item_start', $petition_id, $petition_sign );

$name = '';
if ( $petition_sign['f_name'] != '' ) {
	$name = wp_unslash( $petition_sign['f_name'] );
	if ( $petition_sign['l_name'] != '' ) {
		$name .= ' ' . wp_unslash( $petition_sign['l_name'] );
	}
}
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo '<div class="signature-card">
	                
	                <div class="signature-info">
	                	<div class="signature-thumb-photo">' . get_avatar( sanitize_email( $petition_sign['email'] ), 60 ) . '</div>
						<div class="signature-person">
							<h3 class="signature-name">' . esc_attr( $name ) . '</h3>
		                    <span class="signature-date-time">' . esc_attr(PetitionHelper::dateShowingFormat( $petition_sign['add_date'] )) . esc_html__( ' at ',
		'cbxpetition' ) . esc_attr(PetitionHelper::timeShowingFormat( $petition_sign['add_date'] )) . '</span>				
						</div>	                                        	                    
	                </div>
	                <div class="signature-message-wrap">
                        <div class="signature-message signature-message-readmore">' . wp_kses( wpautop( $petition_sign['comment']) , PetitionHelper::allowedHtmlTags() ) . '</div>	                
					</div>	                                
	           <div class="clear clearfix"></div></div>';
do_action( 'cbxpetition_signature_item_end', $petition_id, $petition_sign );
echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped


// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound