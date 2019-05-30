<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'post_excerpt', '' ) ); ?>" class="frm_action_name" value="<?php echo esc_attr( $form_action->post_excerpt ); ?>" />
<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'ID', '' ) ); ?>" value="<?php echo esc_attr( $form_action->ID ); ?>" />

<div class="frm_grid_container frm_no_p_margin">
	<p class="frm6 frm_form_field">
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
	<p class="frm6 frm_form_field">
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

// Show Conditional logic indicator.
if ( ! FrmAppHelper::pro_is_installed() ) {
	?>
	<h3>
		<a href="javascript:void(0)" class="frm_show_upgrade frm_noallow" data-upgrade="<?php esc_attr_e( 'Conditional emails', 'formidable' ); ?>" data-medium="conditional-email">
			<?php esc_html_e( 'Use Conditional Logic', 'formidable' ); ?>
		</a>
	</h3>
	<?php
}

// Show Form Action Automation indicator.
if ( ! function_exists( 'load_frm_autoresponder' ) ) {
	$install_data = '';
	$class        = ' frm_noallow';
	$upgrading    = FrmAddonsController::install_link( 'autoresponder' );
	if ( isset( $upgrading['url'] ) ) {
		$install_data = json_encode( $upgrading );
		$class        = '';
	}
	?>
	<h3>
		<a href="javascript:void(0)" class="frm_show_upgrade<?php echo esc_attr( $class ); ?>" data-upgrade="<?php esc_attr_e( 'Form action automations', 'formidable' ); ?>" data-medium="action-automation" data-oneclick="<?php echo esc_attr( $install_data ); ?>">
			<?php esc_html_e( 'Setup Automation', 'formidable' ); ?>
		</a>
	</h3>
	<?php
}

// Show link to install logs.
if ( $use_logging ) {
	$upgrading = FrmAddonsController::install_link( 'logs' );
	if ( isset( $upgrading['url'] ) ) {
		?>
		<p>
			<a href="javascript:void(0)" class="frm_show_upgrade" data-upgrade="<?php esc_attr_e( 'Form action logs', 'formidable' ); ?>" data-medium="action-logs" data-oneclick="<?php echo esc_attr( json_encode( $upgrading ) ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_tooltip_solid_icon frm_svg15', array( 'aria-hidden' => 'true' ) ); ?>
				<?php esc_html_e( 'Install logging to get more information on API requests.', 'formidable' ); ?>
			</a>
		</p>
		<?php
	}
}
?>
<span class="alignright frm_action_id frm-sub-label <?php echo esc_attr( empty( $form_action->ID ) ? 'frm_hidden' : '' ); ?>">
	<?php
	/* translators: %1$s: The ID of the form action. */
	printf( esc_html__( 'Action ID: %1$s', 'formidable' ), esc_attr( $form_action->ID ) );
	?>
</span>
<div style="clear:both;"></div>
