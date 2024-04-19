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
			'class' => 'frm-mt-0',
			'page'  => '',
		);
		$tip      = array_merge( $defaults, $tip );

		if ( isset( $tip['link'] ) && ! isset( $tip['link']['medium'] ) ) {
			$tip['link']['medium'] = 'tip';
		}

		if ( 'p' === $html ) {
			echo '<p class="frmcenter ' . esc_attr( $tip['class'] ) . '">';
		}

		$link = empty( $tip['link'] ) ? $tip['page'] : FrmAppHelper::admin_upgrade_link( $tip['link'], $tip['page'] );
		?>
		<span class="frm_pro_tip">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_lightning', array( 'aria-hidden' => 'true' ) ); ?>

			<?php if ( isset( $tip['call'] ) ) { ?>
				<span class="frm-tip-info">
					<?php echo esc_html( $tip['tip'] ); ?>
				</span>
			<?php } ?>
			<a href="<?php echo esc_url( $link ); ?>" <?php echo empty( $tip['link'] ) ? '' : 'target="_blank"'; ?> class="frm-tip-cta">
				<?php echo esc_html( $tip['call'] ? $tip['call'] : $tip['tip'] ); ?>
			</a>
		</span>
		<?php

		if ( 'p' === $html ) {
			echo '</p>';
		}
	}

	/**
	 * Use the correct label for the license.
	 *
	 * @since 6.5.1
	 *
	 * @return string
	 */
	private static function cta_label() {
		return FrmAddonsController::is_license_expired() ? __( 'Renew', 'formidable' ) : __( 'Upgrade to Pro.', 'formidable' );
	}

	/**
	 * @return array
	 */
	public static function get_builder_tip() {
		$tips = array(
			array(
				'call' => self::cta_label(),
				'link' => array(
					'content' => 'conditional-logic',
					'param'   => 'conditional-logic-wordpress-forms',
				),
				'tip'  => __( 'Use conditional logic to shorten your forms and increase conversions.', 'formidable' ),
			),
			array(
				'call' => __( 'Add email confirmation fields.', 'formidable' ),
				'link' => array(
					'content' => 'confirmation-fields',
					'param'   => 'confirmation-fields-wordpress-forms',
				),
				'tip'  => __( 'Want to stop losing leads from email typos?', 'formidable' ),
			),
			array(
				'call' => self::cta_label(),
				'link' => array(
					'content' => 'page-breaks',
					'param'   => 'wordpress-multi-page-forms',
				),
				'tip'  => __( 'Use page breaks for easier forms.', 'formidable' ),
			),
			array(
				'call' => __( 'Allow file uploads in your form.', 'formidable' ),
				'link' => array(
					'content' => 'file-uploads',
					'param'   => 'wordpress-multi-file-upload-fields',
				),
				'tip'  => __( 'Cut down on back-and-forth with clients.', 'formidable' ),
			),
			array(
				'call' => self::cta_label(),
				'link' => array(
					'content' => 'calculations',
					'param'   => 'field-calculations-wordpress-form',
				),
				'tip'  => __( 'Need to calculate a total?', 'formidable' ),
			),
			array(
				'call' => __( 'Fill out forms automatically!', 'formidable' ),
				'link' => array(
					'content' => 'prefill-fields',
					'param'   => 'auto-fill-forms',
				),
				'tip'  => __( 'Save time.', 'formidable' ),
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
				'call' => __( 'Add front-end editing.', 'formidable' ),
				'link' => array(
					'content' => 'front-edit-b',
					'param'   => 'wordpress-front-end-editing',
				),
				'tip'  => __( 'A site with dynamic, user-generated content is within reach.', 'formidable' ),
			),
			array(
				'call' => __( 'Let users save drafts and return later!', 'formidable' ),
				'link' => array(
					'content' => 'save-drafts',
					'param'   => 'save-drafts-wordpress-form',
				),
				'tip'  => __( 'Have long forms?', 'formidable' ),
			),
			array(
				'call' => __( 'Add form scheduling.', 'formidable' ),
				'link' => array(
					'content' => 'form-scheduling',
					'param'   => 'schedule-forms-wordpress',
				),
				'tip'  => __( 'Want your form open only for a certain time period?', 'formidable' ),
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
				'call' => __( 'Add email routing.', 'formidable' ),
				'link' => array(
					'content' => 'email-routing',
					'param'   => 'virtually-unlimited-emails',
				),
				'tip'  => __( 'Save time by sending the email to the right person automatically.', 'formidable' ),
			),
			array(
				'call' => self::cta_label(),
				'link' => array(
					'content' => 'create-posts',
					'param'   => 'create-posts-pages-wordpress-forms',
				),
				'tip'  => __( 'Create blog posts or pages from the front-end.', 'formidable' ),
			),
			array(
				'call' => self::cta_label(),
				'link' => array(
					'content' => 'user-submit',
					'param'   => 'create-posts-pages-wordpress-forms',
				),
				'tip'  => __( 'Let your users submit posts on the front-end.', 'formidable' ),
			),
			array(
				'call' => __( 'Send leads straight to Mailchimp.', 'formidable' ),
				'link' => array(
					'content' => 'mailchimp',
					'page'    => 'mailchimp-tip',
				),
				'tip'  => __( 'Grow your business with automated email follow-up.', 'formidable' ),
			),
			array(
				'call' => __( 'Use PayPal with this form.', 'formidable' ),
				'link' => array(
					'content' => 'paypal-revenue',
					'page'    => 'paypal-increase-revenue-tip',
				),
				'tip'  => __( 'Increase revenue.', 'formidable' ),
			),
			array(
				'call' => __( 'Use Paypal with this form.', 'formidable' ),
				'link' => array(
					'content' => 'paypal-fast',
					'page'    => 'paypal-save-time-tip',
				),
				'tip'  => __( 'Get paid instantly.', 'formidable' ),
			),
			array(
				'call' => __( 'Upgrade to boost your site membership.', 'formidable' ),
				'link' => array(
					'content' => 'registration',
					'page'    => 'registration-tip',
				),
				'tip'  => __( 'Automatically create user accounts.', 'formidable' ),
			),
			array(
				'call' => __( 'Add user registration.', 'formidable' ),
				'link' => array(
					'content' => 'profile',
					'page'    => 'registration-profile-editing-tip',
				),
				'tip'  => __( 'Need front-end profile editing?', 'formidable' ),
			),
			array(
				'call' => __( 'Get the Twilio integration.', 'formidable' ),
				'link' => array(
					'content' => 'twilio-payment',
					'page'    => 'twilio-tip',
				),
				'tip'  => __( 'Want an SMS notification when a form is submitted or a payment received?', 'formidable' ),
			),
			array(
				'call' => __( 'Get the Twilio integration.', 'formidable' ),
				'link' => array(
					'content' => 'twilio',
					'page'    => 'twilio-send-tip',
				),
				'tip'  => __( 'Send an SMS message when a form is submitted.', 'formidable' ),
			),
			array(
				'call' => __( 'Add ACF Integration', 'formidable' ),
				'link' => array(
					'content' => 'acf-tip',
					'param'   => 'acf-tip',
				),
				'tip'  => __( 'Fill Advanced Custom Fields from a form.', 'formidable' ),
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
				'call' => __( 'Use multiple style templates.', 'formidable' ),
				'link' => array(
					'content' => 'style',
					'param'   => 'wordpress-visual-form-styler',
				),
				'tip'  => __( 'Make your sidebar and footer forms stand out.', 'formidable' ),
			),
			array(
				'call' => self::cta_label(),
				'link' => array(
					'content' => 'style',
					'param'   => 'bg-image-style-settings',
				),
				'tip'  => __( 'Want to add a background image?', 'formidable' ),
			),
			array(
				'call' => self::cta_label(),
				'link' => array(
					'content' => 'style',
					'param'   => 'duplicate-style',
				),
				'tip'  => __( 'Want to duplicate a style?', 'formidable' ),
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
				'call' => __( 'Add entry management.', 'formidable' ),
				'link' => array(
					'content' => 'entries',
					'param'   => 'form-entry-management-wordpress',
				),
				'tip'  => __( 'Want to edit form submissions?', 'formidable' ),
			),
			array(
				'call' => self::cta_label(),
				'link' => array(
					'content' => 'entries-search',
					'param'   => 'form-entry-management-wordpress',
				),
				'tip'  => __( 'Want to search submitted entries?', 'formidable' ),
			),
			array(
				'call' => __( 'Display form data with Views.', 'formidable' ),
				'link' => array(
					'content' => 'views',
					'param'   => 'views-display-form-data',
				),
				'tip'  => __( 'A site with dynamic, user-generated content is within reach.', 'formidable' ),
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
				'call' => self::cta_label(),
				'link' => array(
					'content' => 'import',
					'param'   => 'importing-exporting-wordpress-forms',
				),
				'tip'  => __( 'Want to import entries into your forms?', 'formidable' ),
			),
		);

		return $tips;
	}

	/**
	 * @return array
	 */
	public static function get_banner_tip() {
		$tips       = array(
			array(
				'call' => __( 'Take your forms to the next level.', 'formidable' ),
				'link' => array(
					'content' => 'professional-results',
					'medium'  => 'banner',
				),
				'tip'  => __( 'Looking for more ways to get professional results?', 'formidable' ),
			),
			array(
				'call' => __( 'Add conditional logic, page breaks, and section headings.', 'formidable' ),
				'link' => array(
					'content' => 'increase-conversions',
					'medium'  => 'banner',
				),
				'tip'  => __( 'Increase conversions in long forms.', 'formidable' ),
			),
			array(
				'call' => __( 'Collect instant payments, and send leads to Mailchimp.', 'formidable' ),
				'link' => array(
					'content' => 'automate',
					'medium'  => 'banner',
				),
				'tip'  => __( 'Automate your business and increase revenue.', 'formidable' ),
			),
		);
		$random     = rand( 0, count( $tips ) - 1 );
		$tip        = $tips[ $random ];
		$tip['num'] = $random;

		return $tip;
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
			'class'       => '',
			'description' => '',
			'id'          => '',
			'link_text'   => '',
			'link_url'    => '#',
			'title'       => '',
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
}
