<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$plugin_url = \Cbx\Petition\Helpers\PetitionHelper::url_utmy( '#' );
$doc_url    = \Cbx\Petition\Helpers\PetitionHelper::url_utmy( '#' );

$save_svg  = cbxpetition_esc_svg(cbxpetition_load_svg( 'icon_save' ));
?>
<div class="wrap cbx-chota cbxchota-setting-common cbxpetition-page-wrapper cbxpetition-setting-wrapper"
     id="cbxpetition-setting">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2></h2>
				<?php do_action( 'cbxpetition_wpheading_wrap_before', 'settings' ); ?>
                <div class="wp-heading-wrap">
                    <div class="wp-heading-wrap-left pull-left">
						<?php do_action( 'cbxpetition_wpheading_wrap_left_before', 'settings' ); ?>
                        <h1 class="wp-heading-inline wp-heading-inline-cbxpetition">
							<?php esc_html_e( 'Petition: Global Settings', 'cbxpetition' ); ?>
                        </h1>
						<?php do_action( 'cbxpetition_wpheading_wrap_left_before', 'settings' ); ?>
                    </div>
                    <div class="wp-heading-wrap-right  pull-right">
						<?php do_action( 'cbxpetition_wpheading_wrap_right_before', 'settings' ); ?>
                        <a href="<?php echo esc_url(admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-doc' )); ?>"
                           class="button outline primary"><?php esc_html_e( 'Support & Docs', 'cbxpetition' ); ?></a>
                        <a href="#" id="save_settings"
                           class="button primary icon icon-inline icon-right mr-5">
                            <i class="cbx-icon"><?php echo $save_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?></i>
                            <span class="button-label"><?php esc_html_e( 'Save Settings', 'cbxpetition' ); ?></span>
                        </a>
						<?php do_action( 'cbxpetition_wpheading_wrap_right_after', 'settings' ); ?>
                    </div>
                </div>
				<?php do_action( 'cbxpetition_wpheading_wrap_after', 'settings' ); ?>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12">
				<?php do_action( 'cbxpetition_settings_form_before', 'settings' ); ?>
                <div class="postbox">
                    <div class="clear clearfix"></div>
                    <div class="inside setting-form-wrap">
                        <div class="clear clearfix"></div>
						<?php do_action( 'cbxpetition_settings_form_start', 'settings' ); ?>
						<?php
						settings_errors();

						$settings->show_navigation();
						$settings->show_forms();
						?>
						<?php do_action( 'cbxpetition_settings_form_end', 'settings' ); ?>
                        <div class="clear clearfix"></div>
                    </div>
                    <div class="clear clearfix"></div>
                </div>
				<?php do_action( 'cbxpetition_settings_form_after', 'settings' ); ?>
            </div>
        </div>
    </div>
</div>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound