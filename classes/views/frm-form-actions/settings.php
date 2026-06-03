<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$single_action_attrs = array(
	'href'  => 'javascript:void(0)',
	'class' => 'frm_single_action frm_inactive_action frm_show_upgrade button frm-button-secondary frm-button-sm frm-with-icon frm-ml-auto-force frm-fadein-down-short',
);
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
					<li class="frm-active" data-filter="my_actions"><?php esc_html_e( 'My Actions', 'formidable' ); ?></li>
					<li data-filter="all"><?php esc_html_e( 'All', 'formidable' ); ?></li>
					<?php foreach ( $groups as $group_key => $group ) { ?>
						<?php if ( ! empty( $group['name'] ) && 'my_actions' !== $group_key ) { ?>
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

	<div id="frm-actions-filter-content" data-active-filter="my_actions">
		<?php
		foreach ( $groups as $group_key => $group ) {
			// Handle my_actions group with sections
			if ( 'my_actions' === $group_key && isset( $group['sections'] ) ) {
				?>
				<div data-group="<?php echo esc_attr( $group_key ); ?>">
					<?php
					foreach ( $group['sections'] as $section_key => $section ) {
						// Skip section if it has no actions
						if ( empty( $section['actions'] ) ) {
							continue;
						}
						?>
						<div data-section="<?php echo esc_attr( $section_key ); ?>">
							<?php if ( ! empty( $section['name'] ) ) { ?>
								<h3 class="frm-group-heading"><?php echo esc_html( $section['name'] ); ?></h3>
							<?php } ?>

							<?php
							if ( ! isset( $section['actions'] ) ) {
								$section['actions'] = array();
							}

							$icon_atts = isset( $group['color'] ) ? array( 'style' => '--primary-700:' . $group['color'] ) : array();
							?>
							<ul class="frm_actions_list frm-list-grid-layout frm-m-0">
								<?php
								foreach ( $action_controls as $action_control ) {
									if ( ! in_array( $action_control->id_base, $section['actions'], true ) ) {
										continue;
									}

									FrmFormActionsController::show_action_icon_link( $action_control, $allowed );
								}

								// Build array of installed action IDs to skip in second loop
								$installed_action_ids = array();

								foreach ( $action_controls as $action_control ) {
									$installed_action_ids[] = $action_control->id_base;
								}

								foreach ( $section['actions'] as $action ) {
									// Skip if action is already installed (shown in first loop)
									if ( in_array( $action, $installed_action_ids, true ) ) {
										continue;
									}

									$icon_atts = array();

									if ( isset( $group['color'] ) ) {
										$icon_atts = array(
											'style' => '--primary-700:' . $group['color'],
										);
									}

									$action_icon = $group['icon'] ?? 'frmfont frm_plus_icon';

									$single_action_attrs['data-upgrade'] = sprintf(
										/* translators: %s: Name of form action */
										__( '%s form actions', 'formidable' ),
										$action
									);
									$single_action_attrs['data-medium']     = 'settings-' . $action;
									$single_action_attrs['data-actiontype'] = $action;

									// Set limit for payment actions only if not installed
									if ( in_array( $action, array( 'paypal', 'stripe', 'square' ), true ) ) {
										// Check if this action is already installed
										$action_is_installed = false;

										foreach ( $action_controls as $action_control ) {
											if ( $action_control->id_base === $action ) {
												$action_is_installed = true;
												break;
											}
										}

										// Only set limit if action is not installed
										if ( ! $action_is_installed ) {
											$single_action_attrs['data-limit'] = 1;
										}
									}
									?>
										<li class="frm-card-item frm-card-item--outlined frm-action frm-not-installed frm-group-<?php echo esc_attr( $group_key ); ?>" tabindex="0">
											<div class="frm-h-stack-xs frm-w-full">
												<span class="frm-border-icon">
													<?php FrmAppHelper::icon_by_class( $action_icon, $icon_atts ); ?>
												</span>

												<div class="frm-flex-col frm-min-w-0">
													<h3 class="frm-h-stack-xs frm-text-md frm-capitalize">
														<?php FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon frm_svg15', array( 'aria-label' => __( 'Lock icon', 'formidable' ) ) ); ?>
														<span class="frm-font-medium frm-truncate"><?php echo esc_html( $action ); ?></span>
														<?php
														if ( ! empty( $section['new_actions'] ) && in_array( $action, $section['new_actions'], true ) ) {
															FrmAppHelper::show_pill_text();
														}
														?>
													</h3>
												</div>

												<a <?php FrmAppHelper::array_to_html_params( $single_action_attrs, true ); ?>>
													<?php FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon' ); ?>
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
				</div>
				<?php
				continue;
			}//end if

			// Handle regular groups
			?>
			<div data-group="<?php echo esc_attr( $group_key ); ?>"<?php echo 'my_actions' === $group_key ? '' : ( 'misc' === $group_key ? ' class="frm_hidden"' : ' class="frm_hidden"' ); ?>>
				<?php if ( ! empty( $group['name'] ) ) { ?>
					<h3 class="frm-group-heading"><?php echo esc_html( $group['name'] ); ?></h3>
				<?php } ?>

				<?php
				if ( ! isset( $group['actions'] ) ) {
					$group['actions'] = array();
				}

				$icon_atts = isset( $group['color'] ) ? array( 'style' => '--primary-700:' . $group['color'] ) : array();
				?>
				<ul class="frm_actions_list frm-list-grid-layout frm-m-0">
					<?php
					foreach ( $action_controls as $action_control ) {
						if ( ! in_array( $action_control->id_base, $group['actions'], true ) ) {
							continue;
						}

						FrmFormActionsController::show_action_icon_link( $action_control, $allowed );
					}

					// Build array of installed action IDs to skip in second loop
					$installed_action_ids = array();

					foreach ( $action_controls as $action_control ) {
						$installed_action_ids[] = $action_control->id_base;
					}

					foreach ( $group['actions'] as $action ) {
						// Skip if action is already installed (shown in first loop)
						if ( in_array( $action, $installed_action_ids, true ) ) {
							continue;
						}

						$icon_atts = array();

						if ( isset( $group['color'] ) ) {
							$icon_atts = array(
								'style' => '--primary-700:' . $group['color'],
							);
						}

						$action_icon = $group['icon'] ?? 'frmfont frm_plus_icon';

						$single_action_attrs['data-upgrade'] = sprintf(
							/* translators: %s: Name of form action */
							__( '%s form actions', 'formidable' ),
							$action
						);
						$single_action_attrs['data-medium']     = 'settings-' . $action;
						$single_action_attrs['data-actiontype'] = $action;

						// Set limit for payment actions only if not installed
						if ( in_array( $action, array( 'paypal', 'stripe', 'square' ), true ) ) {
							// Check if this action is already installed
							$action_is_installed = false;

							foreach ( $action_controls as $action_control ) {
								if ( $action_control->id_base === $action ) {
									$action_is_installed = true;
									break;
								}
							}

							// Only set limit if action is not installed
							if ( ! $action_is_installed ) {
								$single_action_attrs['data-limit'] = 1;
							}
						}
						?>
							<li class="frm-card-item frm-card-item--outlined frm-action frm-not-installed frm-group-<?php echo esc_attr( $group_key ); ?>" tabindex="0">
								<div class="frm-h-stack-xs frm-w-full">
									<span class="frm-border-icon">
										<?php FrmAppHelper::icon_by_class( $action_icon, $icon_atts ); ?>
									</span>

									<div class="frm-flex-col frm-min-w-0">
										<h3 class="frm-h-stack-xs frm-text-md frm-capitalize">
											<?php FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon frm_svg15', array( 'aria-label' => __( 'Lock icon', 'formidable' ) ) ); ?>
											<span class="frm-font-medium frm-truncate"><?php echo esc_html( $action ); ?></span>
											<?php
											if ( ! empty( $group['new_actions'] ) && in_array( $action, $group['new_actions'], true ) ) {
												FrmAppHelper::show_pill_text();
											}
											?>
										</h3>
									</div>

									<a <?php FrmAppHelper::array_to_html_params( $single_action_attrs, true ); ?>>
										<?php FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon' ); ?>
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

<h3 class="frm-mt-xl"><?php esc_html_e( 'Current Actions', 'formidable' ); ?></h3>

<?php
FrmFormActionsController::list_actions( $form, $values );
FrmTipsHelper::pro_tip( 'get_form_action_tip', 'p' );
