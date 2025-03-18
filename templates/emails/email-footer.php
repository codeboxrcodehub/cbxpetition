<?php
/**
 * Email Footer
 *
 * This template can be overridden by copying it to yourtheme/cbxpetition/emails/email-footer.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 7.4.0
 */

defined( 'ABSPATH' ) || exit;
?>
</div>
</td>
</tr>
</table>
<!-- End Content -->
</td>
</tr>
</table>
<!-- End Body -->
</td>
</tr>
</table>
</td>
</tr>
<tr>
    <td align="center" valign="top">
        <!-- Footer -->
        <table border="0" cellpadding="10" cellspacing="0" width="100%" id="template_footer">
            <tr>
                <td valign="top">
                    <table border="0" cellpadding="10" cellspacing="0" width="100%">
                        <tr>
                            <td colspan="2" valign="middle" id="credit">
								<?php

								$footer_text = isset( $template_settings['footertext'] ) ? $template_settings['footertext'] : '';

								echo wp_kses_post(
									wpautop(
										wptexturize(
										/**
										 * Provides control over the email footer text used for most order emails.
										 *
										 * @param  string  $email_footer_text
										 *
										 * @since 4.0.0
										 *
										 */
											apply_filters( 'cbxpetition_email_footer_text', $footer_text )
										)
									)
								);
								?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <!-- End Footer -->
    </td>
</tr>
</table>
</div>
</td>
<td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
</tr>
</table>
</body>
</html>
