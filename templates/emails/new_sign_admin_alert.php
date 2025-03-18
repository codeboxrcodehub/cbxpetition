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

$data = $email->object;
$anchor_kses = cbxpetition_wp_kses_link();

do_action( 'cbxpetition_email_header', $email_heading, $email ); ?>
    <p><?php echo esc_html__( 'Dear Admin,', 'cbxpetition' ); ?></p>
    <p><?php echo wp_kses(__('A new signature has been added to petition, "{petition}"! Here are the details:', 'cbxpetition'), $anchor_kses) ?></p>
    <h2><?php esc_html_e('Petition Details:', 'cbxpetition'); ?></h2>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'Title: {petition}', 'cbxpetition' ); ?></p>
    <p> <?php echo esc_html__( 'Total Signatures: {signature_count}', 'cbxpetition' ); ?></p>
    <h2><?php esc_html_e('Signature Details:', 'cbxpetition');?></h2>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'First Name: {signature_first_name}', 'cbxpetition' ); ?></p>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'Last Name: {signature_last_name}', 'cbxpetition' ); ?></p>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'Email: {signature_email}', 'cbxpetition' ); ?></p>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'Comment: {signature_comment}', 'cbxpetition' ); ?></p>
    <p><?php echo esc_html__( 'Signature Status: {signature_status}', 'cbxpetition' ); ?></p>
    <h2><?php esc_html_e('Moderation', 'cbxpetition'); ?></h2>
    <p style="margin-bottom:0;"><?php echo '{signature_edit_url}'; ?></p>
    <p><?php esc_html_e('Encourage more signatures by sharing your petition.', 'cbxpetition'); ?></p>
    <p><?php echo esc_html__( 'Thank you for advocating for this cause!', 'cbxpetition' ); ?></p>
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