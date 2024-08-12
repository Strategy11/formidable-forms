<?php
/**
 * Deactivation feedback controller
 *
 * @package Formidable
 * @since x.x
 */

class FrmDeactivationFeedbackController {

	private static function is_plugins_page() {
		return 'plugins' === get_current_screen()->id;
	}

	public static function enqueue_assets() {
		if ( ! self::is_plugins_page() ) {
			return;
		}
		wp_enqueue_script( 'frm-deactivation-feedback', FrmAppHelper::plugin_url() . '/js/admin/deactivation-feedback.js', array( 'formidable', 'formidable_dom', 'jquery' ), FrmAppHelper::plugin_version(), true );
        wp_enqueue_style( 'formidable-admin' );

		FrmAppHelper::localize_script( 'front' );

		wp_localize_script(
			'frm-deactivation-feedback',
			'FrmDeactivationFeedbackI18n',
			array(
				'skip_text' => __( 'Skip & Deactivate', 'formidable' ),
			)
		);
	}

	public static function footer_html() {
		if ( ! self::is_plugins_page() ) {
			return;
		}
		?>
		<div id="frm-deactivation-modal" class="">
			<div class="metabox-holder">
				<div class="postbox">
					<div class="inside">
						<?php self::modal_icon(); ?>
						<div id="frm-deactivation-form-wrapper" class="frmapi-form">
							<span class="frm-wait frm_visible_spinner"></span>
						</div>
					</div>
				</div>
			</div>
		</div><!-- End #frm-deactivation-popup -->
		<style>
			#frm-deactivation-modal {
				--padding: 24px;
			}
			.frm_screen_reader {
				border: 0;
				clip: rect(1px, 1px, 1px, 1px);
				-webkit-clip-path: inset(50%);
				clip-path: inset(50%);
				height: 1px;
				margin: -1px;
				overflow: hidden;
				padding: 0;
				position: absolute;
				width: 1px;
				word-wrap: normal !important; /* many screen reader and browser combinations announce broken words as they would appear visually */
			}

			.frm_deactivation_modal_icon {
				position: absolute;
				top: calc(var(--padding) - 1px);
				left: var(--padding);
			}

			#frm-deactivation-modal .frm_form_title {
				padding: 20px var(--padding) 20px calc(2 * var(--padding));
				margin-top: 0;
				border-bottom: 1px solid #f2f4f7;
			}

			#frm-deactivation-modal .frm_description,
			#frm-deactivation-modal .frm_form_field {
				padding-left: var(--padding);
				padding-right: var(--padding);
			}

			#frm-deactivation-modal .inside {
				padding-left: 0;
				padding-right: 0;
			}

			#frm-deactivation-modal input[name="item_key"] + div {
				display: none;
			}

			#frm-deactivation-modal .frm_submit {
				text-align: right;
			}
		</style>
		<?php
	}

	private static function modal_icon() {
		?>
		<svg class="frmsvg frm_deactivation_modal_icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 20 20" xml:space="preserve" fill="#000000">
			<path d="M9.6 12.8h4.8v2.5H9.6v-2.5zm3.8-8H6.7a1 1 0 0 0-1 1v1.5h8.7V4.8h-1z"/><path d="M13.3 8.8H5.6v6.5h2.5v-4h5.2a1 1 0 0 0 1-.7V8.8h-1z"/><path d="M10 20A10 10 0 0 1 0 10 10 10 0 0 1 10 0a10 10 0 0 1 10 10 10 10 0 0 1-10 10zm0-18.7A8.7 8.7 0 0 0 1.3 10a8.7 8.7 0 0 0 8.7 8.7 8.7 8.7 0 0 0 8.7-8.7A8.7 8.7 0 0 0 10 1.3z"/>
		</svg>
		<?php
	}
}
