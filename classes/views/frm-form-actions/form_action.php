<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$a           = FrmAppHelper::simple_get( 't', 'sanitize_title', 'advanced_settings' );
$form_action = apply_filters( 'frm_form_action_settings', $form_action, $form_action->post_excerpt );
$form_action = apply_filters( 'frm_form_' . $form_action->post_excerpt . '_action_settings', $form_action );

$data_attrs = array(
	'data-actionkey' => $action_key,
);

if ( FrmOnSubmitAction::$slug === $form_action->post_excerpt ) {
	$data_attrs['data-on-submit-type'] = FrmOnSubmitHelper::get_action_type( $form_action );
}
?>
<div
	id="frm_form_action_<?php echo esc_attr( $action_key ); ?>"
	class="widget frm_form_action_settings frm_single_<?php echo esc_attr( $form_action->post_excerpt ); ?>_settings <?php echo esc_attr( $form_action->post_status === 'publish' ? '' : 'frm_disabled_action' ); ?>"
	<?php FrmAppHelper::array_to_html_params( $data_attrs, true ); ?>
>
	<div class="widget-top frm-h-stack-xs">
		<div class="widget-title frm-flex-full">
			<h4 class="frm-h-stack-xs frm-text-md frm-p-sm">
				<span class="frm-border-icon frm-border-icon--small"><?php FrmAppHelper::icon_by_class( $action_control->action_options['classes'], FrmFormActionsController::get_action_icon_atts( $action_control ) ); ?></span>
				<span><?php echo esc_html( $form_action->post_title ); ?></span>
			</h4>
		</div>

		<div class="frm-ml-auto frm-h-stack-sm frm-p-sm">
			<span class="frm_email_icons frm-h-stack-sm">
				<?php if ( $action_control->action_options['limit'] > 2 ) { ?>
					<a href="javascript:void(0)" class="frm_duplicate_form_action" title="<?php esc_attr_e( 'Duplicate', 'formidable' ); ?>">
						<?php FrmAppHelper::icon_by_class( 'frmfont frm-copy-icon frm_svg24' ); ?>
					</a>
				<?php } ?>

				<a href="javascript:void(0)" data-removeid="frm_form_action_<?php echo esc_attr( $action_key ); ?>" class="frm_remove_form_action" data-frmverify="<?php esc_attr_e( 'Delete this form action?', 'formidable' ); ?>" data-frmverify-btn="frm-button-red" title="<?php esc_attr_e( 'Delete', 'formidable' ); ?>">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_delete_icon frm_svg24' ); ?>
				</a>

				<?php
				FrmHtmlHelper::toggle(
					$action_control->get_field_id( 'post_status', '' ),
					$action_control->get_field_name( 'post_status', '' ),
					array(
						'checked'     => $form_action->post_status === 'publish',
						'on_label'    => 'publish',
						'off_label'   => 'OFF',
						'show_labels' => false,
						'echo'        => true,
						'div_class'   => 'frm-ml-xs',
					)
				);
				?>
			</span>

			<div class="widget-title-action">
				<button type="button" class="widget-action frm-flex frm-p-2xs-force hide-if-no-js" aria-expanded="false">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown8_icon frm_svg14' ); ?>
				</button>
			</div>
		</div>
	</div>

	<div class="widget-inside">
		<?php
		// Load settings only if just added or open, otherwise include hidden settings to prevent losing the action on update.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			include __DIR__ . '/_action_inside.php';
		} else {
		?>
		<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'post_excerpt', '' ) ); ?>" class="frm_action_name" value="<?php echo esc_attr( $form_action->post_excerpt ); ?>" />
		<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'ID', '' ) ); ?>" value="<?php echo esc_attr( $form_action->ID ); ?>" />
		<?php } ?>
	</div>
</div>
