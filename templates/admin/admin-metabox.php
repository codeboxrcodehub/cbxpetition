<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXPetition
 * @subpackage CBXPetition/templates/admin
 */

$dir_info    = \Cbx\Petition\Helpers\PetitionHelper::checkUploadDir();
$petition_id = intval( $post->ID );

$disabled_here = ( $petition_id == 0 ) ? '' : '';
$hide_here     = ( $petition_id == 0 ) ? '' : '';


?>
<div id="petitioncustom_meta_box_wrapper">
    <div id="cbxpetition_dashboard_tab_wrap">
		<?php do_action( 'cbxpetition_meta_tabs_before', $petition_id, $disabled_here, $hide_here ); ?>
        <div id="cbxpetition_dashboard_tabs" class="tabs" data-petition-id="<?php echo intval($petition_id); ?>">
			<?php do_action( 'cbxpetition_meta_tabs_start', $petition_id, $disabled_here, $hide_here ); ?>
            <a href="#cbxpetition_dashboard_content_intro" class="active">
				<?php esc_attr_e( 'General', 'cbxpetition' ); ?>
            </a>
            <a href="#cbxpetition_dashboard_content_photo" <?php echo esc_attr($disabled_here) . esc_attr($hide_here); ?>>
				<?php esc_attr_e( 'Media', 'cbxpetition' ); ?>
            </a>
            <a href="#cbxpetition_dashboard_content_video" <?php echo esc_attr($disabled_here) . esc_attr($hide_here); ?>>
				<?php esc_attr_e( 'Video', 'cbxpetition' ); ?>
            </a>
            <a href="#cbxpetition_dashboard_content_letter" <?php echo esc_attr($disabled_here) . esc_attr($hide_here); ?>>
				<?php esc_attr_e( 'The Letter', 'cbxpetition' ); ?>
            </a>
			<?php do_action( 'cbxpetition_meta_tabs_end', $petition_id, $disabled_here, $hide_here ); ?>
        </div>
		<?php do_action( 'cbxpetition_meta_tabs_after', $petition_id, $disabled_here, $hide_here ); ?>

		<?php do_action( 'cbxpetition_meta_contents_before', $petition_id, $disabled_here, $hide_here ); ?>
        <div id="cbxpetition_dashboard_tab_contents">
			<?php do_action( 'cbxpetition_meta_contents_start', $petition_id, $disabled_here, $hide_here ); ?>
            <div id="cbxpetition_dashboard_content_intro" class="cbxpetition_dashboard_content_tab  active">
				<?php

				$signature_target = intval( get_post_meta( $petition_id, $prefix . 'signature_target', true ) );
				$expire_date      = get_post_meta( $petition_id, '_cbxpetition_expire_date', true );

				if ( $signature_target == 0 ) {
					$signature_target = 100;
				}
				if ( $expire_date == '' ) {
					$current_datetime = current_datetime()->format( 'Y-m-d H:i:s' );
					$expire_date      = gmdate( 'Y-m-d H:i:s', strtotime( $current_datetime . ' + 7 days' ) );
				}

				if ( $petition_id > 0 ):
					?>
                    <div class="cbxpetition_dashboard_edit_field_group">
                        <label class="cbxpetition_dashboard_edit_label cbxpetition_dashboard_edit_label_signature_target"
                               for="cbxpetition_dashboard_edit_signature_target"><?php esc_html_e( 'Signature Target(*)', 'cbxpetition' ); ?></label>
                        <input required type="number" min="0"
                               class="cbxpetition_dashboard_edit_field cbxpetition_dashboard_edit_field_signature_target"
                               id="cbxpetition_dashboard_edit_signature_target" name="cbxpetitionmeta[signature-target]"
                               value="<?php echo intval( $signature_target ); ?>"/>
                    </div>
                    <div class="cbxpetition_dashboard_edit_field_group">
                        <label class="cbxpetition_dashboard_edit_label cbxpetition_dashboard_edit_label_expire_date"
                               for="cbxpetition_dashboard_edit_expire_date"><?php esc_html_e( 'Expire Date(*)', 'cbxpetition' ); ?></label>
                        <input required type="text"
                               class="cbxpetition_dashboard_edit_field cbxpetition_dashboard_edit_field_expire_date"
                               id="cbxpetition_dashboard_edit_expire_date" name="cbxpetitionmeta[expire-date]"
                               value="<?php echo esc_attr( $expire_date ); ?>"/>
                    </div>
				<?php endif; ?>
            </div> <!-- #cbxpetition_dashboard_content_intro -->
            <div id="cbxpetition_dashboard_content_photo" class="cbxpetition_dashboard_content_tab">
				<?php
				$media_info = get_post_meta( $petition_id, '_cbxpetition_media_info', true );
				echo '<div data-petition_id="' . intval( $petition_id ) . '" id="cbxpetition_meta_media_photo" class="group cbxpetition_meta_media">';

				// check petition photos
				$petition_photos = [];

				$petition_photos = isset( $media_info['petition-photos'] ) ? $media_info['petition-photos'] : [];

				if ( ! is_array( $petition_photos ) ) {
					$petition_photos = [];
				}


				//$photo_data = 0;

				/*if ( sizeof( $petition_photos ) > 0 ) {
					$photo_data = 1;

					$js_data_photos = [];
					foreach ( $petition_photos as $petition_photo ) {
						$js_data_photos[] = [
							'name' => esc_attr( $petition_photo ),
							'url'  => esc_url( $dir_info['cbxpetition_base_url'] . $petition_id . '/' . $petition_photo ),
							'type' => 'image'
						];
					}

					$js_data = json_encode( $js_data_photos );


					wp_add_inline_script( 'cbxpetition-admin', 'cbxpetition_admin_js_vars.photo.exists = ' . absint( $photo_data ) . '; cbxpetition_admin_js_vars.photo.data = ' . $js_data . ';' );
				}*/

				echo '<div id="petition_photos_wrapper">';

				$photo_max_files   = $settings->get_field( 'photo_max_files', 'cbxpetition_general', 6 );
				$delete_all_photos = '<a class="petition_photos_delete button outline primary" href="#">' . esc_attr( 'Delete All Photos', 'cbxpetition' ) . '</a>';
				/* translators: %d: Max photo number  */
				$max_photos_title  = sprintf( esc_html__( 'Max photos: %d', 'cbxpetition' ), $photo_max_files ); 

				echo '<h3 class="cbxpetition_fields_heading">' . esc_html__( 'Petition Photos', 'cbxpetition' ) . '<span class="small">(' . esc_attr($max_photos_title) . ')</span>' . wp_kses($delete_all_photos , ['a' => ['class'=> [],'href' => []]]) . '</h3>';

				echo '<div id="petition_photo_uploader">';
				echo '<span class="petition_photo_uploader_text button secondary">';
				echo esc_attr__( 'Upload Photo(s)', 'cbxpetition' );
				echo '<input type="file"  id="petition_photo_upload"  />';
				echo '</span>';

				echo '</div>';//#petition_photo_uploader

				echo '<div id="petition_photos" data-file_count="' . intval(sizeof( $petition_photos )) . '">';
				if ( sizeof( $petition_photos ) > 0 ) {
					//$photo_data = 1;

					//$js_data_photos = [];
					foreach ( $petition_photos as $petition_photo ) {
						$url       = esc_url( $dir_info['cbxpetition_base_url'] . $petition_id . '/' . $petition_photo );
						$url_thumb = esc_url( $dir_info['cbxpetition_base_url'] . $petition_id . '/thumbnail/' . $petition_photo );

						echo '<div style="background-image: url(' . esc_url($url_thumb) . ');" class="petition_photo" >';
						echo '<span class="petition_photo_delete" style="text-align: center;"><a data-file="' . esc_attr( $petition_photo ) . '" class="button primary petition_photo_delete_button" href="#">' . esc_attr__( 'Delete Photo', 'cbxpetition' ) . '</a></span>';
						echo '</div>';
					}
				}
				echo '</div>';//#petition_photos


				echo '</div>';//#petition_photos_wrapper
				?>

				<?php

				$petition_banner_url  = '';
				$petition_banner      = '';
				$banner_data          = 0;
				$display_banner_style = ' style="" ';
				//$display_uploader_style = '  ';
				$banner_extra_class = '';

				if ( isset( $media_info['banner-image'] ) && $media_info['banner-image'] != '' ) {
					$petition_banner      = $media_info['banner-image'];
					$petition_banner_url  = $dir_info['cbxpetition_base_url'] . $petition_id . '/' . $petition_banner;
					$banner_data          = 1;
					$display_banner_style = '  style="background-image: url(' . $petition_banner_url . ');" ';
					//$display_uploader_style = ' style="display: none;" ';
					$banner_extra_class = 'petition_banner_exists';
				}


				echo '<div id="petition_banner_wrapper">';

				echo '<h3 class="cbxpetition_fields_heading">' . esc_html__( 'Featured Banner', 'cbxpetition' ) . '</h3>';

				echo '<div id="petition_banner_uploader" class="' . esc_attr( $banner_extra_class ) . '" ' . $display_banner_style . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<span role="button" class="petition_banner_uploader_text button secondary">';
				echo esc_attr__( 'Upload Banner', 'cbxpetition' );
				echo '<input type="file"  id="petition_banner_upload"  />';
				echo '</span>';
				echo '<span role="button" class="button primary petition_banner_delete" style="text-align: center;">' . esc_attr__( 'Delete Banner', 'cbxpetition' ) . '</span>';
				echo '</div>';//#petition_banner_uploader
				echo '</div>';//#petition_banner_wrapper
				echo '</div>';//#cbxpetition_meta_media_photo
				?>
            </div>
            <div id="cbxpetition_dashboard_content_video" class="cbxpetition_dashboard_content_tab">
				<?php
				echo '<div data-petition_id="' . absint( $petition_id ) . '" id="cbxpetition_meta_media_video" class="group cbxpetition_meta_media">';
				$video_url = $video_title = $video_description = '';


				// check video url
				if ( isset( $media_info['video-url'] ) && $media_info['video-url'] != null ) {
					$video_url = $media_info['video-url'];
				}


				// check video title
				if ( isset( $media_info['video-title'] ) && $media_info['video-title'] != null ) {
					$video_title = $media_info['video-title'];
				}

				// check video description
				if ( isset( $media_info['video-description'] ) && $media_info['video-description'] != null ) {
					$video_description = $media_info['video-description'];
				}

				echo '<div class="cbxpetition_dashboard_edit_field_group">';
				echo '<label class="cbxpetition_dashboard_edit_label cbxpetition_dashboard_edit_video_title" for="cbxpetition_dashboard_edit_video_title">' . esc_html__( 'Video Title', 'cbxpetition' ) . '</label>';
				echo '<input id="cbxpetition_dashboard_edit_video_title" type="text" name="cbxpetitionmeta[video-title]" class="cbxpetition_dashboard_edit_field cbxpetition_dashboard_edit_field_video_title" value="' . esc_attr( $video_title ) . '" />';
				echo '</div>';

				echo '<div class="cbxpetition_dashboard_edit_field_group">';
				echo '<label class="cbxpetition_dashboard_edit_label cbxpetition_dashboard_edit_video_url" for="cbxpetition_dashboard_edit_video_url">' . esc_html__( 'Video Url(Youtube)', 'cbxpetition' ) . '</label>';
				echo '<input id="cbxpetition_dashboard_edit_video_url" type="text" name="cbxpetitionmeta[video-url]" class="cbxpetition_dashboard_edit_field cbxpetition_dashboard_edit_field_video_url" value="' . esc_url( $video_url ) . '" />';
				echo '</div>';


				echo '<div class="cbxpetition_dashboard_edit_field_group">';
				echo '<label class="cbxpetition_dashboard_edit_label cbxpetition_dashboard_edit_video_description" for="cbxpetition_dashboard_edit_video_description">' . esc_html__( 'Video Description', 'cbxpetition' ) . '</label>';
				echo '<div class="petition_video_wrapper">';
				wp_editor( $video_description, 'cbxpetition_dashboard_edit_video_description', [
					'wpautop'       => true,
					'media_buttons' => false,
					'textarea_name' => 'cbxpetitionmeta[video-description]',
					'textarea_rows' => 10,
					'teeny'         => true,
					'quicktags'     => false
				] );
				echo '</div>';
				echo '</div>';
				echo '</div>'; //#cbxpetition_meta_media_video
				?>
            </div>
            <div id="cbxpetition_dashboard_content_letter" class="cbxpetition_dashboard_content_tab">
				<?php
				echo '<div id="cbxpetition_meta_letter" class="group"><div class="cbxpetition_letter_section">';
				//template for new recipient
				echo '<script id="cbx_recipientlists_template" type="x-tmpl-mustache">
									<li class="cbxpetition_repeat_field_recipient recipientlist_wrap recipientlist_{{index}}">
									    <div class="field recipientlist_start">
									        <a href="#" title="' . esc_html__( 'Move Recipient', 'cbxpetition' ) . '" class="dashicons dashicons-menu move-recipient"></a>
									    </div>
										<div class="field recipientlist_name">
											<input type="text" value="" name="cbxpetitionmeta[recipients][{{index}}][name]" class="regular-text" 
												placeholder="' . esc_html__( 'Name', 'cbxpetition' ) . '" id="recipientlist_name_{{index}}" class="form-control half" />
										</div>
										<div class="field recipientlist_designation">
											<input type="text" value="" name="cbxpetitionmeta[recipients][{{index}}][designation]" class="regular-text" 
												placeholder="' . esc_html__( 'Designation', 'cbxpetition' ) . '" id="recipientlist_designation_{{index}}" class="form-control half" />
										</div>
										<div class="field recipientlist_email">
											<input type="email" value="" name="cbxpetitionmeta[recipients][{{index}}][email]" class="regular-text" 
												placeholder="' . esc_html__( 'Email', 'cbxpetition' ) . '" id="recipientlist_email_{{index}}" class="form-control half" />
										</div>
										<div class="field recipientlist_actions">											
											<a href="#" title="' . esc_html__( 'Delete Recipient', 'cbxpetition' ) . '" class="dashicons dashicons-post-trash trash-repeat recipient_delete_icon"></a>
										</div>
									</li>
							</script>';


				$letter          = get_post_meta( $petition_id, '_cbxpetition_letter', true );
				$letter          = is_array( $letter ) ? $letter : [];
				$petition_letter = isset( $letter['letter'] ) ? $letter['letter'] : '';

				$recipients     = [];
				$recipients     = isset( $letter['recipients'] ) ? $letter['recipients'] : [];
				$recipients     = is_array( $recipients ) ? $recipients : [];
				$recipients_len = sizeof( $recipients );

				echo '<div class="petition_letter_wrapper">';
				echo '<h3 class="cbxpetition_fields_heading">' . esc_html__( 'Letter Copy', 'cbxpetition' ) . '</h3>';
				wp_editor( $petition_letter, 'petition_letter', [
					'wpautop'       => true,
					'media_buttons' => false,
					'textarea_name' => 'cbxpetitionmeta[letter]',
					'textarea_rows' => 10,
					'teeny'         => true,
					'quicktags'     => false
				] );
				echo '</div>';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<h3 class="cbxpetition_fields_heading">' . esc_html__( 'Letter Recipients', 'cbxpetition' ) . '<a href="#" class="button outline primary cbxpetition_button cbxpetition_button_alt  cbxpetition_add_recipient" data-recipientcount="' . $recipients_len . '">' . esc_html__( 'Add Recipient', 'cbxpetition' ) . '</a></h3>';


				$recipients_index = 0;
				echo '<ul id="cbxpetition_repeat_fields_recipient">';
				foreach ( $recipients as $recipient ) {
					echo '<li class="cbxpetition_repeat_field_recipient recipientlist_wrap recipientlist_' . esc_attr($recipients_index) . '">
										<div class="field recipientlist_start">
										    <a href="#" title="' . esc_html__( 'Move Recipient', 'cbxpetition' ) . '" class="dashicons dashicons-menu move-recipient"></a>
                                        </div>
										<div class="field recipientlist_name">
											<input type="text" value="' . esc_attr($recipient['name']) . '"
												   name="cbxpetitionmeta[recipients][' .esc_attr($recipients_index) . '][name]"
												   placeholder="' . esc_html__( 'Name', 'cbxpetition' ) . '"
												   id="recipientlist_name_' . esc_attr($recipients_index) . '" class="regular-text half" />
										</div>
										<div class="field recipientlist_designation">
											<input type="text" value="' . esc_attr($recipient['designation']) . '"
												   name="cbxpetitionmeta[recipients][' . esc_attr($recipients_index) . '][designation]"
												   placeholder="' . esc_html__( 'Designation', 'cbxpetition' ) . '"
												   id="recipientlist_designation_' . esc_attr($recipients_index) . '" class="regular-text half" />
										</div>
										<div class="field recipientlist_email">
											<input type="text" value="' . esc_attr($recipient['email']) . '"
												   name="cbxpetitionmeta[recipients][' . esc_attr($recipients_index) . '][email]"
												   placeholder="' . esc_html__( 'Email', 'cbxpetition' ) . '"
												   id="recipientlist_email_' . esc_attr($recipients_index) . '" class="regular-text half" />
										</div>
										<div class="field recipientlist_actions">											
											<a href="#" title="' . esc_html__( 'Delete Recipient', 'cbxpetition' ) . '" class="dashicons dashicons-post-trash trash-repeat recipient_delete_icon"></a>
										</div>
									</li>';
					$recipients_index ++;
				}
				echo '</ul>';

				echo '</div></div>';
				?>
            </div>
			<?php do_action( 'cbxpetition_meta_contents_end', $petition_id, $disabled_here, $hide_here ); ?>
        </div>
		<?php do_action( 'cbxpetition_meta_contents_after', $petition_id, $disabled_here, $hide_here ); ?>
    </div>
</div>