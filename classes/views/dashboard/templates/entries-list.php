<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-flex-box frm-justify-between">
	<h2 class="frm-widget-heading"><?php echo esc_html( $template['widget-heading'] ); ?></h2>
	<a class="frm-widget-cta" href="<?php echo esc_url( $template['cta']['link'] ); ?>">
		<?php echo esc_html( $template['cta']['label'] ); ?>
	</a>
</div>
<?php
$wp_list_table->display(
	array(
		'display-top-nav'        => false,
		'display-bottom-nav'     => false,
		'display-bottom-headers' => false,
	)
);
