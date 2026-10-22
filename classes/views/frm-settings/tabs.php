<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<input type="search" id="frm-settings-search" class="frm-settings-search" autocomplete="off"
	placeholder="<?php esc_attr_e( 'Search settings', 'formidable' ); ?>"
	aria-label="<?php esc_attr_e( 'Search settings', 'formidable' ); ?>" />
<p id="frm-settings-search-empty" class="howto frm_hidden"><?php esc_html_e( 'No settings found.', 'formidable' ); ?></p>
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
				<span class="frm-font-medium"><?php FrmAppHelper::kses_echo( $section['name'], array( 'span' ) ); ?></span>
			</a>
		</li>
	<?php } ?>
</ul>
