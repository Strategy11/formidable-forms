<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="<?php echo esc_attr( $component_class ); ?>">
	<div class="frm-radio-container frm-flex-box frm-flex-justify">
		<?php
		$alignments = array( 'left', 'center', 'right' );
		$labels     = array(
			'left'   => __( 'Left', 'formidable' ),
			'center' => __( 'Center', 'formidable' ),
			'right'  => __( 'Right', 'formidable' ),
		);

		foreach ( $alignments as $align ) :
			if ( empty( $component['options'] ) || in_array( $align, $component['options'], true ) ) :
				$radio_id = ! empty( $component['id'] ) ? $component['id'] . '-' . $align : 'frm-align-' . $align . '-' . $field_name;
				?>

				<input aria-label="<?php echo esc_attr( $labels[ $align ] ); ?>" id="<?php echo esc_attr( $radio_id ); ?>" <?php checked( $field_value, $align ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="<?php echo esc_attr( $align ); ?>" />
				<label aria-label="<?php echo esc_attr( $labels[ $align ] ); ?>" class="frm-flex-center" for="<?php echo esc_attr( $radio_id ); ?>" tabindex="0">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-align-' . $align ); ?>
				</label>

				<?php
			endif;
		endforeach;
		?>
		<span class="frm-radio-active-tracker"></span>
	</div>
</div>