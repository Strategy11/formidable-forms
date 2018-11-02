<?php

class FrmTipsHelper {

	public static function pro_tip( $callback, $html = '' ) {
		if ( FrmAppHelper::pro_is_installed() ) {
			return;
		}

		$tips = self::$callback();
		$tip = self::get_random_tip( $tips );

		if ( 'p' === $html ) {
			echo '<p>';
		}

		$link = FrmAppHelper::make_affiliate_url( self::base_url() . $tip['link'] );
		$link .= '&utm_source=WordPress&utm_campaign=liteplugin';
		?>
		<a href="<?php echo esc_url( $link ); ?>" target="_blank" class="frm_pro_tip">
			<span>
				<i class="frm_icon_font frm_check1_icon"></i>  <?php esc_html_e( 'Pro Tip:', 'formidable' ); ?>
			</span>
			<?php echo esc_html( $tip['tip'] ) ?>
			<?php if ( isset( $tip['call'] ) ) { ?>
				<span><?php echo esc_html( $tip['call'] ) ?></span>
			<?php } ?>
		</a>
		<?php
		if ( 'p' === $html ) {
			echo '</p>';
		}
	}

	private static function base_url() {
		return 'https://formidableforms.com/';
	}

	public static function get_builder_tip() {
		$tips = array(
			array(
				'link' => 'conditional-logic-tip/?utm_medium=tip-logic',
				'tip'  => __( 'Use conditional logic to shorten your forms and increase conversions.', 'formidable' ),
				'call' => __( 'Upgrade to Pro.', 'formidable' ),
			),
			array(
				'link' => 'page-break-tip/?utm_medium=tip-pages',
				'tip'  => __( 'Stop intimidating users with long forms.', 'formidable' ),
				'call' => __( 'Use page breaks.', 'formidable' ),
			),
			array(
				'link' => 'file-upload-tip/?utm_medium=tip-uploads',
				'tip'  => __( 'Cut down on back-and-forth with clients.', 'formidable' ),
				'call' => __( 'Allow file uploads in your form.', 'formidable' ),
			),
			array(
				'link' => 'calculations-total-tip/?utm_medium=tip-calculations',
				'tip'  => __( 'Need to calculate a total?', 'formidable' ),
				'call' => __( 'Upgrade to Pro.', 'formidable' ),
			),
			array(
				'link' => 'prefill-fields/?utm_medium=tip-prefill',
				'tip'  => __( 'Save time.', 'formidable' ),
				'call' => __( 'Prefill fields with user info.', 'formidable' ),
			),
		);

		return $tips;
	}

	public static function get_form_settings_tip() {
		$tips = array(
			array(
				'link' => 'front-end-editing-tip/?utm_medium=tip-front-edit',
				'tip'  => __( 'A site with dynamic, user-generated content is within reach.', 'formidable' ),
				'call' => __( 'Add front-end editing.', 'formidable' ),
			),
			array(
				'link' => 'front-end-editing-b-tip/?utm_medium=tip-front-edit',
				'tip'  => __( 'A site with dynamic, user-generated content is within reach.', 'formidable' ),
				'call' => __( 'Add front-end editing.', 'formidable' ),
			),
			array(
				'link' => 'save-drafts-tip/?utm_medium=tip-drafts',
				'tip'  => __( 'Have a long form that takes time to complete?', 'formidable' ),
				'call' => __( 'Let logged-in users save a draft and return later.', 'formidable' ),
			),
			array(
				'link' => 'pricing-lite/?utm_medium=tip-ajax',
				'tip'  => __( 'Want to submit forms without reloading the page?', 'formidable' ),
				'call' => __( 'Get ajax form submit.', 'formidable' ),
			),
		);
		return $tips;
	}

