<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$data = $email->object;

do_action( 'cbxpetition_email_header', $email_heading, $email ); ?>
    <p><strong><?php echo esc_html__( 'Dear, {signature_first_name} {signature_last_name}', 'cbxpetition' ); ?></strong></p>
    <p><?php echo esc_html__( 'We’re happy to inform you that your signature for the petition "{petition}" has been successfully approved! Thank you for taking a stand and supporting this cause.', 'cbxpetition' ); ?></p>
    <h2><?php esc_html_e('Petition Details:', 'cbxpetition');?></h2>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'Title: {petition}', 'cbxpetition' ); ?></p>
    <p><?php echo esc_html__( 'Total Signatures: {signature_count}', 'cbxpetition' ); ?></p>
    <h2><?php esc_html_e('Signature Details:', 'cbxpetition');?></h2>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'First Name: {signature_first_name}', 'cbxpetition' ); ?></p>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'Last Name: {signature_last_name}', 'cbxpetition' ); ?></p>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'Email: {signature_email}', 'cbxpetition' ); ?></p>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'Comment: {signature_comment}', 'cbxpetition' ); ?></p>
    <p><?php echo esc_html__( 'Signature Status: {signature_status}', 'cbxpetition' ); ?></p>
    <?php if(isset($data['id']) && $data['id'] > 0): ?>
    <p><?php echo '{signature_link}'; ?></p>
    <?php endif; ?>
    <?php if(isset($data['delete_token']) && $data['delete_token'] != ''): ?>
    <h2><?php esc_html_e('Manage Your Signature', 'cbxpetition'); ?></h2>
    <p><?php echo '{signature_delete_link}'; ?></p>
    <?php endif; ?>
    <h2><?php echo esc_html__( 'Thank you.', 'cbxpetition' ); ?></h2>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'Want to make an even bigger impact? Share this petition with your friends and network.', 'cbxpetition' ); ?></p>
    <p><?php echo esc_html__( 'Thank you for your support! Stay tuned for updates.', 'cbxpetition' ); ?></p>
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