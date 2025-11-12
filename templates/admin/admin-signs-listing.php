<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Cbx\Petition\Helpers\PetitionHelper;
?>


<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$sign_statuses = PetitionHelper::getPetitionSignStates();
$plus_svg  = cbxpetition_esc_svg(cbxpetition_load_svg( 'icon_plus' ));

$signatures = new \Cbx\Petition\PetitionSignListTable();//Fetch, prepare, sort, and filter CBXPetitionSign_List_Table data
$signatures->prepare_items();

$petition_id = isset( $_GET['petition_id'] ) ? absint( $_GET['petition_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<div class="wrap cbx-chota cbxpetition-page-wrapper cbxpetition-singature-wrapper" id="cbxpetition-singature">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2></h2>
				<?php do_action( 'cbxpetition_wpheading_wrap_before', 'signature-listing' ); ?>
                <div class="wp-heading-wrap">
                    <div class="wp-heading-wrap-left pull-left">
						<?php do_action( 'cbxpetition_wpheading_wrap_left_before', 'signature-listing' ); ?>
                        <h1 class="wp-heading-inline wp-heading-inline-cbxpetition">
							<?php
							esc_html_e( 'Petition: Signatures', 'cbxpetition' );
							?>

                        </h1>
						<?php do_action( 'cbxpetition_wpheading_wrap_left_after', 'signature-listing' ); ?>
                    </div>
                    <div class="wp-heading-wrap-right  pull-right">
						<?php do_action( 'cbxpetition_wpheading_wrap_right_before', 'signature-listing' ); ?>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-settings' ) ); ?>"
                           class="button outline primary pull-right"><?php esc_html_e( 'Global Settings', 'cbxpetition' ); ?></a>
						<?php do_action( 'cbxpetition_wpheading_wrap_right_after', 'signature-listing' ); ?>
                    </div>
                </div>
				<?php do_action( 'cbxpetition_wpheading_wrap_after', 'signature-listing' ); ?>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12">
				<?php do_action( 'cbxpetition_signature_listing_before_postbox', 'signature-listing' ); ?>
                <div class="postbox">
                    <div class="inside">
						<?php do_action( 'cbxpetition_signature_listing_before', 'signature-listing' ); ?>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="cbx-sub-heading-wrap" id="dashlisting_toolbar">
                                        <div class="cbx-sub-heading-l">
                                            <h2 class="cbx-sub-heading cbx-sub-heading-petition">
	                                            <?php
	                                            if ( $petition_id > 0 ) {
		                                            /* translators: %s: Petition Title  */
		                                            echo sprintf( wp_kses( __( 'Signatures from petition "<strong>%s</strong>"', 'cbxpetition' ), [ 'strong' => [] ] ), esc_attr( get_the_title( $petition_id ) ) );

	                                            } else {
		                                            echo esc_html__( 'All Signatures', 'cbxpetition' );
	                                            }
	                                            ?>
                                            </h2>
                                            <?php
                                                if($petition_id > 0){
	                                                echo '<a class="button outline small ml-10" href="' . esc_url( get_edit_post_link( $petition_id ) ) . '" target="_blank">' . esc_html__( 'Edit', 'cbxpetition' ) . '</a><a class="button outline small" href="' . esc_url( get_permalink( $petition_id ) ) . '" target="_blank">' . esc_html__( 'View', 'cbxpetition' ) . '</a>';
	                                                echo '<a class="button outline primary" href="' . esc_url( admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-signatures' ) ) . '">' . esc_html__( 'Back to all signatures', 'cbxpetition' ) . '</a>';
                                                    do_action( 'cbxpetition_signature_subheading_after', 'signature-listing' );
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php do_action( 'cbxpetition_send_mail_to_recipients_box', 'signature-listing' ); ?>
                        
                        <form id="cbxpetition_signs" method="post" class="cbx-wplisttable">

							<?php do_action( 'cbxpetition_signature_listing_form_start', 'signature-listing' ); ?>
							<?php $signatures->views(); ?>
                            <input type="hidden" name="page"
                                   value="<?php echo isset( $_REQUEST['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended  ?>"/>
                            <input type="hidden" name="post_type" value="cbxpetition"/>
                            <div id="cbxpetition_signs_filters_wrap">
                                <div class="cbxpetition_signs_filters pull-left">
									<?php
									$sign_status = isset( $_REQUEST['sign_status'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['sign_status'] ) ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

									?>
                                    <select name="sign_status" id="petition_sign_status">
                                        <option value="all" <?php selected( $sign_status, 'all' ); ?>><?php esc_html_e( 'Status(All)', 'cbxpetition' ); ?></option>
										<?php
										foreach ( $sign_statuses as $sign_status_key => $sign_status_label ) {
											echo '<option ' . selected( $sign_status, $sign_status_key ) . ' value="' . esc_attr( $sign_status_key ) . '">' . esc_html( $sign_status_label ) . '</option>';
										}
										?>
                                    </select>
                                </div>
								<?php $signatures->search_box( esc_html__( 'Search', 'cbxpetition' ), 'petitionsignsearch' ); ?>
                                <div class="clearfix clear"></div>
                            </div>
							<?php $signatures->display() ?>
							<?php do_action( 'cbxpetition_signature_listing_form_end', 'signature-listing' ); ?>
                        </form>
						<?php do_action( 'cbxpetition_signature_listing_after', 'signature-listing' ); ?>
                    </div>
                </div>
				<?php do_action( 'cbxpetition_signature_listing_after_postbox', 'signature-listing' ); ?>
            </div>
        </div>
    </div>
</div>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound