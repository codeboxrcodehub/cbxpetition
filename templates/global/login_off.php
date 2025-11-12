<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

//echo '<div class="cbx-chota"><div class="container"><div class="row"><div class="col-12">';
/* translators: %1$s: Login Link */
echo '<div class="guest_login_url_wrap"><p class="mb-0">'.esc_html__('Sorry, log in feature is not available from this area.', 'cbxpetition').'</p></div>';
//echo '</div></div></div></div>';

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound