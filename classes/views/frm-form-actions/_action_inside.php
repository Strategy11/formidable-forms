<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

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
}//end if

?>
</div>
<?php
$pass_args = array(
	'form'           => $form,
	'action_control' => $action_control,
	'action_key'     => $action_key,
	'values'         => $values,
);

/**
 * Fires before form action settings.
 *
 * @since 6.10
 *
 * @param object $form_action Form action object.
 * @param array  $pass_args   Pass args.
 */
do_action( 'frm_before_action_settings', $form_action, $pass_args );

$action_control->form( $form_action, compact( 'form', 'action_key', 'values' ) );

do_action( 'frm_additional_action_settings', $form_action, $pass_args );

// Show Conditional logic indicator.
if ( ! FrmAppHelper::pro_is_installed() ) {
	if ( 'email' === $form_action->post_excerpt ) {
		?>
		<h3>
			<a href="javascript:void(0)" class="frm_show_upgrade frm_noallow" data-upgrade="<?php esc_attr_e( 'Email attachments', 'formidable' ); ?>" data-message="<?php esc_attr_e( 'Email a CSV or a PDF of each new entry, or attach a file of your choice.', 'formidable' ); ?>" data-medium="email-attachment">
				<?php esc_html_e( 'Attachment', 'formidable' ); ?>
			</a>
		</h3>
		<?php
	}

	$action_control->render_conditional_logic_call_to_action();
}

// Show Form Action Automation indicator.
if ( ! function_exists( 'load_frm_autoresponder' ) && in_array( $form_action->post_excerpt, apply_filters( 'frm_autoresponder_allowed_actions', array( 'email', 'twilio', 'api', 'register' ) ), true ) ) {
	$upgrading = FrmAddonsController::install_link( 'autoresponder' );
	$params    = array(
		'href'         => 'javascript:void(0)',
		'class'        => 'frm_show_upgrade',
		'data-upgrade' => __( 'Form action automations', 'formidable' ),
		'data-medium'  => 'action-automation',
	);

	if ( isset( $upgrading['url'] ) ) {
		$params['data-oneclick'] = json_encode( $upgrading );
	} else {
		$params['class']        .= ' frm_noallow';
		$params['data-requires'] = FrmFormsHelper::get_plan_required( $upgrading );
	}
	?>
	<h3>
		<a <?php FrmAppHelper::array_to_html_params( $params, true ); ?>>
			<?php esc_html_e( 'Setup Automation', 'formidable' ); ?>
		</a>
	</h3>
	<?php
	unset( $params );
}//end if

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
