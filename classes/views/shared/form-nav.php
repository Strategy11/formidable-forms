<div id="titlediv">
<ul class="frm_form_nav">
<?php

$class = ' class="first"';
foreach ( $nav_items as $nav_item ) {
	if ( current_user_can( $nav_item['permission'] ) ) {
		?>
		<li<?php echo $class ?>><a<?php FrmAppHelper::select_current_page( $nav_item['page'], $current_page, $nav_item['current'] ); ?> href="<?php echo esc_url( $nav_item['link'] ) ?>"><?php echo esc_html( $nav_item['label'] ) ?></a> </li>
		<?php
		$class = '';
	}
}

FrmFormsHelper::form_switcher();
?>
</ul>

<?php if ( $form && $title == 'show' ) { ?>
    <input id="title" type="text" value="<?php echo esc_attr( $form->name == '' ? __( '(no title)') : $form->name ) ?>" readonly="readonly" disabled="disabled" />
<?php } ?>
</div>
