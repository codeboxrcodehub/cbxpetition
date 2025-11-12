<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing petition sign form
 *
 * @link       http://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXPetition
 * @subpackage CBXPetition/templates
 */

use Cbx\Petition\Helpers\PetitionHelper;

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


// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$terms_page     = absint( $settings->get_field( 'terms_page', 'cbxpetition_general', 0 ) );
$terms_page_url = ( $terms_page > 0 ) ? get_permalink( $terms_page ) : '#';

//$show_login_form         = esc_attr( $settings->get_field( 'show_login_form', 'cbxpetition_general', 'yes' ) );

$sign_comment_req        = absint( $settings->get_field( 'sign_comment_req', 'cbxpetition_general', 0 ) );
$allow_guest_sign        = esc_attr( $settings->get_field( 'allow_guest_sign', 'cbxpetition_general', 'yes' ) );
$guest_login_form        = esc_attr( $settings->get_field( 'guest_login_form', 'cbxpetition_general', 'wordpress' ) );
$sign_comment_req_length = apply_filters( 'cbxpetition_sign_comment_req_length', 50 );
$sign_comment_req_html   = ( $sign_comment_req ) ? '  required data-rule-minlength="' . absint( $sign_comment_req_length ) . '" ' : '';
$login_html              = '';

if ( ! is_user_logged_in() ):
    if($guest_login_form != 'off'){
	    if ( $guest_login_form != 'none' ) {
		    $login_html .= cbxpetition_get_template_html( 'global/login_form.php', [
			    'settings' => $settings,
		    ] );
	    } else {
		    $login_html .= cbxpetition_get_template_html( 'global/login_url.php', [
			    'settings' => $settings,
		    ] );
	    }
    }
    else{
	    /*$login_html .= cbxpetition_get_template_html( 'global/login_off.php', [
		    'settings' => $settings,
	    ] );*/
    }

	//echo $login_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped


endif;

