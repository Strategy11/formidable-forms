<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-field-group-controls" number-of-fields="<?php echo esc_attr( $this->current_field_count ); ?>">
	<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_field_group_layout_icon' ); ?>
	<span class="frm-move">
		<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_thick_move_icon' ); ?>
	</span>
</div>
