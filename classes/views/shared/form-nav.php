<a href="?page=formidable" class="frm-header-logo">
<?php
FrmAppHelper::show_logo(
	array(
		'height' => 35,
		'width'  => 35,
	)
);
?>
</a>

<?php FrmFormsHelper::form_switcher( $form->name ); ?>

<ul class="frm_form_nav">
<?php

foreach ( $nav_items as $nav_item ) {
	if ( current_user_can( $nav_item['permission'] ) ) {
		?>
		<li>
			<a href="<?php echo esc_url( $nav_item['link'] ); ?>"
				<?php FrmAppHelper::select_current_page( $nav_item['page'], $current_page, $nav_item['current'] ); ?>
				<?php
				if ( isset( $nav_item['atts'] ) ) {
					foreach ( $nav_item['atts'] as $att => $value ) {
						echo esc_attr( $att ) . '="' . esc_attr( $value ) . '" ';
					}
				}
				?>>
				<?php echo esc_html( $nav_item['label'] ); ?>
			</a>
		</li>
		<?php
	}
}
?>
</ul>
