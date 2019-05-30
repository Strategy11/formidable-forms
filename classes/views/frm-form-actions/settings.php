<p class="howto">
	<?php esc_html_e( 'Add form actions to your form to perform tasks when an entry is created, updated, imported, and more.', 'formidable' ); ?>
</p>
				
<div id="frm_email_addon_menu" class="frm-limited-actions">
	<?php
	FrmAppHelper::show_search_box(
		array(
			'input_id'    => 'actions',
			'placeholder' => __( 'Search Form Actions', 'formidable' ),
			'tosearch'    => 'frm-action',
		)
	);
	?>
	<h3 class="frm-no-border">
		<?php esc_html_e( 'Form Actions', 'formidable' ); ?>
		<span class="frm-sub-label">
			<?php esc_html_e( '(click an action to add it to your form)', 'formidable' ); ?>
		</span>
	</h3>
	<?php
	$displayed_actions = array();
	foreach ( $groups as $group_name => $group ) {
		if ( ! empty( $group['name'] ) ) {
			?>
			<h3 class="frm-group-heading"><?php echo esc_html( $group['name'] ); ?></h3>
			<?php
		}

		if ( ! isset( $group['actions'] ) ) {
			$group['actions'] = array();
		}
		?>
		<ul class="frm_actions_list">
			<?php
			foreach ( $action_controls as $action_control ) {
				if ( in_array( $action_control->id_base, $displayed_actions ) || ! in_array( $action_control->id_base, $group['actions'] ) ) {
					continue;
				}

				$displayed_actions[] = $action_control->id_base;
				FrmFormActionsController::show_action_icon_link( $action_control, $allowed );
				unset( $actions_icon, $classes );
			}

			foreach ( $group['actions'] as $action ) {
				if ( in_array( $action, $displayed_actions ) ) {
					continue;
				}
				?>
					<li class="frm-action frm-not-installed">
						<a href="javascript:void(0)" class="frm-single-action frm_show_upgrade">
							<span class="frm-outer-circle">
								<span class="frm-inner-circle" <?php
									echo FrmAppHelper::array_to_html_params( $icon_atts ); // WPCS: XSS ok.
								?>>
								<?php
								$icon_atts = array();
								if ( isset( $group['color'] ) ) {
									$icon_atts = array(
										'style' => '--primary-hover:' . $group['color'],
									);
								}
								FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon', $icon_atts );
								?>
								</span>
							</span>
							<?php echo esc_html( $action ); ?>
						</a>
					</li>
				<?php
			}
			?>
		</ul>
		<?php
	}
	?>
	<div class="clear"></div>
	<a href="#" id="frm-show-groups">
		<?php esc_html_e( 'Show all form actions', 'formidable' ); ?>
	</a>
	<a href="#" id="frm-hide-groups">
		<?php esc_html_e( 'Hide extra form actions', 'formidable' ); ?>
	</a>
	<div class="clear"></div>
</div>

<?php FrmFormActionsController::list_actions( $form, $values ); ?>
<?php FrmTipsHelper::pro_tip( 'get_form_action_tip', 'p' ); ?>
