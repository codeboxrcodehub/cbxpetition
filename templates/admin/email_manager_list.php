<?php
/**
 * Provide a dashboard view for the plugin
 * This file is used to markup the public-facing aspects of the plugin.
 * @link       https://codeboxr.com
 * @since      2.0.0
 * @package    cbxpetition
 * @subpackage cbxpetition/templates/admin
 */
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<div class="section_header row">
    <div class="col-12 section_header_l">
        <h2><?php esc_html_e( 'Email notifications', 'cbxpetition' ); ?></h2>
        <p><?php esc_html_e( 'Here are the list of all the email notification send from this accounting system. Please note that, few notification may sent from background without any setting based on the type of not.', 'cbxpetition' ); ?></p>
    </div>
    <!--                        <div class="col-6 section_header_r"></div>-->
</div>
<div id="email_manager_listing_wrapper">
    <h3><?php esc_html_e( 'Notification list', 'cbxpetition' ); ?></h3>
    <table class="table table-bordered table-striped table-hover" id="cbxpetition_email_items">
        <thead>
        <tr>
            <th><?php esc_html_e( 'Title', 'cbxpetition' ); ?></th>
            <th><?php esc_html_e( 'Type', 'cbxpetition' ); ?></th>
            <th><?php esc_html_e( 'Recipient(s)', 'cbxpetition' ); ?></th>
            <th><?php esc_html_e( 'Actions', 'cbxpetition' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php
		$admin_url = admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-emails' );

		$enabled_svg     = cbxpetition_esc_svg( cbxpetition_load_svg( 'icon_enabled', 'app' ) );
		$disabled_svg    = cbxpetition_esc_svg( cbxpetition_load_svg( 'icon_disabled', 'app' ) );

		foreach ( $emails as $email ):
			$id = $email->id;
			$title       = $email->title;
			$description = $email->description;
			$settings    = $email->settings;
			$user_email  = $email->is_user_email();

			$manual = $email->is_manual();


			if ( ! is_array( $settings ) ) {
				$settings = [];
			}

			$enabled    = isset( $settings['enabled'] ) ? $settings['enabled'] : '';
			$email_type = isset( $settings['email_type'] ) ? $settings['email_type'] : 'html';

			$status_title = ( $enabled == 'yes' ) ? esc_attr__( 'Enabled', 'cbxpetition' ) : esc_attr__( 'Disabled', 'cbxpetition' );

			$button_status_class = ( $enabled == 'yes' ) ? 'cbxpetition_email_status_enabled' : 'cbxpetition_email_status_disabled';
			if ( $manual ) {
				$button_status_class = 'cbxpetition_email_status_manual';
				$status_title        = esc_attr__( 'Manually Triggered', 'cbxpetition' );
			}

			// $enabled_icon_class = ( $enabled == 'yes' ) ? 'cbx-icon-enabled' : 'cbx-icon-disabled';
			$status_svg = ( $enabled == 'yes' ) ? $enabled_svg : $disabled_svg;

			$recipient = $email->get_recipient();

			$action_url = add_query_arg( [ 'edit' => $id ], $admin_url );
			?>
            <tr>
                <td>
                    <span aria-label="<?php echo esc_attr( $status_title ); ?>" data-balloon-pos="up" class="button cbxpetition_email_status <?php echo esc_attr( $button_status_class ); ?> outline secondary icon icon-only">
                        <i class="cbx-icon">
                            <?php echo $status_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            ?>
                        </i>
                    </span>
					<?php echo esc_html( $title ); ?>
                    <p><small><?php echo esc_html( $description ); ?></small></p>
                </td>
                <td><?php echo esc_html( $email->get_content_type() ); ?></td>
                <td><?php echo ( $user_email ) ? esc_html__( 'System User/Guest', 'cbxpetition' ) : esc_html( $recipient ); ?></td>
                <td><a class="button primary icon icon-inline small" href="<?php echo esc_url( $action_url ); ?>">
                        <i class="cbx-icon cbx-icon-edit-white"></i>
                        <span class="button-label"><?php esc_html_e( 'Edit', 'cbxpetition' ); ?></span>
                    </a>
                </td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound