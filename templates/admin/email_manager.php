<?php
/**
 * Provide a dashboard view for the plugin
 * This file is used to markup the public-facing aspects of the plugin.
 * @link       https://codeboxr.com
 * @since      2.0.0
 * @package    cbxpetition
 * @subpackage cbxpetition/templates/admin
 */

use Cbx\Petition\Helpers\PetitionHelper;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$more_v_svg = cbxpetition_esc_svg( cbxpetition_load_svg( 'icon_more_v' ) );
?>
<div class="wrap cbx-chota cbxpetition-page-wrapper cbxpetition-email-manager-wrapper"
     id="cbxpetition-email-manager">
    <div class="container">
        <div class="row">
            <div class="col-12 mb-20">
                <h2></h2>
				<?php settings_errors(); ?>
				<?php do_action( 'cbxpetition_wpheading_wrap_before', 'email_manager' ); ?>
                <div class="wp-heading-wrap">
                    <div class="wp-heading-wrap-left pull-left">
						<?php do_action( 'cbxpetition_wpheading_wrap_left_before', 'email_manager' ); ?>
                        <h1 class="wp-heading-inline wp-heading-inline-cbxpetition">
							<?php esc_html_e( 'CBX Petition: Email Manager', 'cbxpetition' ); ?>
                        </h1>
						<?php do_action( 'cbxpetition_wpheading_wrap_left_before', 'email_manager' ); ?>
                    </div>
                    <div class="wp-heading-wrap-right pull-right">
						<?php do_action( 'cbxpetition_wpheading_wrap_right_before', 'email_manager' ); ?>

						<?php do_action( 'cbxpetition_wpheading_wrap_right_after', 'email_manager' ); ?>
                    </div>
                </div>
				<?php do_action( 'cbxpetition_wpheading_wrap_after', 'email_manager' ); ?>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12">
				<?php do_action( 'cbxpetition_email_manager_before' ); ?>
                <div id="email_manager_wrapper">
					<?php do_action( 'cbxpetition_email_manager_start', 'email_manager' ); ?>
					<?php
					$template_data = [ 'settings' => $settings ];
					if ( $edit ):
						$template_data['email'] = $emails[ $id ];
						$template_data['id']    = $id;

						echo cbxpetition_get_template_html( 'admin/email_manager_edit.php', $template_data );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					else:
						$template_data = [ 'emails' => $emails ];

						echo cbxpetition_get_template_html( 'admin/email_manager_list.php', $template_data );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					endif;
					?>
					<?php do_action( 'cbxpetition_email_manager_end', 'email_manager' ); ?>
                </div>
				<?php do_action( 'cbxpetition_email_manager_after', 'email_manager' ); ?>
            </div>
        </div>
    </div>
</div>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound