<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = $email->object;

$show_activation = false;
if(!is_null($data) && isset($data['activation']) && $data['activation'] != ''){
    $show_activation = true;
}


do_action( 'cbxpetition_email_header', $email_heading, $email ); ?>
    <p><strong><?php echo esc_html__( 'Dear {signature_first_name} {signature_last_name},', 'cbxpetition' ); ?></strong></p>
    <p><?php echo esc_html__( 'Thank you for signing the petition, "{petition}"! Your support is crucial in making a difference.', 'cbxpetition' ); ?></p>

    <h2><?php esc_html_e('Petition Details:', 'cbxpetition');?></h2>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'Title: {petition}', 'cbxpetition' ); ?></p>
    <p><?php echo esc_html__( 'Total Signatures: {signature_count}', 'cbxpetition' ); ?></p>
    <h2><?php esc_html_e('Signature Details:', 'cbxpetition');?></h2>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'First Name: {signature_first_name}', 'cbxpetition' ); ?></p>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'Last Name: {signature_last_name}', 'cbxpetition' ); ?></p>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'Your Email: {signature_email}', 'cbxpetition' ); ?></p>
    <p style="margin-bottom:0;"><?php echo esc_html__( 'Comment: {signature_comment}', 'cbxpetition' ); ?></p>
    <p><?php echo esc_html__( 'Signature Status: {signature_status}', 'cbxpetition' ); ?></p>
    <?php if($show_activation): ?>
    <h2><?php esc_html_e('Important: Confirm Your Signature', 'cbxpetition'); ?></h2>
    <p style="margin-bottom:0;"><?php esc_html_e('Once verified, your signature will be counted.', 'cbxpetition'); ?></p>
    <p><?php echo '{signature_activation_link}'; ?></p>
    <h2><?php echo esc_html__( 'Thank you.', 'cbxpetition' ); ?></h2>
    <?php endif; ?>
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