<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$data = $email->object;

$show_activation = false;
if ( ! is_null( $data ) && isset( $data['activation'] ) && $data['activation'] != '' ) {
    $show_activation = true;
}

do_action( 'cbxpetition_email_header', $email_heading, $email ); ?>
    <div class="content-section">

        <h2 class="heading">{email_heading}</h2>

        <p class="message">
            <strong><?php echo esc_html__( 'Dear {signature_first_name} {signature_last_name},', 'cbxpetition' ); ?></strong><br>
            <?php echo esc_html__( 'Thank you for signing the petition, "{petition}"! Your support is crucial in making a difference.', 'cbxpetition' ); ?>
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

        <div class="quote-section">
            <h4 class="form-summary-heading"><?php echo esc_html__( 'The Letter:', 'cbxpetition' ); ?></h4>
            <p class="quote-text">
                {petition_letter}
            </p>
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
                    <td class="form-label"><?php echo esc_html__( 'Your Email:', 'cbxpetition' ); ?></td>
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

        <!-- <?php if ( isset( $data['id'] ) && $data['id'] > 0 ): ?>
        <p class="message"><?php echo '{signature_link}'; ?></p>
        <?php endif; ?> -->

        <?php if ( $show_activation ): ?>
            <h2><?php esc_html_e( 'Important: Confirm Your Signature', 'cbxpetition' ); ?></h2>
            <p class="message"
               style="margin-bottom:0;"><?php esc_html_e( 'Once verified, your signature will be counted.', 'cbxpetition' ); ?></p>
            <p class="message"><?php echo '{signature_activation_link}'; ?></p>
            <h2><?php echo esc_html__( 'Thank you.', 'cbxpetition' ); ?></h2>
        <?php endif; ?>


        <h2><?php esc_html_e( 'Manage Your Signature', 'cbxpetition' ); ?></h2>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
                <td class="button-center">
                    <?php if ( isset( $data['id'] ) && $data['id'] > 0 ): ?>
                        <a href="{signature_url}" class="button">View Signature</a>
                    <?php endif; ?>
                    <?php if ( isset( $data['delete_token'] ) && $data['delete_token'] != '' ): ?>
                        <a href="{delete_url}" class="button button-secondary">Delete Signature</a>
                    <?php endif; ?>
                </td>
            </tr>
            </tbody>
        </table>

        <p class="message" style="margin-top:25px;">
            <?php echo esc_html__( 'Want to make an even bigger impact? Share this petition with your friends and network.', 'cbxpetition' ); ?>
        </p>
        <p class="message">
            <?php echo esc_html__( 'Thank you for your support! Stay tuned for updates.', 'cbxpetition' ); ?>
        </p>
    </div>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
    echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'cbxpetition_email_footer', $email );

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound