<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing petition photos
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

use Cbx\Petition\Helpers\PetitionHelper;

if ( is_array( $petition_photos ) && sizeof( $petition_photos ) > 0 ):

	do_action( 'cbxpetition_photos_before', $petition_id );

	$dir_info = PetitionHelper::checkUploadDir();
	//echo '<div class="cbx-chota">';
	echo '<div class="cbxpetition_photos_wrapper">';
	echo '<div id="cbxpetition_photos">';

	foreach ( $petition_photos as $filename ) {
		$petition_photo_url       = $dir_info['cbxpetition_base_url'] . $petition_id . '/' . $filename;
		$petition_photo_thumb_url = $dir_info['cbxpetition_base_url'] . $petition_id . '/thumbnail/' . $filename;

		echo '<div class="cbxpetition_photo">';
		echo '<a href="' . esc_url( $petition_photo_url ) . '" data-gall="cbxpetition_photo_background-' . esc_attr($petition_id) . '" class="venobox cbxpetition_photo_background" style="background-image: url(\'' . esc_url( $petition_photo_url ) . '\');"></a>';
		echo '</div>';
	}

	echo '</div>'; //.cbxpetition_photos
	echo '</div>'; //.cbxpetition_photos_wrapper
	//echo '</div>'; //.cbx-chota

	do_action( 'cbxpetition_photos_after', $petition_id );
endif;