<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_insert_form" class="frm_hidden hidden">
	<div id="frm_popup_content">
		<div class="media-modal wp-core-ui">
			<a href="#" class="media-modal-close">
				<span class="media-modal-icon">
					<span class="screen-reader-text"><?php esc_html_e( 'Close panel', 'formidable' ); ?></span>
				</span>
			</a>

			<div class="media-modal-content">
				<div class="media-frame mode-select wp-core-ui hide-router">

					<div id="frm_insert_form_content">

						<div class="media-frame-menu">
							<div class="media-menu">
								<?php foreach ( $shortcodes as $shortcode => $labels ) { ?>
									<a href="#" class="media-menu-item frm_switch_sc" id="sc-link-<?php echo esc_attr( $shortcode ); ?>">
										<?php echo esc_html( $labels['name'] ); ?>
										<span class="howto"><?php echo esc_html( $labels['label'] ); ?></span>
									</a>
								<?php } ?>
								<div class="clear"></div>
							</div>
						</div>

						<div class="media-frame-title">
							<h1><?php esc_html_e( 'Insert a Form', 'formidable' ); ?>
								<span class="spinner"></span>
								<span class="frm_icon_font frm_arrowdown4_icon"></span>
							</h1>
						</div>

						<div class="media-frame-content">
							<div class="attachments-browser">
								<div id="frm_shortcode_options" class="media-embed">

								</div>
							</div>
						</div>

						<div class="media-frame-toolbar">
							<div class="media-toolbar">
								<div class="media-toolbar-secondary">
									<input type="text" value="" id="frm_complete_shortcode"/>
								</div>
								<div class="media-toolbar-primary search-form">
									<a href="javascript:void(0);" class="button-primary button media-button-group" id="frm_insert_shortcode">
										<?php esc_html_e( 'Insert into Post', 'formidable' ); ?>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
