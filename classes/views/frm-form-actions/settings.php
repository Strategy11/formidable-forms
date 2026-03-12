<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="howto">
	<?php esc_html_e( 'Add form actions to your form to perform tasks when an entry is created, updated, imported, and more.', 'formidable' ); ?>
</p>

<div id="frm_email_addon_menu" class="frm-limited-actions">
	<div class="frm-h-stack">
		<div class="frm-style-tabs-wrapper" data-filter-target="#frm-actions-filter-content">
			<div class="frm-tabs-delimiter"><span class="frm-tabs-active-underline"></span></div>
			<div class="frm-tabs-navs">
				<ul class="frm-h-stack-xs frm-children-px-sm">
					<li class="frm-active" data-filter="all"><?php esc_html_e( 'All', 'formidable' ); ?></li>
					<li data-filter="misc"><?php esc_html_e( 'Featured', 'formidable' ); ?></li>
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
				'placeholder' => __( 'Search Form Actions', 'formidable' ),
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
				<ul class="frm_actions_list frm-list-grid-layout">
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
						?>
							<li class="frm-card-item frm-action frm-not-installed">
								<a href="javascript:void(0)" class="frm-single-action frm_show_upgrade">
									<span class="frm-outer-circle">
										<span class="frm-inner-circle" <?php FrmAppHelper::array_to_html_params( $icon_atts, true ); ?>>
										<?php
										$icon_atts = array();

										if ( isset( $group['color'] ) ) {
											$icon_atts = array(
												'style' => '--primary-700:' . $group['color'],
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
					}//end foreach
					?>
				</ul>
			</div>
		<?php
		}//end foreach
		?>
</div>

<?php
FrmFormActionsController::list_actions( $form, $values );
FrmTipsHelper::pro_tip( 'get_form_action_tip', 'p' );
