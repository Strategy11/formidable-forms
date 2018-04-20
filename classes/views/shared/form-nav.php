<ul class="frm_form_nav">
<?php

foreach ( $nav_items as $nav_item ) {
	if ( current_user_can( $nav_item['permission'] ) ) {
		?>
		<li><a<?php FrmAppHelper::select_current_page( $nav_item['page'], $current_page, $nav_item['current'] ); ?> href="<?php echo esc_url( $nav_item['link'] ) ?>"><?php echo esc_html( $nav_item['label'] ) ?></a> </li>
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
	<input id="title" type="text" value="<?php echo esc_attr( '' === $form->name ? __( '(no title)' ) : $form->name ) ?>" readonly="readonly" disabled="disabled" />
<?php } ?>
