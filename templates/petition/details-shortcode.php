<?php
do_action( 'cbxpetition_before_main_content', 'shortcode' );
do_action( 'cbxpetition_single_before_main_content', 'shortcode' );
?>
    <div id="cbxpetition_main_content" class="cbxpetition-single-content">
        <div class="cbx-chota">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <?php do_action('cbxpetition_single_content_before_title', 'shortcode'); ?>
                        <div class="cbxpetition_title_header cbxpetition_title_header_single cbxpetition_title_header_shortcode">
	                        <?php do_action('cbxpetition_single_title_header_before', 'shortcode'); ?>
                            <?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            <h2 class="h1 cbxpetition-title cbxpetition-title-single mb-20"><?php echo get_the_title($petition_id); ?></h2>
	                        <?php do_action('cbxpetition_single_title_header_after', 'shortcode'); ?>
                        </div>
                        <?php do_action('cbxpetition_single_content_after_title', 'shortcode'); ?>
		                <?php
			                do_action( 'cbxpetition_single_content_before_details', 'shortcode' );

                            echo $output; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			                do_action( 'cbxpetition_single_content_after_details', 'shortcode' );
		                ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
do_action( 'cbxpetition_single_after_main_content', 'shortcode'  );
do_action( 'cbxpetition_after_main_content', 'shortcode'  );