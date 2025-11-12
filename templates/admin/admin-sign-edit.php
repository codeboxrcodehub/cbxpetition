<?php
/**
 * Provide a dashboard view for the plugin
 *
 * This file is used to markup the admin-facing signature edit
 *
 * @link       https://codeboxr.com
 * @since      1.0.7
 *
 * @package    cbxpetition
 * @subpackage cbxpetition/templates/admin
 *
 */

use Cbx\Petition\Helpers\PetitionHelper;

if ( ! defined( 'WPINC' ) ) {
	die;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$plus_svg  = cbxpetition_esc_svg(cbxpetition_load_svg( 'icon_plus' ));
$back_svg  = cbxpetition_esc_svg(cbxpetition_load_svg( 'icon_back' ));
?>
<div class="wrap cbx-chota cbxpetition-page-wrapper cbxpetition-singature-wrapper" id="cbxpetition-singature">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2></h2>
                <div class="wp-heading-wrap">
                    <div class="wp-heading-wrap-left pull-left">
                        <h1 class="wp-heading-inline wp-heading-inline-cbxpetition">
		                    <?php esc_html_e( 'Petition: Signature', 'cbxpetition' ); ?>
                        </h1>
                    </div>
                    <div class="wp-heading-wrap-right pull-right">
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cbxpetition' ) ); ?>"
                           class="button primary icon icon-inline icon-right  mr-5"><i class="cbx-icon"><?php echo $plus_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></i><span class="button-label"><?php esc_html_e( 'New Petition', 'cbxpetition' ); ?></span></a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cbxpetition-settings' ) ); ?>"
                           class="button outline primary"><?php esc_html_e( 'Global Settings', 'cbxpetition' ); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="postbox">
                    <div class="clear clearfix"></div>
                    <div class="inside" style="margin-bottom:0 !important;">
                        <h3 class="cbx-sub-heading">
                            <span><?php esc_html_e('Petition', 'cbxpetition'); ?>: <a target="_blank"
                                               href="<?php echo esc_url( get_permalink( $petition_id ) ); ?>"><?php echo esc_attr(get_the_title( $petition_id )); ?></a></span>
                            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-signatures' ) ); ?>"
                               class="button secondary icon icon-inline pull-right"
                               role="button"><i class="cbx-icon">
                                <?php echo $back_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></i>
                                <span class="button-label"><?php esc_attr_e( 'Back', 'cbxpetition' ); ?></span></a>
                        </h3>
                    </div>
                    <div class="clear clearfix"></div>
                </div>
                <div class="postbox">
                    <div class="inside">
                        <h3 class="cbx-sub-heading">
                            <span><?php 
                            /* translators: %d: Signature Log id  */
                            echo sprintf( esc_html__( 'Signature Edit: ID - %d', 'cbxpetition' ), intval( $log_id ));  ?></span>
                        </h3>
                        <div class="cbxpetition_signform_wrapper">
							<?php
							do_action( 'cbxpetition_sign_edit_before', $log_id, $petition_id );
							?>

							<?php if ( ! is_null( $sign_info ) ) : ?>
                                <form data-busy="0" id="cbxpetition_sign_edit_form"
                                      class="cbx_form_wrapper cbx_form_wrapper_signature" action="" method="post"
                                      novalidate="novalidate">
									<?php

									$first_name = sanitize_text_field( $sign_info['f_name'] );
									$last_name  = sanitize_text_field( $sign_info['l_name'] );
									$email      = sanitize_email( $sign_info['email'] );
									$add_by     = absint( $sign_info['add_by'] );
									?>

									<?php
									do_action( 'cbxpetition_sign_edit_start', $log_id, $petition_id );
									?>
                                    <div class="cbxpetition-signform-field">
                                        <label for="first_name"><?php esc_html_e( 'First Name', 'cbxpetition' ); ?></label>
                                        <input class="disabled" readonly id="first_name" type="text" name="first_name"
                                               value="<?php echo esc_attr( $first_name ); ?>"/>
                                    </div>
                                    <div class="cbxpetition-signform-field">
                                        <label for="last_name"><?php esc_html_e( 'Last Name', 'cbxpetition' ); ?></label>
                                        <input class="disabled" readonly id="last_name" type="text" name="last_name"
                                               value="<?php echo esc_attr( $last_name ); ?>"/>
                                    </div>
                                    <div class="cbxpetition-signform-field">
                                        <label for="email">
											<?php esc_html_e( 'Email', 'cbxpetition' ); ?>
											<?php
											if ( $add_by > 0 ) {

												echo '(';

												if ( current_user_can( 'edit_user', $add_by ) ) {
													echo '<a target="_blank" href="' . esc_url( get_edit_user_link( $add_by ) ) . '">';
												}

												echo PetitionHelper::userDisplayName( $add_by ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

												if ( current_user_can( 'edit_user', $add_by ) ) {
													echo '</a>';
												}

												echo ')';
											} else {
												echo esc_html__( '(Guest user)', 'cbxpetition' );
											}

											?>
                                        </label>
                                        <input class="disabled" readonly id="email" type="email" name="email"
                                               value="<?php echo esc_attr( $email ); ?>"/>
                                    </div>

                                    <div class="cbxpetition-signform-field">
                                        <label for="add_date"><?php esc_html_e( 'Add Date', 'cbxpetition' ); ?></label>
                                        <input class="disabled" readonly id="add_date" type="text" name="add_date"
                                               value="<?php echo esc_attr( PetitionHelper::datetimeShowingFormat( $sign_info['add_date'] ) ); ?>"/>
                                    </div>
									<?php if ( $sign_info['mod_date'] !== null ): ?>
                                        <div class="cbxpetition-signform-field">
                                            <label for="mod_date"><?php esc_html_e( 'Modified Date', 'cbxpetition' ); ?></label>
                                            <input class="disabled" readonly id="mod_date" type="text" name="mod_date"
                                                   value="<?php echo esc_attr( PetitionHelper::datetimeShowingFormat( $sign_info['mod_date'] ) ); ?>"/>
                                        </div>
									<?php endif; ?>
                                    <div class="cbxpetition-signform-field">
                                        <label for="signature_comment"><?php esc_html_e( 'Comment', 'cbxpetition' ); ?></label>
                                        <textarea
                                                name="comment" id="signature_comment"
                                                class="regular-text cbxpetition_sign_comment"
                                                rows="10" cols="50"><?php echo esc_textarea( wp_unslash( $comment ) ); ?></textarea>
                                    </div>
                                    <div class="cbxpetition-signform-field">
                                        <label for="singature_state"><?php esc_html_e( 'Status', 'cbxpetition' ); ?></label>
                                        <select name="state" id="singature_state">
											<?php
											//unless a status is in 'unverified' state it should be save as 'unverified'
											if ( $state != 'unverified' ) {
												if ( isset( $state_arr['unverified'] ) ) {
													unset( $state_arr['unverified'] );
												}
											}

											//if a status is 'unverified' it should not be save as 'pending', it should be either approve or unapprove
											if ( $state == 'unverified' ) {
												if ( isset( $state_arr['pending'] ) ) {
													unset( $state_arr['pending'] );
												}
											}

											foreach ( $state_arr as $state_key => $state_name ) {
												?>
                                                <option value="<?php echo esc_attr( $state_key ); ?>" <?php if ( $state_key == $state ) {
													echo ' selected="selected" ';
												} ?> > <?php echo esc_attr( $state_name ); ?>
                                                </option>
											<?php } ?>
                                        </select>
                                    </div>

                                    <input type="hidden" name="id" value="<?php echo absint( $log_id ); ?>"/>
                                    <input type="hidden" name="cbxpetition_sign_edit" value="1"/>
                                    <input type="hidden" name="action" value="cbxpetition_sign_edit"/>
									<?php wp_nonce_field( 'cbxpetition_nonce', 'cbxpetition_token' ); ?>

									<?php
									do_action( 'cbxpetition_sign_edit_end', $log_id, $petition_id );
									?>

                                    <button type="submit"
                                            class="ld-ext-right button primary cbxpetition-submit mt-10 "><?php esc_html_e( 'Update Sign', 'cbxpetition' ); ?>
                                        <span class="ld ld-ring ld-spin"></span></button>

									<?php
									do_action( 'cbxpetition_sign_edit_after_submit', $log_id, $petition_id );
									?>
                                </form>
								<?php
								do_action( 'cbxpetition_sign_edit_after', $log_id, $petition_id );
								?>
							<?php else :
								echo '<div class="notice notice-error inline"><p>' . esc_html__( 'Sorry, invalid signature or signature not found.', 'cbxpetition' ) . '</p></div>';
								?>
							<?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound