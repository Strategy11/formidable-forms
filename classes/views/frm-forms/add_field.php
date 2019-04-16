<li id="frm_field_id_<?php echo esc_attr( $field['id'] ); ?>" class="<?php echo esc_attr( $li_classes ); ?>" data-fid="<?php echo esc_attr( $field['id'] ); ?>" data-formid="<?php echo esc_attr( 'divider' == $field['type'] ? $field['form_select'] : $field['form_id'] ); ?>" data-ftype="<?php echo esc_attr( $display['type'] ); ?>" data-type="<?php echo esc_attr( $field['type'] ); ?>">
<?php if ( $field['type'] == 'divider' ) { ?>
<div class="divider_section_only">
<?php } ?>

	<?php do_action( 'frm_extra_field_actions', $field['id'] ); ?>

<div id="field_<?php echo esc_attr( $field['id'] ); ?>_inner_container" class="frm_inner_field_container">
	<div class="frm-field-action-icons frm-show-hover">

		<?php if ( $field['type'] === 'divider' ) { ?>
			<a href="#" class="frm-collapse-section frm-hover-icon fas fa-chevron-down" title="<?php esc_attr_e( 'Expand/Collapse Section', 'formidable' ); ?>"></a>
		<?php } ?>

		<a href="#" class="frm_bstooltip frm-move frm-hover-icon frm_icon_font frm_move_icon" title="<?php esc_attr_e( 'Move Field', 'formidable' ); ?>"> </a>
		<a href="#" class="frm_bstooltip frm-hover-icon far fa-trash-alt frm_delete_field" title="<?php esc_attr_e( 'Delete Field', 'formidable' ); ?>"> </a>
		<a href="#" class="frm_bstooltip frm-hover-icon far fa-clone" title="<?php ( $field['type'] === 'divider' ) ? esc_attr_e( 'Duplicate Section', 'formidable' ) : esc_attr_e( 'Duplicate Field', 'formidable' ); ?>"> </a>
	</div>

	<label class="frm_primary_label" id="field_label_<?php echo esc_attr( $field['id'] ); ?>">
		<?php echo FrmAppHelper::kses( force_balance_tags( $field['name'] ), 'all' ); // WPCS: XSS ok. ?>
	</label>

	<div class="frm_form_fields" data-ftype="<?php echo esc_attr( $display['type'] ); ?>">
		<?php $field_obj->show_on_form_builder(); ?>
		<div class="clear"></div>
	</div>
	<?php if ( $display['description'] || in_array( $field['type'], array( 'address', 'credit_card' ) ) ) { ?>
		<div class="description" id="field_description_<?php echo esc_attr( $field['id'] ); ?>">
			<?php echo FrmAppHelper::kses( force_balance_tags( $field['description'] ), 'all' ); // WPCS: XSS ok. ?>
		</div>
	<?php } ?>
</div>
<?php if ( $display['conf_field'] ) { ?>
<div id="frm_conf_field_<?php echo esc_attr( $field['id'] ); ?>_container" class="frm_conf_field_container frm_form_fields frm_conf_details<?php echo esc_attr( $field['id'] . ( $field['conf_field'] ? '' : ' frm_hidden' ) ); ?>">
	<div id="frm_conf_field_<?php echo esc_attr( $field['id'] ); ?>_inner_container" class="frm_inner_conf_container">
		<label class="frm_primary_label">&nbsp;</label>
		<div class="frm_form_fields">
			<input type="text" id="conf_field_<?php echo esc_attr( $field['field_key'] ); ?>" name="field_options[conf_input_<?php echo esc_attr( $field['id'] ); ?>]" placeholder="<?php echo esc_attr( $field['conf_input'] ); ?>" class="dyn_default_value" />
		</div>
		<div id="conf_field_description_<?php echo esc_attr( $field['id'] ); ?>" class="description"><?php
			echo FrmAppHelper::kses( force_balance_tags( $field['conf_desc'] ), 'all' ); // WPCS: XSS ok.
		?></div>
</div>
</div>
<div class="clear"></div>
	<?php
}

FrmFieldsController::load_single_field_settings( compact( 'field', 'field_obj', 'values', 'display' ) );

if ( 'divider' === $field['type'] ) {
	?>
</div>
<div class="frm_no_section_fields">
	<i class="frm-large-plus fas fa-plus-circle"></i>
	<p class="howto"><?php esc_html_e( 'Your section has no fields. Drag fields here to add them to this section.', 'formidable' ); ?></p>
</div>
<ul class="start_divider frm_sorting">
	<?php
} elseif ( 'end_divider' === $field['type'] ) {
	?>
</ul>
	<?php
}

if ( $field['type'] !== 'divider' ) {
	?>
</li>
	<?php
}

?>
