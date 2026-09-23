<?php
/**
 * Show a disabled preview of the Pro auto-delete entries setting.
 *
 * @since x.x
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$wrapper_params = FrmSettingsUpsellHelper::add_upgrade_modal_atts(
	array( 'class' => 'frm8 frm_form_field frm_noallow' ),
	'gdpr-auto-delete',
	__( 'Entry auto-delete settings', 'formidable' ),
	'gdpr-settings'
);

$wrapper_params['data-message'] = __( 'Keep only the data you need. Old entries are removed automatically after the number of days you choose.', 'formidable' );
?>
<p <?php FrmAppHelper::array_to_html_params( $wrapper_params, true ); ?>>
	<label for="frm_auto_delete_entries" class="frm_inline_block">
		<input type="checkbox" id="frm_auto_delete_entries" disabled="disabled" />
		<?php esc_html_e( 'Automatically delete entries after', 'formidable' ); ?>
	</label>
	<input type="number" class="frm-auto-delete-days" value="90" disabled="disabled" aria-label="<?php esc_attr_e( 'Number of days', 'formidable' ); ?>" />
	<?php esc_html_e( 'days', 'formidable' ); ?>
</p>
