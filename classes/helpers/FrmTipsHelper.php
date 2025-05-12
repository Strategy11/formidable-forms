<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTipsHelper {

	/**
	 * @param string $callback
	 * @param string $html
	 *
	 * @return void
	 */
	public static function pro_tip( $callback, $html = '' ) {
		if ( FrmAppHelper::pro_is_installed() ) {
			return;
		}

		$tips = self::$callback();
		$tip  = self::get_random_tip( $tips );

		self::show_tip( $tip, $html );
	}

	/**
	 * Shows tip.
	 *
	 * @since 6.0
	 *
	 * @param array  $tip {
	 *      Tip args.
	 *
	 *     @type array  $link Tip link data. See the first parameter of {@see FrmAppHelper::admin_upgrade_link()} for more details.
	 *     @type string $page The based link of the tip. If this is empty, `https://formidableforms.com/lite-upgrade/` will
	 *                        be used. Otherwise, `https://formidableforms.com/{$page}` will be used.
	 *     @type string $tip  Tip text.
	 *     @type string $call Call to action text.
	 * }
	 * @param string $html
	 *
	 * @return void
	 */
	public static function show_tip( $tip, $html = '' ) {
		$defaults = array(
			'page'  => '',
			'class' => 'frm-mt-0',
		);
		$tip      = array_merge( $defaults, $tip );

		if ( isset( $tip['link'] ) && ! isset( $tip['link']['medium'] ) ) {
			$tip['link']['medium'] = 'tip';
		}

		if ( 'p' === $html ) {
			echo '<p class="frmcenter ' . esc_attr( $tip['class'] ) . '">';
		}

		$link = self::get_tip_link( $tip );
		?>
		<a href="<?php echo esc_url( $link ); ?>" <?php echo empty( $tip['link'] ) ? '' : 'target="_blank"'; ?> class="frm_pro_tip frm-gradient">
			<span class="frm-tip-badge"><?php esc_html_e( 'PRO TIP', 'formidable' ); ?></span>

			<?php if ( isset( $tip['call'] ) ) { ?>
				<span class="frm-tip-info">
					<?php echo esc_html( $tip['tip'] ); ?>
				</span>
			<?php } ?>
			<span class="frm-tip-cta">
				<?php echo esc_html( $tip['call'] ? $tip['call'] : $tip['tip'] ); ?>
			</span>
		</a>
		<?php

		if ( 'p' === $html ) {
			echo '</p>';
		}
	}

	/**
	 * @since 6.21
	 *
	 * @param array $tip
	 * @return string
	 */
	private static function get_tip_link( $tip ) {
		if ( empty( $tip['tip'] ) ) {
			return $tip['page'];
		}

		$cta_link = FrmSalesApi::get_best_sale_value( 'pro_tip_cta_link' );
		if ( $cta_link ) {
			if ( is_array( $tip['link'] ) ) {
				$cta_link = FrmAppHelper::maybe_add_missing_utm( $cta_link, $tip['link'] );
			}
			return $cta_link;
		}

		return FrmAppHelper::admin_upgrade_link( $tip['link'], $tip['page'] );
	}

	/**
	 * Use the correct label for the license.
	 *
	 * @since 6.5.1
	 *
	 * @return string
	 */
	private static function cta_label() {
		$cta_text = FrmSalesApi::get_best_sale_value( 'pro_tip_cta_text' );
		if ( $cta_text ) {
			return $cta_text;
		}
		return FrmAddonsController::is_license_expired() ? __( 'Renew', 'formidable' ) : __( 'Upgrade to Pro.', 'formidable' );
	}

	/**
	 * @return array
	 */
	public static function get_builder_tip() {
		$tips = array(
			array(
				'link' => array(
					'content' => 'conditional-logic',
					'param'   => 'conditional-logic-wordpress-forms',
				),
				'tip'  => __( 'Use conditional logic to shorten your forms and increase conversions.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'confirmation-fields',
					'param'   => 'confirmation-fields-wordpress-forms',
				),
				'tip'  => __( 'Eliminate input errors with email confirmation fields.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'page-breaks',
					'param'   => 'wordpress-multi-page-forms',
				),
				'tip'  => __( 'Use page breaks for easier forms.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'file-uploads',
					'param'   => 'wordpress-multi-file-upload-fields',
				),
				'tip'  => __( 'Skip the follow-ups. Let users upload files.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'calculations',
					'param'   => 'field-calculations-wordpress-form',
				),
				'tip'  => __( 'Need to calculate a total?', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'prefill-fields',
					'param'   => 'auto-fill-forms',
				),
				'tip'  => __( 'Save time with autofill forms.', 'formidable' ),
				'call' => self::cta_label(),
			),
		);

		return $tips;
	}

	/**
	 * @return array
	 */
	public static function get_form_settings_tip() {
		$tips = array(
			array(
				'link' => array(
					'content' => 'front-edit-b',
					'param'   => 'wordpress-front-end-editing',
				),
				'tip'  => __( 'Make your site dynamic. Enable front-end editing.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'save-drafts',
					'param'   => 'save-drafts-wordpress-form',
				),
				'tip'  => __( 'Long form? Let users save and finish later', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'form-scheduling',
					'param'   => 'schedule-forms-wordpress',
				),
				'tip'  => __( 'Limit form access with built-in scheduling.', 'formidable' ),
				'call' => self::cta_label(),
			),
		);

		return $tips;
	}

	/**
	 * @return array
	 */
	public static function get_form_action_tip() {
		$tips = array(
			array(
				'link' => array(
					'content' => 'email-routing',
					'param'   => 'virtually-unlimited-emails',
				),
				'tip'  => __( 'Save time — route emails to the right person automatically.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'create-posts',
					'param'   => 'create-posts-pages-wordpress-forms',
				),
				'tip'  => __( 'Create blog posts or pages from the front-end.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'user-submit',
					'param'   => 'create-posts-pages-wordpress-forms',
				),
				'tip'  => __( 'Let your users submit posts on the front-end.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'mailchimp',
					'page'    => 'mailchimp-tip',
				),
				'tip'  => __( 'Send leads to Mailchimp for instant email follow-up.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'paypal-revenue',
					'page'    => 'paypal-increase-revenue-tip',
				),
				'tip'  => __( 'Accept PayPal payments and grow your sales.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'paypal-fast',
					'page'    => 'paypal-save-time-tip',
				),
				'tip'  => __( 'Accept payments now with PayPal integration.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'registration',
					'page'    => 'registration-tip',
				),
				'tip'  => __( 'Automatically create user accounts.', 'formidable' ),
				'call' => __( 'Upgrade to boost your site membership.', 'formidable' ),
			),
			array(
				'link' => array(
					'content' => 'profile',
					'page'    => 'registration-profile-editing-tip',
				),
				'tip'  => __( 'Enable front-end profile editing with User Registration.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'twilio-payment',
					'page'    => 'twilio-tip',
				),
				'tip'  => __( 'Get SMS alerts for form submissions and payments—just add Twilio.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'twilio',
					'page'    => 'twilio-send-tip',
				),
				'tip'  => __( 'Use Twilio to send SMS when forms are submitted.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'acf-tip',
					'param'   => 'acf-tip',
				),
				'tip'  => __( 'Fill Advanced Custom Fields automatically with form entries.', 'formidable' ),
				'call' => self::cta_label(),
			),
		);

		return $tips;
	}

	/**
	 * @return array
	 */
	public static function get_styling_tip() {
		$tips = array(
			array(
				'link' => array(
					'content' => 'style',
					'param'   => 'wordpress-visual-form-styler',
				),
				'tip'  => __( 'Make your forms stand out with multiple style templates.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'style',
					'param'   => 'bg-image-style-settings',
				),
				'tip'  => __( 'Want to add a background image?', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'style',
					'param'   => 'duplicate-style',
				),
				'tip'  => __( 'Want to duplicate a style?', 'formidable' ),
				'call' => self::cta_label(),
			),
		);

		return $tips;
	}

	/**
	 * @return array
	 */
	public static function get_entries_tip() {
		$tips = array(
			array(
				'link' => array(
					'content' => 'entries',
					'param'   => 'form-entry-management-wordpress',
				),
				'tip'  => __( 'Edit form entries anytime with entry management.', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'entries-search',
					'param'   => 'form-entry-management-wordpress',
				),
				'tip'  => __( 'Want to search submitted entries?', 'formidable' ),
				'call' => self::cta_label(),
			),
			array(
				'link' => array(
					'content' => 'views',
					'param'   => 'views-display-form-data',
				),
				'tip'  => __( 'Turn entries into dynamic content — no code needed.', 'formidable' ),
				'call' => self::cta_label(),
			),
		);
		$tips = array_merge( $tips, self::get_import_tip() );

		return $tips;
	}

	/**
	 * @return array
	 */
	public static function get_import_tip() {
		$tips = array(
			array(
				'link' => array(
					'content' => 'import',
					'param'   => 'importing-exporting-wordpress-forms',
				),
				'tip'  => __( 'Want to import entries into your forms?', 'formidable' ),
				'call' => self::cta_label(),
			),
		);

		return $tips;
	}

	public static function get_random_tip( $tips ) {
		$random = rand( 0, count( $tips ) - 1 );

		return $tips[ $random ];
	}

	/**
	 * Displays a call-to-action section in the admin area.
	 *
	 * @since 6.7
	 *
	 * @param array $args {
	 *     An array of arguments to configure the call-to-action section.
	 *
	 *     @type string $title       The title of the section.
	 *     @type string $description The description of the section.
	 *     @type string $link_text   The text for the link.
	 *     @type string $link_url    The URL for the link.
	 *     @type string $role        The required user role to view the section. Default 'administrator'.
	 * }
	 *
	 * @return void
	 */
	public static function show_admin_cta( $args ) {
		$role = ! empty( $args['role'] ) ? $args['role'] : 'administrator';

		if ( ! current_user_can( $role ) ) {
			// Return early if the user doesn't have the required capability.
			return;
		}

		$defaults = array(
			'title'       => '',
			'description' => '',
			'link_text'   => '',
			'link_url'    => '#',
			'class'       => '',
			'id'          => '',
			'target'      => '_blank',
		);

		$args = wp_parse_args( $args, $defaults );

		$attributes = array(
			'class' => trim( 'frm-cta frm-flex frm-p-sm ' . $args['class'] ),
		);

		if ( $args['id'] ) {
			$attributes['id'] = $args['id'];
		}

		require FrmAppHelper::plugin_path() . '/classes/views/shared/admin-cta.php';
	}

	/**
	 * @deprecated 6.21
	 *
	 * @return array
	 */
	public static function get_banner_tip() {
		_deprecated_function( __METHOD__, '6.21' );
		return array();
	}
}
