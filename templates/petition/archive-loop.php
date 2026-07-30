<?php
if ( ! defined('WPINC')) {
    die;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$bg_images = [
        'contour_line.svg',
        'curve_line.svg',
        'hexagon.svg',
        'simple_shiny.svg',
        'sprinkle.svg',
        'wave_line.svg',
];

$petition_id        = get_the_ID();
$cbxpetition_banner = cbxpetition_petitionBannerImage($petition_id);
$header_extra_class = ($cbxpetition_banner != '') ? 'cbxpetition_loop_item_header_banner' : '';

$header_extra_class = '';
$header_style       = '';
if ($cbxpetition_banner != '') {
    $header_extra_class = 'cbxpetition_loop_item_header_banner';
    $header_style       = 'background-image:url('.$cbxpetition_banner.') ;';
}
else{
    $bg_image = $bg_images[$petition_id % count($bg_images)];
    $header_extra_class = 'cbxpetition_loop_item_header_banner';
    $header_style       = 'background-image:url('.CBXPETITION_ROOT_URL.'assets/images/bg-svg/'.$bg_image.') ;';
}

do_action('cbxpetition_archive_loop_item_before', $petition_id);
?>
    <div class="cbxpetition_loop_item col-4">
        <?php
        do_action('cbxpetition_archive_loop_item_start', $petition_id);
        ?>
        <div class="cbxpetition_loop_item_inside">
            <?php
            do_action('cbxpetition_archive_loop_item_inside_start', $petition_id);
            ?>
            <div class="cbxpetition_loop_item_header <?php echo esc_attr($header_extra_class); ?>"
                 style="<?php echo esc_attr($header_style); ?>">
                <?php
                do_action('cbxpetition_archive_loop_item_header_start', $petition_id);
                ?>

                <?php
                do_action('cbxpetition_archive_loop_item_header_end', $petition_id);
                ?>
            </div>
            <?php
            do_action('cbxpetition_archive_loop_item_content_before', $petition_id);
            ?>
            <div class="cbxpetition_loop_item_content">
                <div class="cbxpetition_loop_item_heading">
                    <?php
                    do_action('cbxpetition_archive_loop_item_heading_start', $petition_id);

                    do_action('cbxpetition_archive_loop_item_heading_end', $petition_id);
                    ?>
                </div>
                <?php
                do_action('cbxpetition_archive_loop_item_content_start', $petition_id);
                ?>
                <div class="cbxpetition_loop_item_content_inside">
                    <?php
                    //do_action( 'cbxpetition_archive_loop_item_content_inside_start' );
                    do_action('cbxpetition_archive_loop_item_content_inside', $petition_id);
                    ?>
                    <?php //echo do_shortcode( '[cbxpetition_stat style=2]' ); ?>
                    <?php
                    //do_action( 'cbxpetition_archive_loop_item_content_inside_end' );
                    ?>
                </div>
                <?php
                do_action('cbxpetition_archive_loop_item_content_end', $petition_id);
                ?>
            </div>
            <?php
            do_action('cbxpetition_archive_loop_item_inside_end', $petition_id);
            ?>
        </div>
        <?php
        do_action('cbxpetition_archive_loop_item_end', $petition_id);
        ?>
    </div>
<?php
do_action('cbxpetition_archive_loop_item_after', $petition_id);
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound