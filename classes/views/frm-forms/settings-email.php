<?php FrmTipsHelper::pro_tip( 'get_form_action_tip', 'p' ); ?>

<div id="frm_email_addon_menu" class="manage-menus">
	<h3><?php esc_html_e( 'Add New Action', 'formidable' ); ?></h3>
	<ul class="frm_actions_list">
		<?php

		// For each add-on, add an li, class, and javascript function. If active, add an additional class.
		$included = false;
		foreach ( $action_controls as $action_control ) {
			$classes = ( isset( $action_control->action_options['active'] ) && $action_control->action_options['active'] ) ? 'frm_active_action ' : 'frm_inactive_action ';
			$classes .= $action_control->action_options['classes'];

			if ( ! $included && strpos( $classes, 'frm_show_upgrade' ) ) {
				$included = true;
				FrmAppController::include_upgrade_overlay();
			}

			/* translators: %s: Name of form action */
			$upgrade_label = sprintf( esc_html__( '%s form actions', 'formidable' ), $action_control->action_options['tooltip'] );
			?>
			<li>
				<a href="javascript:void(0)" class="frm_<?php echo esc_attr( $action_control->id_base ); ?>_action frm_bstooltip <?php echo esc_attr( $classes ); ?>" title="<?php echo esc_attr( $action_control->action_options['tooltip'] ); ?>" data-limit="<?php echo esc_attr( isset( $action_control->action_options['limit'] ) ? $action_control->action_options['limit'] : '99' ); ?>" data-actiontype="<?php echo esc_attr( $action_control->id_base ); ?>" data-upgrade="<?php echo esc_attr( $upgrade_label ); ?>" data-medium="settings-<?php echo esc_attr( $action_control->id_base ); ?>"></a>
			</li>
			<?php
			unset( $actions_icon, $classes );
		}
		?>
	</ul>
</div>
<div class="frm_no_actions">
	<div class="inner_actions">
		<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/sketch_arrow1.png' ); ?>" alt=""/>
		<div class="clear"></div>
		<?php esc_html_e( 'Click an action to add it to this form', 'formidable' ); ?>
	</div>
</div>
<?php FrmFormActionsController::list_actions( $form, $values ); ?>
