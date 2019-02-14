<?php
if ( isset( $include_extra_container ) ) { ?>
<div class="<?php echo esc_attr( $include_extra_container ); ?>" id="frm_form_<?php echo esc_attr( $form->id ); ?>_container">
	<?php
}
if ( isset( $message ) && $message != '' ) {
	if ( FrmAppHelper::is_admin() ) {
		?>
<div id="message" class="frm_updated_message"><?php echo wp_kses_post( $message ); ?></div>
		<?php
	} else {
		FrmFormsHelper::maybe_get_scroll_js( $form->id );

		// we need to allow scripts here for javascript in the success message
		echo $message; // WPCS: XSS ok.
	}
}

if ( isset( $errors ) && is_array( $errors ) && ! empty( $errors ) ) {

	if ( isset( $form ) && is_object( $form ) ) {
		FrmFormsHelper::get_scroll_js( $form->id );
	}
	?>
<div class="<?php echo esc_attr( FrmFormsHelper::form_error_class() ); ?>">
	<?php
	$img = '';
	if ( ! FrmAppHelper::is_admin() ) {
		$img = apply_filters( 'frm_error_icon', $img );
		if ( $img && ! empty( $img ) ) {
			echo '<img src="' . esc_url( $img ) . '" alt="" />';
		}
	}

	FrmFormsHelper::show_errors( compact( 'img', 'errors', 'form' ) );

	?>
</div>
	<?php
}

if ( isset( $include_extra_container ) ) {
	?>
</div>
	<?php
}
