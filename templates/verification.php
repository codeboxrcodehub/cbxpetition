<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/*if(wp_is_block_theme()){
	//$header_content = '<!-- wp:template-part {"slug":"header"} /-->';
	//echo do_blocks( $header_content );
	//echo block_template_part( 'header' );
	//echo get_template_part( 'parts/header', 'cbxpetition' );
	//block_template_part('header');
	// Include wp_head() for styles and scripts
	wp_head();
}
else{*/
	get_header( 'cbxpetition' );
//}
?>
    <div class="cbx-chota" id="cbxpetition_verification_public">
        <div class="container" id="cbxpetition_verification_wrapper">
            <div class="cbxpetition_verification_inside">
                <h2 class="cbxpetition_section_heading cbxpetition_section_heading_verification"><?php esc_html_e('Guest signature verification', 'cbxpetition'); ?></h2>
				<?php
				echo $confirmation_message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
            </div>
        </div>
    </div>

<?php
/*if(wp_is_block_theme()){
    //$footer_content = '<!-- wp:template-part {"slug":"footer"} /-->';
    //echo do_blocks($footer_content);

	//echo block_template_part( 'footer' );
	//echo get_template_part( 'parts/footer', 'cbxpetition' );
	//block_template_part('footer');
	// Include wp_head() for styles and scripts
	wp_footer();
}
else{*/
	get_footer( 'cbxpetition' );
//}

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound