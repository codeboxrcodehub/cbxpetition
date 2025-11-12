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
do_action( 'cbxpetition_before_main_content', 'taxonomy-cbxpetition' );
do_action( 'cbxpetition_taxonomy_before_main_content', 'taxonomy-cbxpetition' );
?>
    <div id="cbxpetition_main_content" class="cbxpetition-taxonomy-content">
        <div class="cbx-chota">
            <div class="container">
                <div class="col-12">
                    <div class="cbxpetition_title_header cbxpetition_title_header_tax">
	                    <?php do_action('cbxpetition_tax_title_header_before', 'taxonomy-cbxpetition'); ?>
                        <?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <h1 class="cbxpetition-title cbxpetition-title-tax text-center"><?php echo esc_html__( 'Petition', 'cbxpetition' ) . ' ' . get_the_archive_title(); ?></h1>
	                    <?php do_action('cbxpetition_tax_title_header_after', 'taxonomy-cbxpetition'); ?>
                    </div>
                    <div id="cbxpetition_archive_items" class="cbxpetition_loop_items cbxpetition_loop_items-tax row">
						<?php
						while ( have_posts() ) :
							the_post();

							//the_content();
							cbxpetition_get_template('petition/archive-loop.php');
							?>
						<?php
						endwhile;
						?>
                    </div>
	                <?php
	                global $wp_query;
	                if ( $wp_query->max_num_pages > 1 ) :
		                $prev_arrow = is_rtl() ? '&rarr;' : '&larr;';
		                $next_arrow = is_rtl() ? '&larr;' : '&rarr;';
		                ?>
                        <div id="cbxpetition_archive_pagination">
                            <nav class="pagination">
                                <div class="nav-previous"><?php
			                        /* translators: %s: HTML entity for arrow character. */
			                        previous_posts_link( sprintf( esc_html__( '%s Previous', 'cbxpetition' ), sprintf( '<span class="meta-nav">%s</span>', $prev_arrow ) ) );
			                        ?></div>
                                <div class="nav-next"><?php
			                        /* translators: %s: HTML entity for arrow character. */
			                        next_posts_link( sprintf( esc_html__( 'Next %s', 'cbxpetition' ), sprintf( '<span class="meta-nav">%s</span>', $next_arrow ) ) );
			                        ?></div>
                            </nav>
                        </div>
	                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php
do_action( 'cbxpetition_taxonomy_after_main_content', 'taxonomy-cbxpetition' );
do_action( 'cbxpetition_after_main_content', 'taxonomy-cbxpetition' );

do_action( 'cbxpetition_sidebar_taxonomy', 'taxonomy-cbxpetition' );
?>

<?php
get_footer( 'cbxpetition' );
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound