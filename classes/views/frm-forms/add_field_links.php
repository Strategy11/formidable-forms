<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-right-panel">
	<div class="frm_field_list">
		<ul id="frm-nav-tabs" class="frm-nav-tabs">
			<li class="frm-tabs">
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
				foreach ( $frm_field_selection as $field_key => $field_type ) {
					$field_label = FrmFormsHelper::get_field_link_name( $field_type );
					?>
					<li class="frmbutton frm6 <?php echo esc_attr( ' frm_t' . $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>">
						<a href="#" class="frm_add_field frm_animate_bg" title="<?php echo esc_html( $field_label ); ?>">
							<?php FrmAppHelper::icon_by_class( FrmFormsHelper::get_field_link_icon( $field_type ) ); ?>
							<span><?php echo esc_html( $field_label ); ?></span>
						</a>
					</li>
					<?php
					unset( $field_key, $field_type );
				}
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
$field_sections = array();
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
					<a href="#" id="frm-<?php echo esc_attr( $field_key ); ?>Drop" class="frm-dropdown-toggle" data-toggle="dropdown" title="<?php echo esc_html( $field_label ); ?>">
						<?php FrmAppHelper::icon_by_class( FrmFormsHelper::get_field_link_icon( $field_type ) ); ?>
						<span><?php echo esc_html( $field_label ); ?> <b class="caret"></b></span>
					</a>

					<ul class="frm-dropdown-menu" role="menu" aria-labelledby="frm-<?php echo esc_attr( $field_key ); ?>Drop">
					<?php foreach ( $field_type['types'] as $k => $type ) { ?>
						<li class="frm_t<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>|<?php echo esc_attr( $k ); ?>">
							<?php echo FrmAppHelper::kses( apply_filters( 'frmpro_field_links', $type, $id, $field_key . '|' . $k ), array( 'a', 'i', 'span' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
	}

	unset( $field_key, $field_type, $field_label );
}
?>
			</ul>
			<div class="clear"></div>

			<?php foreach ( $field_sections as $section => $section_fields ) { ?>
				<h3 class="frm-with-line">
					<span><?php esc_html_e( 'Pricing Fields', 'formidable' ); ?></span>
				</h3>
				<ul class="field_type_list frm_grid_container">
					<?php
					foreach ( $section_fields as $field_key => $field_type ) {
						FrmFieldsHelper::show_add_field_buttons( compact( 'field_key', 'field_type', 'id', 'no_allow_class' ) );
						unset( $field_key, $field_type );
					}
					?>
				</ul>
				<div class="clear"></div>
			<?php } ?>
		</div>
		<?php do_action( 'frm_extra_form_instructions' ); ?>

		<div id="frm-options-panel" class="frm-p-6 tabs-panel frm_hidden">
			<div class="frm-single-settings">
				<div class="frm-embed-field-placeholder">
					<div class="frm-embed-message">
						<?php esc_html_e( 'Select a field to see the options', 'formidable' ); ?>
					</div>
				</div>
			</div>
			<form method="post" id="new_fields">
				<input type="hidden" name="frm_action" value="update" />
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="id" id="form_id" value="<?php echo esc_attr( $values['id'] ); ?>" />
				<?php wp_nonce_field( 'frm_save_form_nonce', 'frm_save_form' ); ?>
				<input type="hidden" id="frm-end-form-marker" name="frm_end" value="1" />

				<?php
				FrmFieldsHelper::inline_modal(
					array(
						'title'    => __( 'Smart Default Values', 'formidable' ),
						'callback' => array( 'FrmFieldsHelper', 'smart_values' ),
						'id'       => 'frm-smart-values-box',
					)
				);

				FrmFieldsHelper::inline_modal(
					array(
						'title'    => __( 'Add Layout Classes', 'formidable' ),
						'callback' => array( 'FrmFieldsHelper', 'layout_classes' ),
						'id'       => 'frm-layout-classes-box',
					)
				);

				FrmFieldsHelper::inline_modal(
					array(
						'title'    => __( 'Input Mask Format', 'formidable' ),
						'callback' => array( 'FrmFieldsHelper', 'input_mask' ),
						'id'       => 'frm-input-mask-box',
					)
				);
				?>
			</form>
		</div>
	</div>

	<form method="post" id="frm_js_build_form">
		<input type="hidden" id="frm_compact_fields" name="frm_compact_fields" value="" />
		<button class="frm_submit_form frm_submit_<?php echo esc_attr( ( isset( $values['ajax_load'] ) && $values['ajax_load'] ) ? '' : 'no_' ); ?>ajax frm_hidden frm_button_submit" type="button" id="frm_submit_side"><?php esc_html_e( 'Update', 'formidable' ); ?></button>
	</form>
</div>
