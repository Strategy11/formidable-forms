<?php
/**
 * Deactivation feedback controller
 *
 * @package Formidable
 * @since x.x
 */

/**
 * Class FrmDeactivationFeedbackController
 */
class FrmDeactivationFeedbackController {

	private static function is_plugins_page() {
		return 'plugins' === get_current_screen()->id;
	}

	public static function enqueue_assets() {
		if ( ! self::is_plugins_page() ) {
			return;
		}
		wp_enqueue_script(
			'frm-deactivation-feedback',
			FrmAppHelper::plugin_url() . '/js/admin/deactivation-feedback.js',
			array( 'formidable', 'formidable_dom', 'jquery' ),
			FrmAppHelper::plugin_version(),
			true
		);
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
					<a class="frm-modal-close dismiss" title="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
						<svg class="frmsvg" id="frm_close_icon" viewBox="0 0 20 20" width="18px" height="18px" aria-label="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
							<path d="M16.8 4.5l-1.3-1.3L10 8.6 4.5 3.2 3.2 4.5 8.6 10l-5.4 5.5 1.3 1.3 5.5-5.4 5.5 5.4 1.3-1.3-5.4-5.5 5.4-5.5z"/>
						</svg>
					</a>

					<div class="inside">
						<img class="frmsvg" src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/logo.svg' ); ?>" alt="" />
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

			#frm-deactivation-modal .inside .frmsvg {
				position: absolute;
				top: calc(var(--padding) - 1px);
				left: var(--padding);
				width: 20px;
				height: 20px;
			}

			#frm-deactivation-modal .frm-modal-close {
				position: absolute;
				top: 20px;
				right: var(--padding);
				z-index: 1000;
			}

			#frm-deactivation-modal .frm_form_title {
				padding: 20px var(--padding) 12px calc(28px + var(--padding));
				margin-top: 0;
				border-bottom: 1px solid #f2f4f7;
				font-weight: 400;
				font-size: 14px;
				line-height: 26px;
			}

			#frm-deactivation-modal .frm_description,
			#frm-deactivation-modal .frm_form_field {
				padding-left: var(--padding);
				padding-right: var(--padding);
			}

			#frm-deactivation-modal .frm_description p {
				font-size: 12px;
				margin-top: 16px;
				margin-bottom: 16px;
				color: #667085;
			}

			#frm-deactivation-modal .inside {
				padding-left: 0;
				padding-right: 0;
			}

			#frm-deactivation-modal .frm_radio {
				margin-bottom: 15px;
			}

			#frm-deactivation-modal .frm_radio .frm_form_field {
				padding-left: 0;
				padding-right: 0;
				margin-bottom: -5px;
			}

			#frm-deactivation-modal .frm_radio .frm_form_field textarea {
				margin-top: 5px;
				height: 65px;
				border-radius: 8px;
				padding: 5px 12px;
				font-size: 14px;
			}

			#frm-deactivation-modal .frm_radio .frm_form_field textarea:focus {
				border-color: #4199FD;
				box-shadow: none;
			}

			#frm-deactivation-modal .frm_radio .frm_form_field textarea::placeholder {
				color: #98A2B3;
			}

			#frm-deactivation-modal .frm_radio .frm_html_container {
				padding: 6px;
				background-color: #F9FAFB;
				margin-top: 8px;
				margin-bottom: 15px;
				border-radius: 8px;
			}

			#frm-deactivation-modal .frm_radio .frm_html_container p {
				margin: 0;
				font-size: 14px;
				line-height: 20px;
				color: #667085;
			}

			#frm-deactivation-modal input[name="item_key"] + div,
			#frm-deactivation-modal .frm_primary_label,
			#frm-deactivation-form-wrapper[data-slug="formidable"] .frm_radio:nth-of-type(5) { /* Hide too expensive option */
				display: none;
			}

			#frm-deactivation-modal .frm_submit {
				text-align: right;
				margin-top: 38px;
			}

			#frm-deactivation-modal .frm_button_submit {
				margin-right: 0;
			}

			#frm-deactivation-modal .frm-skip-link {
				color: #98A2B3;
				display: inline-block;
				font-size: 14px;
				line-height: 40px;
				padding: 0 13px;
			}
		</style>
		<?php
	}

	private static function modal_icon() {
		?>
		<svg
			class="frmsvg frm_deactivation_modal_icon"
			xmlns="http://www.w3.org/2000/svg"
			xmlns:xlink="http://www.w3.org/1999/xlink"
			viewBox="0 0 20 20"
			xml:space="preserve"
		>
			<path d="M9.6 12.8h4.8v2.5H9.6v-2.5zm3.8-8H6.7a1 1 0 0 0-1 1v1.5h8.7V4.8h-1z"/>
			<path d="M13.3 8.8H5.6v6.5h2.5v-4h5.2a1 1 0 0 0 1-.7V8.8h-1z"/>
			<path d="M10 20A10 10 0 0 1 0 10 10 10 0 0 1 10 0a10 10 0 0 1 10 10 10 10 0 0 1-10 10zm0-18.7A8.7 8.7 0 0 0 1.3 10a8.7 8.7 0 0 0 8.7 8.7 8.7 8.7 0 0 0 8.7-8.7A8.7 8.7 0 0 0 10 1.3z"/>
		</svg>
		<?php
	}
}
