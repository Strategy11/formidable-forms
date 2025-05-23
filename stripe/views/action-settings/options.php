<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$show_layout_setting         = in_array( 'stripe', (array) $form_action->post_content['gateway'], true );
$layout_setting_wrapper_atts = array( 'class' => 'frm_grid_container show_stripe' );
if ( ! $show_layout_setting ) {
	$layout_setting_wrapper_atts['class'] .= ' frm_hidden';
}
?>
<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'plan_id' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['plan_id'] ); ?>" />
<div <?php FrmAppHelper::array_to_html_params( $layout_setting_wrapper_atts, true ); ?>>
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
