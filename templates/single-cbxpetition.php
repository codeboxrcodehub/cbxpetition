<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

get_header( 'cbxpetition' );
?>
<?php
do_action( 'cbxpetition_before_main_content', 'single-cbxpetition' );
do_action( 'cbxpetition_single_before_main_content', 'single-cbxpetition' );
?>
    <div id="cbxpetition_main_content" class="cbxpetition-single-content">
        <div class="cbx-chota">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <?php do_action('cbxpetition_single_content_before_title', 'single-cbxpetition'); ?>
                        <div class="cbxpetition_title_header cbxpetition_title_header_single">
	                        <?php do_action('cbxpetition_single_title_header_before', 'single-cbxpetition'); ?>
	                        <?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            <h1 class="cbxpetition-title cbxpetition-title-single"><?php echo get_the_title(); ?></h1>
	                        <?php do_action('cbxpetition_single_title_header_after', 'single-cbxpetition'); ?>
                        </div>


                        <?php do_action('cbxpetition_single_content_after_title', 'single-cbxpetition'); ?>
		                <?php
		                while ( have_posts() ) :
			                the_post();

			                do_action( 'cbxpetition_single_content_before_details', 'single-cbxpetition' );

			                the_content();

			                do_action( 'cbxpetition_single_content_after_details', 'single-cbxpetition' );

		                endwhile;
		                ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
do_action( 'cbxpetition_single_after_main_content', 'single-cbxpetition' );
do_action( 'cbxpetition_after_main_content', 'single-cbxpetition' );

do_action( 'cbxpetition_sidebar_single', 'single-cbxpetition' );
?>

<?php
get_footer( 'cbxpetition' );
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound