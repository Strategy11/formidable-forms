<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<ul class="frm-category-tabs frm-form-setting-tabs">
	<?php foreach ( $sections as $section ) { ?>
		<li class="<?php echo esc_attr( $current === $section['anchor'] ? 'tabs active starttab' : '' ); ?>">
			<a href="#<?php echo esc_attr( $section['anchor'] ); ?>" class="<?php echo esc_attr( $section['html_class'] ); ?>"
				<?php
				if ( isset( $section['data'] ) ) {
					foreach ( $section['data'] as $data_key => $data_value ) {
						?>
						data-<?php echo esc_attr( $data_key ); ?>="<?php echo esc_attr( $data_value ); ?>"
						<?php
					}
				}
				?>
				>
				<?php FrmAppHelper::icon_by_class( $section['icon'], array( 'aria-hidden' => 'true' ) ); ?>
				<?php echo FrmAppHelper::kses( $section['name'], array( 'span' ) ); // WPCS: XSS ok. ?>
			</a>
		</li>
	<?php } ?>
</ul>
