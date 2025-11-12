<?php
if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

if ( ! is_user_logged_in()):
    if (is_singular()) {
        $login_url    = wp_login_url(get_permalink());
        $redirect_url = get_permalink();
    } else {
        global $wp;
        //$login_url =  wp_login_url( home_url( $wp->request ) );
        $login_url    = wp_login_url(home_url(add_query_arg(array(), $wp->request)));
        $redirect_url = home_url(add_query_arg(array(), $wp->request));
    }

    $guest_html = '<div class="cbx-guest-wrap cbxpetition-guest-wrap">';

    $guest_html       .= '<p class="cbx-title-login cbxpetition-title-login">'.wp_kses(__('Do you have account, <a role="button" class="guest-login-trigger cbxpetition-guest-login-trigger" href="#">please login</a>', 'cbxpetition'), ['a' => ['href' => [], 'role' => [], 'class' => [], 'style' => []]]).'</p>';
    $guest_login_html = wp_login_form(array(
            'redirect' => $redirect_url,
            'echo'     => false
    ));


    $guest_login_html = apply_filters('cbxpetition_login_html', $guest_login_html, $login_url, $redirect_url);


    $guest_register_html = '';
    $guest_show_register = absint($settings->get_field('guest_show_register', 'cbxpetition_general', 1));
    if ($guest_show_register && get_option('users_can_register')) {
        $register_url = add_query_arg('redirect_to', urlencode($redirect_url), wp_registration_url());
        /* translators: %s: Registration Link */
        $guest_register_html .= '<p class="cbx-guest-register cbxpetition-guest-register">'.sprintf(wp_kses(__('No account yet? <a href="%s">Register</a>', 'cbxpetition'), ['a' => ['href' => []]]), $register_url).'</p>';


        $guest_register_html = apply_filters('cbxpetition_register_html', $guest_register_html, $redirect_url);
    }//end show register

    $guest_html .= '<div class="cbx-guest-login-wrap cbxpetition-guest-login-wrap">'.$guest_login_html.$guest_register_html.'</div>';
    $guest_html .= '</div>';

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo '<div class="petition_login_regi_box">'.$guest_html.'</div>'; /* translators: %1$s: Resume text , %2$s: Resume number  */
endif;
?>
<script type="text/javascript">
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('guest-login-trigger')) {
            e.preventDefault();

            var parent = e.target.closest('.cbx-guest-wrap');
            if (parent) {
                var loginWrap = parent.querySelector('.cbx-guest-login-wrap');
                if (loginWrap) {
                    loginWrap.classList.toggle('cbx-guest-login-wrap-show');
                }
            }
        }
    });
</script>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound