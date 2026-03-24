<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="howto">
	<?php esc_html_e( 'Add form actions to your form to perform tasks when an entry is created, updated, imported, and more.', 'formidable' ); ?>
</p>

<div id="frm_email_addon_menu" class="frm-mt-md">
	<div class="frm-h-stack frm-mb-lg">
		<div class="frm-style-tabs-wrapper" data-filter-target="#frm-actions-filter-content">
			<div class="frm-tabs-delimiter"><span class="frm-tabs-active-underline"></span></div>
			<div class="frm-tabs-navs">
				<ul class="frm-h-stack-xs frm-children-px-sm">
					<li data-filter="all"><?php esc_html_e( 'All', 'formidable' ); ?></li>
					<li class="frm-active" data-filter="misc"><?php esc_html_e( 'Featured', 'formidable' ); ?></li>
					<?php foreach ( $groups as $group_key => $group ) { ?>
						<?php if ( ! empty( $group['name'] ) ) { ?>
							<li data-filter="<?php echo esc_attr( $group_key ); ?>"><?php echo esc_html( $group['name'] ); ?></li>
						<?php } ?>
					<?php } ?>
				</ul>
			</div>
		</div>

		<?php
		FrmAppHelper::show_search_box(
			array(
				'input_id'    => 'actions',
				'placeholder' => __( 'Search Actions', 'formidable' ),
				'tosearch'    => 'frm-action',
				'class'       => 'frm-ml-auto-force',
			)
		);
		?>
	</div>

	<div id="frm-actions-filter-content">
		<?php
		$displayed_actions = array();

		foreach ( $groups as $group_key => $group ) {
			?>
			<div data-group="<?php echo esc_attr( $group_key ); ?>">
				<?php if ( ! empty( $group['name'] ) ) { ?>
					<h3 class="frm-group-heading"><?php echo esc_html( $group['name'] ); ?></h3>
				<?php } ?>

				<?php
				if ( ! isset( $group['actions'] ) ) {
					$group['actions'] = array();
				}
				?>
				<ul class="frm_actions_list frm-list-grid-layout frm-m-0">
					<?php
					foreach ( $action_controls as $action_control ) {
						if ( in_array( $action_control->id_base, $displayed_actions, true ) || ! in_array( $action_control->id_base, $group['actions'], true ) ) {
							continue;
						}

						$displayed_actions[] = $action_control->id_base;
						FrmFormActionsController::show_action_icon_link( $action_control, $allowed );
					}

					foreach ( $group['actions'] as $action ) {
						if ( in_array( $action, $displayed_actions, true ) ) {
							continue;
						}

						$icon_atts = array();
						if ( isset( $group['color'] ) ) {
							$icon_atts = array(
								'style' => '--primary-700:' . $group['color'],
							);
						}

						$action_icon = isset( $group['icon'] ) ? $group['icon'] : 'frmfont frm_plus_icon';
						?>
							<li class="frm-card-item frm-card-item--outlined frm-action frm-not-installed frm-group-<?php echo esc_attr( $group_key ); ?>">
								<div class="frm-h-stack-xs frm-w-full frm-mb-2xs">
									<span class="frm-border-icon">
										<?php FrmAppHelper::icon_by_class( $action_icon, $icon_atts ); ?>
									</span>

									<div class="frm-flex-col">
										<h3>
											<span class="frm-action-lock-icon">
												<?php FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon', array( 'aria-label' => __( 'Lock icon', 'formidable' ) ) ); ?>
											</span>
											<span class="frm-font-medium frm-truncate"><?php echo esc_html( $action ); ?></span>
											<?php if ( ! empty( $group['new_actions'] ) && in_array( $action, $group['new_actions'], true ) ) { ?>
												<?php FrmAppHelper::show_pill_text(); ?>
											<?php } ?>
										</h3>
									</div>

									<a href="javascript:void(0)" class="frm_single_action frm_inactive_action frm_show_upgrade button frm-button-secondary frm-button-sm frm-with-icon frm-ml-auto-force" data-upgrade="<?php echo esc_attr( sprintf( /* translators: %s: Name of form action */ __( '%s form actions', 'formidable' ), $action ) ); ?>" data-medium="<?php echo esc_attr( 'settings-' . $action ); ?>">
										<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_plus_icon' ); ?>
										<span><?php echo esc_html_x( 'Add', 'form action', 'formidable' ); ?></span>
									</a>
								</div>
							</li>
						<?php
					}//end foreach
					?>
				</ul>
			</div>
		<?php
		}//end foreach
		?>

		<p id="frm-actions-no-results" class="frm_hidden frm-mt-0">
			<?php esc_html_e( 'No actions found. Try a different search term.', 'formidable' ); ?>
		</p>
	</div>
</div>

<h3><?php esc_html_e( 'Your Actions', 'formidable' ); ?></h3>

<?php
FrmFormActionsController::list_actions( $form, $values );
FrmTipsHelper::pro_tip( 'get_form_action_tip', 'p' );
