<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<li id="frm_field_id_<?php echo esc_attr( $field['id'] ); ?>" class="<?php echo esc_attr( $li_classes ); ?>" data-fid="<?php echo esc_attr( $field['id'] ); ?>" data-formid="<?php echo esc_attr( 'divider' === $field['type'] ? $field['form_select'] : $field['form_id'] ); ?>" data-ftype="<?php echo esc_attr( $display['type'] ); ?>" data-type="<?php echo esc_attr( $field['type'] ); ?>">
<?php if ( $field['type'] === 'divider' ) { ?>
<div class="divider_section_only">
<?php } ?>

<?php do_action( 'frm_extra_field_actions', $field['id'] ); ?>

<div id="field_<?php echo esc_attr( $field['id'] ); ?>_inner_container" class="frm_inner_field_container">
	<div class="frm-field-action-icons frm-show-hover">

		<div class="frm-sub-label frm-field-id">
			(ID <?php echo esc_html( $field['id'] ); ?>)
		</div>

		<?php if ( $field['type'] === 'divider' ) : ?>
			<a href="#" class="frm-collapse-section" title="<?php esc_attr_e( 'Expand/Collapse Section', 'formidable' ); ?>">
				<?php
				FrmAppHelper::icon_by_class(
					'frmfont frm_arrowdown6_icon',
					array( 'aria-label' => esc_attr__( 'Expand/Collapse Section Icon', 'formidable' ) )
				);
				?>
			</a>
		<?php endif; ?>

		<a href="#" class="frm_bstooltip frm-move frm-hover-icon" title="<?php esc_attr_e( 'Move Field', 'formidable' ); ?>" data-container="body" aria-label="<?php esc_attr_e( 'Move Field', 'formidable' ); ?>">
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_thick_move_icon' ); ?>
		</a>

		<div class="dropdown">
			<a href="#" class="frm_bstooltip frm-hover-icon frm-dropdown-toggle dropdown-toggle" title="<?php esc_attr_e( 'More Options', 'formidable' ); ?>" data-toggle="dropdown" data-container="body" aria-expanded="false">
				<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_thick_more_vert_icon' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Toggle More Options Dropdown', 'formidable' ); ?></span>
			</a>
			<ul class="frm-dropdown-menu frm-p-1 <?php echo esc_attr( is_rtl() ? 'dropdown-menu-left' : 'dropdown-menu-right' ); ?>" role="menu"></ul>
		</div>

	</div>

	<?php $field_obj->show_label_on_form_builder(); ?>

	<div class="frm_form_fields frm_opt_container" data-ftype="<?php echo esc_attr( $display['type'] ); ?>">
		<?php $field_obj->show_on_form_builder(); ?>
		<div class="clear"></div>
	</div>
	<?php if ( $display['description'] || in_array( $field['type'], array( 'address', 'credit_card' ), true ) ) { ?>
		<div class="frm_description" id="field_description_<?php echo esc_attr( $field['id'] ); ?>">
			<?php echo FrmAppHelper::kses( force_balance_tags( $field['description'] ), 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php } ?>
</div>
<?php
if ( $display['conf_field'] ) {
	$input_html = sprintf(
		'<input type="text" id="conf_field_%1$s" name="field_options[conf_input_%2$s]" placeholder="%3$s" class="dyn_default_value" />',
		esc_attr( $field['field_key'] ),
		esc_attr( $field['id'] ),
		esc_attr( $field['conf_input'] )
	);
	?>
	<div id="frm_conf_field_<?php echo esc_attr( $field['id'] ); ?>_container" class="frm_conf_field_container frm_form_fields frm_conf_details<?php echo esc_attr( $field['id'] . ( $field['conf_field'] ? '' : ' frm_hidden' ) ); ?>">
		<div id="frm_conf_field_<?php echo esc_attr( $field['id'] ); ?>_inner_container" class="frm_inner_conf_container">
			<label class="frm_primary_label">&nbsp;</label>
			<div class="frm_form_fields">
				<?php
				/**
				 * Filters the HTML of confirmation input in the backend.
				 *
				 * @since 6.3.1
				 *
				 * @param string $input_html Input HTML.
				 * @param array  $args       Contains `field` array.
				 */
				echo apply_filters( 'frm_conf_input_backend', $input_html, compact( 'field' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
			<div id="conf_field_description_<?php echo esc_attr( $field['id'] ); ?>" class="frm_description"><?php
				echo FrmAppHelper::kses( force_balance_tags( $field['conf_desc'] ), 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?></div>
	</div>
	</div>
	<div class="clear"></div>
	<?php
}//end if

FrmFieldsController::load_single_field_settings( compact( 'field', 'field_obj', 'values', 'display' ) );

if ( 'divider' === $field['type'] ) {
	?>
</div>
<div class="frm_no_section_fields">
	<p class="howto"><?php esc_html_e( 'Add Fields Here', 'formidable' ); ?></p>
</div>
<ul class="start_divider frm_sorting frm_grid_container">
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