	public static function get_form_action_tip() {
		$tips = array(
			array(
				'link' => 'email-routing-tip/?utm_medium=tip-routing',
				'tip'  => __( 'Save time by sending the email to the right person automatically.', 'formidable' ),
				'call' => __( 'Add email routing.', 'formidable' ),
			),
			array(
				'link' => 'create-posts-tip/?utm_medium=tip-posts',
				'tip'  => __( 'Create blog posts or pages from the front-end.', 'formidable' ),
				'call' => __( 'Upgrade to Pro.', 'formidable' ),
			),
			array(
				'link' => 'front-end-posting-tip/?utm_medium=tip-user-submit',
				'tip'  => __( 'Make front-end posting easy.', 'formidable' ),
				'call' => __( 'Upgrade to Pro.', 'formidable' ),
			),
			array(
				'link' => 'mailchimp-tip/?utm_medium=tip-mailchimp',
				'tip'  => __( 'Grow your business with automated email follow-up.', 'formidable' ),
				'call' => __( 'Send leads straight to MailChimp.', 'formidable' ),
			),
			array(
				'link' => 'paypal-tip/?utm_medium=tip-paypal',
				'tip'  => __( 'Save hours and increase revenue by collecting payments with every submission.', 'formidable' ),
				'call' => __( 'Use PayPal with this form.', 'formidable' ),
			),
			array(
				'link' => 'paypal-increase-revenue-tip/?utm_medium=tip-paypal-revenue',
				'tip'  => __( 'Increase revenue.', 'formidable' ),
				'call' => __( 'Use PayPal with this form.', 'formidable' ),
			),
			array(
				'link' => 'paypal-save-time-tip/?utm_medium=tip-paypal-fast',
				'tip'  => __( 'Get paid more quickly.', 'formidable' ),
				'call' => __( 'Use Paypal with this form.', 'formidable' ),
			),
			array(
				'link' => 'registration-tip/?utm_medium=tip-registration',
				'tip'  => __( 'Boost your site membership.', 'formidable' ),
				'call' => __( 'Automatically create user accounts.', 'formidable' ),
			),
			array(
				'link' => 'registration-profile-editing-tip/?utm_medium=tip-profile',
				'tip'  => __( 'Make front-end profile editing possible.', 'formidable' ),
				'call' => __( 'Add user registration.', 'formidable' ),
			),
			array(
				'link' => 'twilio-tip/?utm_medium=tip-twilio-payment',
				'tip'  => __( 'Want a text when this form is submitted or when a payment is received?', 'formidable' ),
				'call' => __( 'Use Twilio with this form.', 'formidable' ),
			),
			array(
				'link' => 'twilio-send-tip/?utm_medium=tip-twilio',
				'tip'  => __( 'Send a text when this form is submitted.', 'formidable' ),
				'call' => __( 'Get Twilio.', 'formidable' ),
			),
		);

		return $tips;
	}

	public static function get_styling_tip() {
		$tips = array(
			array(
				'link' => 'visual-styling-tip/?utm_medium=tip-style',
				'tip'  => __( 'Make your sidebar or footer form stand out.', 'formidable' ),
				'call' => __( 'Use multiple style templates.', 'formidable' ),
			),
		);
		return $tips;
	}

	public static function get_entries_tip() {
		$tips = array(
			array(
				'link' => 'manage-entries-tip/?utm_medium=tip-entries',
				'tip'  => __( 'Want to edit or delete form submissions?', 'formidable' ),
				'call' => __( 'Add entry management.', 'formidable' ),
			),
			array(
				'link' => 'search-entries-tip/?utm_medium=tip-entries-search',
				'tip'  => __( 'Want to search submitted entries?', 'formidable' ),
				'call' => __( 'Upgrade to Pro.', 'formidable' ),
			),
		);
		$tips = array_merge( $tips, self::get_import_tip() );
		return $tips;
	}

	public static function get_import_tip() {
		$tips = array(
			array(
				'link' => 'import-entries-tip/?utm_medium=tip-import',
				'tip'  => __( 'Want to import entries into your forms?', 'formidable' ),
				'call' => __( 'Upgrade to Pro.', 'formidable' ),
			),
		);
		return $tips;
	}

	public static function get_banner_tip() {
		$tips = array(
			array(
				'link' => '?utm_medium=banner',
				'tip'  => __( 'Looking for more ways to get professional results?', 'formidable' ),
				'call' => __( 'Take your forms to the next level.', 'formidable' ),
			),
			array(
				'link' => '?utm_medium=banner',
				'tip'  => __( 'Increase conversions in long forms.', 'formidable' ),
				'call' => __( 'Add conditional logic, page breaks, and section headings.', 'formidable' ),
			),
			array(
				'link' => '?utm_medium=banner',
				'tip'  => __( 'Automate your business and increase revenue.', 'formidable' ),
				'call' => __( 'Collect instant payments, and send leads to MailChimp.', 'formidable' ),
			),
		);
		$random = rand( 0, count( $tips ) - 1 );
		$tip = $tips[ $random ];
		$tip['num'] = $random;
		return $tip;
	}

	public static function get_random_tip( $tips ) {
		$random = rand( 0, count( $tips ) - 1 );
		return $tips[ $random ];
	}
}
