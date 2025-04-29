<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'plan_id' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['plan_id'] ); ?>" />
<div class="frm_grid_container">
	<p class="frm6">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'layout' ) ); ?>">
			<?php esc_html_e( 'Layout', 'formidable' ); ?>
		</label>
		<select name="<?php echo esc_attr( $action_control->get_field_name( 'layout' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'layout' ) ); ?>">
			<option value="">
				<?php esc_html_e( 'Tabs', 'formidable' ); ?>
			</option>
			<option value="accordion"
			<?php
			if ( isset( $form_action->post_content['layout'] ) ) {
				selected( $form_action->post_content['layout'], 'accordion' );
			}
			?>
			>
				<?php esc_html_e( 'Accordion', 'formidable' ); ?>
			</option>
		</select>
	</p>
</div>
