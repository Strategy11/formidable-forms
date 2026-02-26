<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-right-panel frm-settings-panel frm-scrollbar-wrapper">
	<div class="frm_field_list">
		<div class="frm-style-tabs-wrapper">
			<div class="frm-tabs-delimiter">
				<span data-initial-width="190" class="frm-tabs-active-underline frm-first"></span>
			</div>

			<div class="frm-tabs-navs">
				<ul class="frm-flex-box">
					<li class="frm-active">
						<a href="#frm-insert-fields" id="frm_insert_fields_tab">
							<?php esc_html_e( 'Add Fields', 'formidable' ); ?>
						</a>
					</li>
					<li class="hide-if-no-js">
						<a href="#frm-options-panel" id="frm-options-panel-tab">
							<?php esc_html_e( 'Field Options', 'formidable' ); ?>
						</a>
					</li>
					<?php do_action( 'frm_extra_form_instruction_tabs' ); ?>
				</ul>
			</div>

			<div class="frm-tabs-container">
				<div class="frm-tabs-slide-track frm-flex-box">
					<div class="frm-active">
						<div id="frm-insert-fields" class="tabs-panel">
							<?php
							FrmAppHelper::show_search_box(
								array(
									'input_id'    => 'field-list',
									'placeholder' => __( 'Search Fields', 'formidable' ),
									'tosearch'    => 'frmbutton',
								)
							);
							?>
							<ul class="field_type_list frm_grid_container">
								<?php
								$field_sections = array();
								foreach ( $frm_field_selection as $field_key => $field_type ) {
									// Skip showing field if it's in a section.
									if ( isset( $field_type['section'] ) ) {
										if ( ! isset( $field_sections[ $field_type['section'] ] ) ) {
											$field_sections[ $field_type['section'] ] = array();
										}

										// Mark this field as available when showing in later sections.
										$field_type['is_available'] = true;

										$field_sections[ $field_type['section'] ][ $field_key ] = $field_type;
										continue;
									}

									$field_type['key'] = $field_key;
									FrmFieldsHelper::show_add_field_link( $field_type );
									unset( $field_key, $field_type );
								}//end foreach
								?>
							</ul>
							<div class="clear"></div>
							<?php FrmTipsHelper::pro_tip( 'get_builder_tip' ); ?>
							<h3 class="frm-with-line">
								<span><?php esc_html_e( 'Advanced Fields', 'formidable' ); ?></span>
							</h3>
							<ul class="field_type_list frm_grid_container">
								<?php
								$no_allow_class = apply_filters( 'frm_noallow_class', 'frm_noallow' );

								if ( $no_allow_class === 'frm_noallow' ) {
									$no_allow_class .= ' frm_show_upgrade';
								}

								$pro_fields = FrmField::pro_field_selection();
								// These are Lite fields. They're kept in pro_field_selection for backward compatibility.
								unset( $pro_fields['credit_card'] );
								unset( $pro_fields['product'] );

								foreach ( $pro_fields as $field_key => $field_type ) {
									if ( isset( $field_type['section'] ) ) {
										if ( ! isset( $field_sections[ $field_type['section'] ] ) ) {
											$field_sections[ $field_type['section'] ] = array();
										}
										$field_sections[ $field_type['section'] ][ $field_key ] = $field_type;
										continue;
									}

									if ( is_array( $field_type ) && isset( $field_type['switch_from'] ) ) {
										continue;
									}

									if ( is_array( $field_type ) && isset( $field_type['types'] ) ) {
										_deprecated_argument( 'Field with sub types', '4.0' );
										$field_label = $field_type['name'];
										?>
										<li class="frmbutton frm6 <?php echo esc_attr( $no_allow_class . ' frm_t' . $field_key ); ?> dropdown" id="<?php echo esc_attr( $field_key ); ?>">
											<a href="#" id="frm-<?php echo esc_attr( $field_key ); ?>Drop" class="frm-dropdown-toggle" data-toggle="dropdown" title="<?php echo esc_attr( $field_label ); ?>" role="button" aria-label="<?php echo esc_attr( $field_label ); ?>">
												<?php FrmAppHelper::icon_by_class( FrmFormsHelper::get_field_link_icon( $field_type ) ); ?>
												<span><?php echo esc_html( $field_label ); ?> <b class="caret"></b></span>
											</a>

											<ul class="frm-dropdown-menu" role="menu" aria-labelledby="frm-<?php echo esc_attr( $field_key ); ?>Drop">
											<?php foreach ( $field_type['types'] as $k => $type ) { ?>
												<li class="frm_t<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>|<?php echo esc_attr( $k ); ?>">
													<?php FrmAppHelper::kses_echo( apply_filters( 'frmpro_field_links', $type, $id, $field_key . '|' . $k ), array( 'a', 'i', 'span' ) ); ?>
												</li>
												<?php
												unset( $k, $type );
											}
											?>
											</ul>
										</li>
										<?php
									} else {
										FrmFieldsHelper::show_add_field_buttons( compact( 'field_key', 'field_type', 'id', 'no_allow_class' ) );
									}//end if

									unset( $field_key, $field_type, $field_label );
								}//end foreach
								?>
							</ul>
							<div class="clear"></div>

							<?php
							$section_labels = FrmField::field_section_labels();
							foreach ( $field_sections as $section => $section_fields ) { ?>
								<h3 class="frm-with-line">
									<span><?php echo esc_html( isset( $section_labels[ $section ] ) ? $section_labels[ $section ] : ucwords( $section ) ); ?></span>
									<span style="padding-left: 0;">
										<?php FrmAppHelper::show_pill_text(); ?>
									</span>
								</h3>
								<ul class="field_type_list frm_grid_container">
									<?php
									foreach ( $section_fields as $field_key => $field_type ) {
										if ( ! empty( $field_type['is_available'] ) ) {
											$field_type['key'] = $field_key;
											FrmFieldsHelper::show_add_field_link( $field_type );
										} else {
											FrmFieldsHelper::show_add_field_buttons( compact( 'field_key', 'field_type', 'id', 'no_allow_class' ) );
										}
										unset( $field_key, $field_type );
									}
									?>
								</ul>
								<div class="clear"></div>
							<?php } ?>
						</div>
						<?php do_action( 'frm_extra_form_instructions' ); ?>
					</div>

					<div>
						<div id="frm-options-panel" class="tabs-panel">
							<div class="frm-single-settings">
								<div class="frm-embed-field-placeholder">
									<div class="frm-embed-message">
										<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/page-skeleton/empty-state.svg' ); ?>" alt="<?php esc_attr_e( 'Empty State', 'formidable' ); ?>" />
										<span><?php esc_html_e( 'Select a field to see the options', 'formidable' ); ?></span>
										<button type="button" id="frm-form-add-field" class="frm-button-secondary frm-cursor-pointer frm-mt-xs">
											<?php FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon', array( 'aria-label' => __( 'Add Fields', 'formidable' ) ) ); ?>
											<span><?php esc_html_e( 'Add Fields', 'formidable' ); ?></span>
										</button>
									</div>
								</div>
							</div>
							<form method="post" id="new_fields">
								<input type="hidden" name="frm_action" value="update" />
								<input type="hidden" name="action" value="update" />
								<input type="hidden" name="id" id="form_id" value="<?php echo esc_attr( $values['id'] ); ?>" />
								<input type="hidden" name="draft_fields" id="draft_fields" value="<?php echo esc_attr( implode( ',', FrmFieldsHelper::get_all_draft_field_ids( $values['id'] ) ) ); ?>" />
								<?php wp_nonce_field( 'frm_save_form_nonce', 'frm_save_form' ); ?>
								<input type="hidden" id="frm-end-form-marker" name="frm_end" value="1" />

								<?php
								FrmFieldsHelper::inline_modal(
									array(
										'callback'     => array( 'FrmFieldsHelper', 'smart_values' ),
										'id'           => 'frm-smart-values-box',
										'dismiss-icon' => false,
									)
								);

								FrmFieldsHelper::inline_modal(
									array(
										'callback'     => array( 'FrmFieldsHelper', 'layout_classes' ),
										'id'           => 'frm-layout-classes-box',
										'dismiss-icon' => false,
									)
								);

								FrmFieldsHelper::inline_modal(
									array(
										'callback'     => array( 'FrmFieldsHelper', 'input_mask' ),
										'id'           => 'frm-input-mask-box',
										'dismiss-icon' => false,
									)
								);
								?>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<form method="post" id="frm_js_build_form">
		<input type="hidden" id="frm_compact_fields" name="frm_compact_fields" value="" />
		<button class="frm_submit_form frm_submit_<?php echo esc_attr( ! empty( $values['ajax_load'] ) ? '' : 'no_' ); ?>ajax frm_hidden frm_button_submit" type="button" id="frm_submit_side"><?php esc_html_e( 'Update', 'formidable' ); ?></button>
	</form>
</div>
