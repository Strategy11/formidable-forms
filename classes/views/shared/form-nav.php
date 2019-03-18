<ul class="frm_form_nav">
<?php

foreach ( $nav_items as $nav_item ) {
	if ( current_user_can( $nav_item['permission'] ) ) {
		?>
		<li>
			<a href="<?php echo esc_url( $nav_item['link'] ) ?>"
				<?php FrmAppHelper::select_current_page( $nav_item['page'], $current_page, $nav_item['current'] ); ?>
				<?php
				if ( isset( $nav_item['atts'] ) ) {
					foreach ( $nav_item['atts'] as $att => $value ) {
						echo esc_attr( $att ) . '="' . esc_attr( $value ) . '" ';
					}
				}
				?>>
				<?php echo esc_html( $nav_item['label'] ) ?>
			</a>
		</li>
		<?php
	}
}

FrmFormsHelper::form_switcher();
?>
</ul>

<?php
if ( $form && 'show' === $title ) {
	_deprecated_argument( '$title in form-nav.php', '3.0' );
?>
	<input id="title" type="text" value="<?php echo esc_attr( '' === $form->name ? __( '(no title)', 'formidable' ) : $form->name ) ?>" readonly="readonly" disabled="disabled" />
<?php } ?>
