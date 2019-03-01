<p class="howto">
	<?php esc_html_e( 'Add form actions to your form to perform tasks when an entry is created, updated, imported, and more.', 'formidable' ); ?>
</p>

<?php FrmTipsHelper::pro_tip( 'get_form_action_tip', 'p' ); ?>

<div id="frm_email_addon_menu">
	<h3><?php esc_html_e( 'Select the type of form action you would like to add', 'formidable' ); ?></h3>
	<ul class="frm_actions_list frm-show-groups">
		<?php
		$displayed_actions = array();
		foreach ( $groups as $group_name => $group ) {
			$is_single = ( ! isset( $group['actions'] ) || count( $group['actions'] ) === 1 );
			if ( isset( $action_controls[ $group_name ] ) && $is_single ) {
				$displayed_actions[] = $group_name;
				FrmFormActionsController::show_action_icon_link( $action_controls[ $group_name ], $group );
			} else {
				?>
				<li class="frm-group-action" data-group="<?php echo esc_attr( $group_name ); ?>">
					<a href="javascript:void(0)">
						<span>
							<i class="<?php echo esc_attr( $group['icon'] ); ?>"
							<?php if ( isset( $group['color'] ) ) { ?>
							style="--primary-hover:<?php echo esc_attr( $group['color'] ); ?>"
							<?php } ?>></i>
						</span>
						<?php echo esc_html( $group['name'] ); ?>
					</a>
				</li>
				<?php
			}
		}

		foreach ( $action_controls as $action_control ) {
			if ( in_array( $action_control->id_base, $displayed_actions ) ) {
				continue;
			}

			$displayed_actions[] = $action_control->id_base;
			FrmFormActionsController::show_action_icon_link( $action_control );
			unset( $actions_icon, $classes );
		}

		foreach ( $groups as $group_name => $group ) {
			if ( ! isset( $group['actions'] ) ) {
				continue;
			}

			foreach ( $group['actions'] as $action ) {
				if ( ! in_array( $action, $displayed_actions ) ) {
					?>
					<li class="frm-group-<?php echo esc_attr( $group_name ); ?>">
						<a href="javascript:void(0)" class="frm-single-action frm_show_upgrade">
							<span>
								<i class="dashicons dashicons-plus"
								<?php if ( isset( $group['color'] ) ) { ?>
								style="--primary-hover:<?php echo esc_attr( $group['color'] ); ?>"
								<?php } ?>></i>
							</span>
							<?php echo esc_html( $action ); ?>
						</a>
					</li>
					<?php
				}
			}
		}
		?>
	</ul>
	<div class="clear"></div>
	<a href="#" id="frm-show-groups">
		<?php esc_html_e( 'Cancel', 'formidable' ); ?>
	</a>
	<div class="clear"></div>
</div>
<div class="frm_no_actions">
	<div class="inner_actions">
		<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/sketch_arrow1.png' ); ?>" alt=""/>
		<div class="clear"></div>
		<?php esc_html_e( 'Click an action to add it to this form', 'formidable' ); ?>
	</div>
</div>
<?php FrmFormActionsController::list_actions( $form, $values ); ?>
