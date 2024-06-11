<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

FrmFormsHelper::form_switcher( $form );
?>

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
					FrmAppHelper::array_to_html_params( $nav_item['atts'], true );
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
