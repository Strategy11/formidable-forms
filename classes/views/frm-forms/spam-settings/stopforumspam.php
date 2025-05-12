<?php
/**
 * Settings for stopforumspam check
 *
 * @since 6.21
 * @package Formidable
 *
 * @var array $values Form values.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm6 frm_form_field frm_first">
	<label>
		<input type="checkbox" name="options[stopforumspam]" <?php checked( $values['stopforumspam'], 1 ); ?> value="1" />
		<?php esc_html_e( 'Use stopforumspam API to check entries for spam', 'formidable' ); ?>
		<?php FrmAppHelper::tooltip_icon( __( 'Sends the IP address and any email addresses to the stopforumspam API.', 'formidable' ), array( 'data-container' => 'body' ) ); ?>
	</label>
</p>
