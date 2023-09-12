<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm_grid_container">
	<label class="frm4 frm_form_field" for="frm_strp_processing_message">
		<?php esc_html_e( 'Stripe Processing Message', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The text used to notify users that a Stripe payment is still processing and will not be finalized for several days.', 'formidable' ); ?>"></span>
	</label>
	<input type="text" id="frm_strp_processing_message" name="frm_strp_processing_message" class="frm8 frm_form_field" value="<?php echo esc_attr( $stripe_settings->processing_message ); ?>" />
</p>
