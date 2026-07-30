<?php
/**
 * Review added by user email for admin
 *
 * This template can be overridden by copying it to yourtheme/cbxpetition/emails/new_sign_admin_alert.php
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$data        = $email->object;
$anchor_kses = cbxpetition_wp_kses_link();

do_action( 'cbxpetition_email_header', $email_heading, $email ); ?>
    <div class="content-section">

        <h2 class="heading">{email_heading}</h2>

        <p class="message">
            <?php echo esc_html__( 'Dear Admin,', 'cbxpetition' ); ?><br>
            <?php echo wp_kses( __( 'A new signature has been added to petition, "{petition}"! Here are the details:', 'cbxpetition' ), $anchor_kses ); ?>
        </p>

        <div class="form-summary-section">
            <h3 class="form-summary-heading"><?php echo esc_html__( 'Petition Details:', 'cbxpetition' ); ?></h3>

            <table role="presentation" class="form-summary-table" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="form-label" width="35%"><?php echo esc_html__( 'Title:', 'cbxpetition' ); ?></td>
                    <td class="form-value">{petition}</td>
                </tr>

                <tr>
                    <td class="form-label"><?php echo esc_html__( 'Total Signatures:', 'cbxpetition' ); ?></td>
                    <td class="form-value">{signature_count}</td>
                </tr>
            </table>
        </div>

        <div class="form-summary-section">
            <h3 class="form-summary-heading"><?php echo esc_html__( 'Signature Details:', 'cbxpetition' ); ?></h3>

            <table role="presentation" class="form-summary-table" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="form-label" width="35%"><?php echo esc_html__( 'First Name:', 'cbxpetition' ); ?></td>
                    <td class="form-value">{signature_first_name}</td>
                </tr>

                <tr>
                    <td class="form-label"><?php echo esc_html__( 'Last Name:', 'cbxpetition' ); ?></td>
                    <td class="form-value">{signature_last_name}</td>
                </tr>

                <tr>
                    <td class="form-label"><?php echo esc_html__( 'Email:', 'cbxpetition' ); ?></td>
                    <td class="form-value">{signature_email}</td>
                </tr>

                <tr>
                    <td class="form-label"><?php echo esc_html__( 'Comment:', 'cbxpetition' ); ?></td>
                    <td class="form-value">{signature_comment}</td>
                </tr>

                <tr>
                    <td class="form-label"><?php echo esc_html__( 'Signature Status:', 'cbxpetition' ); ?></td>
                    <td class="form-value">{signature_status}</td>
                </tr>
            </table>
        </div>

        <p class="message">
            <?php esc_html_e( 'Please review the submission, take any necessary action if required, and follow up with the customer/user where appropriate.', 'cbxpetition' ); ?>
        </p>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="button-center">
                    <a href="{signature_edit_url}"
                       class="button"><?php esc_html_e( 'Moderation', 'cbxpetition' ); ?></a>
                </td>
            </tr>
        </table>

        <p class="message">
            <?php esc_html_e( 'Encourage more signatures by sharing your petition.', 'cbxpetition' ); ?>
        </p>
        <p class="message">
            <?php esc_html_e( 'Thank you for advocating for this cause!', 'cbxpetition' ); ?>
        </p>

        <p class="message" style="margin-top:25px;">
            <?php esc_html_e( 'Thank you', 'cbxpetition' ); ?>,<br>
            <strong>{site_title}</strong>
        </p>
    </div>
<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
    echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}
?>

<?php
do_action( 'cbxpetition_email_footer', $email );

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound