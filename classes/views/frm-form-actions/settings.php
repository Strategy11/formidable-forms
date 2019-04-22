<p class="howto">
	<?php esc_html_e( 'Add form actions to your form to perform tasks when an entry is created, updated, imported, and more.', 'formidable' ); ?>
</p>

<?php FrmTipsHelper::pro_tip( 'get_form_action_tip', 'p' ); ?>
				
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
		if ( ! empty( $group['name'] ) ) { ?>
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
				FrmFormActionsController::show_action_icon_link( $action_control );
				unset( $actions_icon, $classes );
			}

			foreach ( $group['actions'] as $action ) {
				if ( ! in_array( $action, $displayed_actions ) ) {
					?>
					<li class="frm-action frm-not-installed">
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
<div class="frm_no_actions">
	<div class="inner_actions">
		<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/sketch_arrow1.png' ); ?>" alt=""/>
		<div class="clear"></div>
		<?php esc_html_e( 'Click an action to add it to this form', 'formidable' ); ?>
	</div>
</div>
<?php FrmFormActionsController::list_actions( $form, $values ); ?>
