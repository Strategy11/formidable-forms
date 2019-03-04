<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'post_excerpt', '' ) ); ?>" class="frm_action_name" value="<?php echo esc_attr( $form_action->post_excerpt ); ?>" />
<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'ID', '' ) ); ?>" value="<?php echo esc_attr( $form_action->ID ); ?>" />

<div class="frm_grid_container frm_no_p_margin">
	<p class="frm_half">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'action_post_title' ) ); ?>" <?php FrmAppHelper::maybe_add_tooltip( 'action_title' ); ?>>
			<?php esc_html_e( 'Action Name', 'formidable' ); ?>
		</label>
		<input type="text" name="<?php echo esc_attr( $action_control->get_field_name( 'post_title', '' ) ); ?>" value="<?php echo esc_attr( $form_action->post_title ); ?>" class="large-text <?php FrmAppHelper::maybe_add_tooltip( 'action_title', 'open' ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'action_post_title' ) ); ?>" />
	</p>
<?php

if ( ! isset( $action_control->action_options['event'] ) ) {
	$events = 'create';
}

if ( ! is_array( $action_control->action_options['event'] ) ) {
	$action_control->action_options['event'] = explode( ',', $action_control->action_options['event'] );
}

if ( count( $action_control->action_options['event'] ) == 1 || $action_control->action_options['force_event'] ) {
	foreach ( $action_control->action_options['event'] as $e ) {
		?>
		<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'event' ) ); ?>[]" value="<?php echo esc_attr( $e ); ?>" />
		<?php
	}
} else {
	?>
	<p class="frm_half">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'event' ) ); ?>">
			<?php esc_html_e( 'Trigger this action when', 'formidable' ); ?>
		</label>
		<select name="<?php echo esc_attr( $action_control->get_field_name( 'event' ) ); ?>[]" multiple="multiple" class="frm_multiselect" id="<?php echo esc_attr( $action_control->get_field_id( 'event' ) ); ?>">
	<?php

	$event_labels = FrmFormAction::trigger_labels();
	foreach ( $action_control->action_options['event'] as $event ) {
		?>
		<option value="<?php echo esc_attr( $event ); ?>" <?php echo in_array( $event, (array) $form_action->post_content['event'] ) ? ' selected="selected"' : ''; ?> ><?php echo esc_html( isset( $event_labels[ $event ] ) ? $event_labels[ $event ] : $event ); ?></option>
<?php } ?>
		</select>
	</p>
	<?php
}

?>
</div>
<?php

$action_control->form( $form_action, compact( 'form', 'action_key', 'values' ) );

$pass_args = array(
	'form'       => $form,
	'action_control' => $action_control,
	'action_key' => $action_key,
	'values'     => $values,
);
do_action( 'frm_additional_action_settings', $form_action, $pass_args );

?>
<span class="alignright frm_action_id <?php echo esc_attr( empty( $form_action->ID ) ? 'frm_hidden' : '' ); ?>">
	<?php
	/* translators: %1$s: The ID of the form action. */
	printf( esc_html__( 'Action ID: %1$s', 'formidable' ), esc_attr( $form_action->ID ) );
	?>
</span>
<div style="clear:both;"></div>
