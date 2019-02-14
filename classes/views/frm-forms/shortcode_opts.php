<?php
if ( ! empty( $form_id ) ) {
	?>
	<h4 class="frm_left_label"><?php esc_html_e( 'Select a form:', 'formidable' ); ?></h4>
	<?php FrmFormsHelper::forms_dropdown( 'frmsc_' . $shortcode . '_' . $form_id ); ?>
	<div class="frm_box_line"></div>
	<?php
}

if ( ! empty( $opts ) ) {
	?>
	<h4><?php esc_html_e( 'Options', 'formidable' ); ?></h4>
	<ul>
	<?php
	foreach ( $opts as $opt => $val ) {
		if ( isset( $val['type'] ) && 'text' === $val['type'] ) {
			?>
		<li>
			<label class="setting" for="frmsc_<?php echo esc_attr( $shortcode . '_' . $opt ); ?>">
				<span><?php echo esc_html( $val['label'] ); ?></span>
				<input type="text" id="frmsc_<?php echo esc_attr( $shortcode . '_' . $opt ); ?>" value="<?php echo esc_attr( $val['val'] ); ?>" />
			</label>
		</li>
			<?php
		} elseif ( isset( $val['type'] ) && 'select' === $val['type'] ) {
			?>
		<li>
			<label class="setting" for="frmsc_<?php echo esc_attr( $shortcode . '_' . $opt ); ?>">
				<span><?php echo esc_html( $val['label'] ); ?></span>
				<select id="frmsc_<?php echo esc_attr( $shortcode . '_' . $opt ); ?>">
					<?php foreach ( $val['opts'] as $select_opt => $select_label ) { ?>
						<option value="<?php echo esc_attr( $select_opt ); ?>"><?php echo esc_html( $select_label ); ?></option>
					<?php } ?>
				</select>
			</label>
		</li>
			<?php
		} else {
			?>
		<li>
			<label class="setting" for="frmsc_<?php echo esc_attr( $shortcode . '_' . $opt ); ?>">
				<input type="checkbox" id="frmsc_<?php echo esc_attr( $shortcode . '_' . $opt ); ?>" value="<?php echo esc_attr( $val['val'] ); ?>" />
				<?php echo esc_html( $val['label'] ); ?>
			</label>
		</li>
			<?php
		}
	}
	?>
	</ul>
	<?php
}