?>
<!--<div class="cbx-chota">-->
<div class="cbxpetition_signform_wrapper">
	<?php
	$user_id = absint( get_current_user_id() );

	if ( $title != '' ) {
		echo '<h2 class="cbxpetition_section_heading cbxpetition_section_heading_form">' . esc_html( $title ) . '</h2>';
	}


	if ( is_singular() ) {
		$redirect_url = get_permalink();
		$login_url    = wp_login_url( $redirect_url );
	} else {
		global $wp;

		$redirect_url = home_url( add_query_arg( [], $wp->request ) );
		$login_url    = wp_login_url( $redirect_url );
	}

	$current_user_info = null;
	$guest             = true;

	if ( is_user_logged_in() ) {
		$guest = false;

		$current_user      = wp_get_current_user();
		$user_id           = $current_user->ID;
		$user_display_name = isset( $current_user->display_name ) ? $current_user->display_name : '';
		$user_display_name = \Cbx\Petition\Helpers\PetitionHelper::userDisplayNameAlt( $current_user, $user_display_name );
		$user_email        = $current_user->user_email;
		$log_out_url       = wp_logout_url( $redirect_url );
	}

	if ( $petition_id == 0 ) {
		//echo '<div class="cbx-chota"><div class="container">';
		echo '<p class="cbxpetition-info cbxpetition-info-notfound cbxpetition-alert cbxpetition-alert-info">' . esc_html__( 'No valid petition found.',
				'cbxpetition' ) . '</p>';
		//echo '</div></div>';
	} else {
		$expire_date = get_post_meta( $petition_id, '_cbxpetition_expire_date', true );

		if ( $expire_date == '' ) {
			//echo '<div class="cbx-chota"><div class="container">';
			echo '<p class="cbxpetition-info cbxpetition-info-datenotset cbxpetition-alert cbxpetition-alert-info">' . esc_html__( 'Sorry, petition did not start yet.', 'cbxpetition' ) . '</p>';
			//echo '</div></div>';
		} elseif ( $expire_date != '' ) {
			$expire_date = new \DateTime( $expire_date );
			$now_date    = new \DateTime( 'now' );

			if ( $expire_date < $now_date ) {
				//echo '<div class="cbx-chota"><div class="container">';
				echo '<p class="cbxpetition-info cbxpetition-info-alreadyexpired cbxpetition-alert cbxpetition-alert-danger">' . esc_html__( 'Sorry, petition already expired',
						'cbxpetition' ) . '</p>';
				//echo '</div></div>';
			} else {

				$is_petition_signed_by_user = PetitionHelper::isPetitionSignedByUser( $petition_id, $user_id );

				if ( $is_petition_signed_by_user !== false ) {
					//echo '<div class="cbx-chota"><div class="container">';
					echo '<p class="cbxpetition-info cbxpetition-info-alreadysigned cbxpetition-alert cbxpetition-alert-success">' . esc_html__( 'You already signed the petition, thank you.',
							'cbxpetition' ) . '</p>';
					/* translators: %1$s: user name , %2$s: logout link  */
					echo '<p class="cbxpetition-sign-as cbxpetition-sign-as-user"> <strong>' . sprintf( wp_kses( __( 'You are logged in as <strong>%1$s</strong>, <a class="cbxpetition-sign-logout-confirm" href="%2$s">Logout?</a>', 'cbxpetition' ), [
							'a'      => [
								'href'  => [],
								'class' => [],
								'style' => []
							],
							'strong' => []
						] ), esc_html( $user_display_name ), esc_url( $log_out_url ) ) . '</strong></p>';
					//echo '</div></div>';
				} else {

					do_action( 'cbxpetition_sign_form_before', $petition_id );

					if ( ! is_user_logged_in() ) {
						echo $login_html;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						do_action( 'cbxpetition_sign_form_before_guest', $petition_id );
					}

					?>
					<?php
					if ( is_user_logged_in() || ( $allow_guest_sign == 'yes' ) ):
						?>
                        <!--                        <div class="cbx-chota">
													<div class="container">-->
                        <form action="#" data-busy="0" class="cbx_form_wrapper cbxpetition-signform" method="POST"
                              novalidate="novalidate">

							<?php
							do_action( 'cbxpetition_sign_form_start', $petition_id );
							?>

							<?php if ( $guest ): ?>
                                <p class="cbxpetition-sign-as cbxpetition-sign-as-guest">
                                    <strong><?php esc_html_e( 'Sign the petition as guest user', 'cbxpetition' ); ?></strong>
                                </p>
                                <div class="cbxpetition-signform-field">
                                    <label class="cbxpetition-signform-field-label"
                                           for="cbxpetition-fname"><?php esc_html_e( 'First Name', 'cbxpetition' ); ?></label>
                                    <input type="text"
                                           class="cbxpetition-signform-field-input cbxpetition-signform-field-text cbxpetition-signform-field-fname"
                                           name="cbxpetition-fname" id="cbxpetition-fname"
                                           placeholder="<?php esc_html_e( 'First Name', 'cbxpetition' ); ?>"
                                           value="" required data-rule-required="true" data-rule-minlength="2"/>
                                </div>
                                <div class="cbxpetition-signform-field">
                                    <label class="cbxpetition-signform-field-label"
                                           for="cbxpetition-lname"><?php esc_html_e( 'Last Name', 'cbxpetition' ); ?></label>
                                    <input type="text"
                                           class="cbxpetition-signform-field-input cbxpetition-signform-field-text cbxpetition-signform-field-lname"
                                           name="cbxpetition-lname" id="cbxpetition-lname"
                                           placeholder="<?php esc_html_e( 'Last Name', 'cbxpetition' ); ?>"
                                           value="" required data-rule-required="true" data-rule-minlength="2"/>

                                </div>

                                <div class="cbxpetition-signform-field">
                                    <label class="cbxpetition-signform-field-label"
                                           for="cbxpetition-email"><?php esc_html_e( 'Email', 'cbxpetition' ); ?></label>

                                    <input type="email"
                                           class="cbxpetition-signform-field-input cbxpetition-signform-field-email cbxpetition-signform-field-email"
                                           name="cbxpetition-email" id="cbxpetition-email"
                                           placeholder="<?php esc_html_e( 'Email', 'cbxpetition' ); ?>"
                                           value="" required data-rule-required="true" data-rule-email="true"/>

                                </div>
							<?php else: ?>
                                <p class="cbxpetition-sign-as cbxpetition-sign-as-user">
                                    <strong><?php
										/* translators: %1$s: user name , %2$s: logout link  */
										echo sprintf( wp_kses( __( 'You are logged in as <strong>%1$s</strong>, <a class="cbxpetition-sign-logout-confirm" href="%2$s">Logout?</a>', 'cbxpetition' ), [
											'a'      => [
												'href'  => [],
												'class' => [],
												'style' => []
											],
											'strong' => []
										] ), esc_html( $user_display_name ), esc_url( $log_out_url ) ); ?></strong>
                                </p>

							<?php endif; ?>


                            <div class="cbxpetition-signform-field">
                                <label class="cbxpetition-signform-field-label"
                                       for="cbxpetition-comment"><?php esc_html_e( 'Comment', 'cbxpetition' ); ?></label>
                                <textarea <?php echo $sign_comment_req_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?> cols="30" rows="6"
                                   class="cbxpetition-signform-field-input cbxpetition-signform-field-textarea cbxpetition-signform-field-comment"
                                   name="cbxpetition-comment"
                                   id="cbxpetition-comment"
                                   placeholder="<?php esc_html_e( 'I’m signing because… (optional)', 'cbxpetition' ); ?>"></textarea>

                            </div>
							<?php
							do_action( 'cbxpetition_sign_form_before_privacy', $petition_id );
							?>


                            <div class="checkbox_field magic_checkbox_field cbxpetition-signform-field">
                                <input required data-rule-required="true" type="checkbox" name="cbxpetition-privacy"
                                       id="cbxpetition-privacy"
                                       class="magic-checkbox cbxpetition-signform-field-input cbxpetition-signform-field-checkbox cbxpetition-signform-field-privacy"
                                       value="1"/>
                                <label class="cbxpetition-signform-field-label cbxpetition-signform-field-label-terms"
                                       for="cbxpetition-privacy">

									<?php
									/* translators: %1$s: policy page url, %2$s: terms page link  */
									echo sprintf( wp_kses( __( 'YES, I agree with <a href="%1$s" target="_blank">Privacy policy</a> and <a target="_blank" href="%2$s">Terms & Condition</a>', 'cbxpetition' ), [
										'a' => [
											'href'   => [],
											'target' => [],
											'class'  => [],
											'style'  => []
										]
									] ), esc_url( get_privacy_policy_url() ), esc_url( $terms_page_url ) ); ?>
                                </label>
                            </div>
							<?php
							do_action( 'cbxpetition_sign_form_end', $petition_id );
							?>
                            <p class="text-center">
                                <button type="submit"
                                        class="ld-ext-right cbxpetition_button primary cbxpetition-sign-submit"><?php esc_html_e( 'Sign This', 'cbxpetition' ); ?>
                                    <span class="ld ld-ring ld-spin">
                </span></button>
                            </p>
							<?php
							do_action( 'cbxpetition_sign_form_after_submit', $petition_id );
							?>

                            <input type="hidden" name="cbxpetition-id" value="<?php echo absint( $petition_id ) ?>"/>
                            <input type="hidden" name="cbxpetition_sign_submit" value="1"/>
                            <input type="hidden" name="action" value="cbxpetition_sign_submit"/>
							<?php wp_nonce_field( 'cbxpetition_nonce', 'cbxpetition_token' ); ?>
                        </form>
                        <!--                            </div>
												</div>-->
					<?php endif;
					do_action( 'cbxpetition_sign_form_after', $petition_id );
				}
			}
		}//end petition didn't expire yet
	}//end if petition_id valid
	?>
</div>
<!--
</div>-->
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound