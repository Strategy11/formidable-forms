<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
_deprecated_file( esc_html( basename( __FILE__ ) ), '6.11' );
?>
<div class="frm_actions_dropdown dropdown <?php echo esc_attr( is_rtl() ? 'dropdown-menu-right' : 'dropdown-menu-left' ); ?>">
	<a href="#" id="frm-actionsDrop" class="frm-dropdown-toggle frm_icon_font frm_option_icon" data-toggle="dropdown" title="<?php esc_attr_e( 'Show options', 'formidable' ); ?>"></a>
	<ul class="frm-dropdown-menu frm-on-top" role="menu" aria-labelledby="frm-actionsDrop">
		<?php foreach ( $links as $link ) { ?>
		<li>
			<a href="<?php echo esc_url( $link['url'] ); ?>" tabindex="-1" <?php
			if ( isset( $link['data'] ) ) {
				foreach ( $link['data'] as $data => $value ) {
					echo 'data-' . esc_attr( $data ) . '="' . esc_attr( $value ) . '" ';
				}
			}
			?> >
				<span class="<?php echo esc_attr( $link['icon'] ); ?>"></span>
				<span class="frm_link_label"><?php echo esc_html( $link['label'] ); ?></span>
			</a>
		</li>
		<?php } ?>
	</ul>
</div>
