<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="<?php echo esc_attr( $component_class ); ?>">
	<div class="frm-radio-container frm-flex-box frm-flex-justify">
		<?php
		$alignments = array( 'left', 'center', 'right' );
		foreach ( $alignments as $align ) :
			if ( empty( $component['options'] ) || in_array( $align, $component['options'], true ) ) :
				$radio_id = 'frm-align-' . $align . '-' . esc_attr( $field_name );
				?>

				<input id="<?php echo esc_attr( $radio_id ); ?>" <?php checked( $field_value, $align ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="<?php echo esc_attr( $align ); ?>" />
				<label class="frm-flex-center" for="<?php echo esc_attr( $radio_id ); ?>" tabindex="0">
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-align-' . $align ); ?>
				</label>

				<?php
			endif;
		endforeach;
		?>
		<span class="frm-radio-active-tracker"></span>
	</div>
</div>