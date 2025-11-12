<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

do_action('cbxpetition_archive_loop_item_before');
?>
<div class="cbxpetition_loop_item col-4">
	<?php
	do_action('cbxpetition_archive_loop_item_start');
	?>
    <div class="cbxpetition_loop_item_inside">
	    <?php
	    do_action('cbxpetition_archive_loop_item_inside_start');
	    ?>
        <div class="cbxpetition_loop_item_heading">
	        <?php
	        do_action('cbxpetition_archive_loop_item_heading_start');
	        ?>
	        <?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <h2 class="cbxpetition_loop_item-title cbxpetition_loop_item-title-archive"><a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php echo get_the_title() ?></a></h2>
	        <?php
	        do_action('cbxpetition_archive_loop_item_heading_end');
	        ?>
        </div>
	    <?php
	    do_action('cbxpetition_archive_loop_item_content_before');
	    ?>
        <div class="cbxpetition_loop_item_content">
	        <?php
	        do_action('cbxpetition_archive_loop_item_content_start');
	        ?>
            <div class="cbxpetition_loop_item_content_inside">
	            <?php
	            do_action('cbxpetition_archive_loop_item_content_inside_start');
	            ?>
                <?php echo do_shortcode('[cbxpetition_stat]'); ?>
                <?php
	            do_action('cbxpetition_archive_loop_item_content_inside_end');
                ?>
            </div>
	        <?php
	        do_action('cbxpetition_archive_loop_item_content_end');
	        ?>
        </div>
	    <?php
	    do_action('cbxpetition_archive_loop_item_inside_end');
	    ?>
    </div>
	<?php
	do_action('cbxpetition_archive_loop_item_end');
	?>
</div>
<?php
do_action('cbxpetition_archive_loop_item_after');


// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound