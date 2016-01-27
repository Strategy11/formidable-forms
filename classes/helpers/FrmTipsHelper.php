<?php

class FrmTipsHelper {

	public static function pro_tip( $callback, $html = '' ) {
		if ( FrmAppHelper::pro_is_installed() ) {
			return;
		}

		$tip = self::$callback();
		if ( $html == 'p' ) {
			echo '<p>';
		}
		?>
		<a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( $tip['link'] ) ) ?>" target="_blank" class="frm_pro_tip">
			<span><i class="frm_icon_font frm_check1_icon"></i>  Pro Tip:</span>
			<?php echo $tip['tip'] ?>
			<?php if ( isset( $tip['call'] ) ) { ?>
				<span><?php echo $tip['call'] ?></span>
			<?php } ?>
		</a>
		<?php
		if ( $html == 'p' ) {
			echo '</p>';
		}
	}

	public static function get_builder_tip() {
		$tips = array(
			array(
				'link' => 'https://formidablepro.com/section-tip',
				'tip'  => __( 'Long forms can still be beautiful with sections.', 'formidable' ),
				'call' => __( 'Upgrade to Pro.', 'formidable' ),
			),
			array(
				'link' => 'https://formidablepro.com/conditional-logic-tip',
				'tip'  => __( 'Use conditional logic to shorten your forms and increase conversions.', 'formidable' ),
				'call' => __( 'Upgrade to Pro.', 'formidable' ),
			),
			array(
				'link' => 'https://formidablepro.com/page-break-tip',
				'tip'  => __( 'Stop intimidating users with long forms.', 'formidable' ),
				'call' => __( 'Use page breaks.', 'formidable' ),
			),
			array(
				'link' => 'https://formidablepro.com/file-upload-tip',
				'tip'  => __( 'Cut down on back-and-forth with clients.', 'formidable' ),
				'call' => __( 'Allow file uploads in your form.', 'formidable' ),
			),
		);

		return self::get_random_tip( $tips );
	}

	public static function get_form_settings_tip() {
		$tips = array(
			array(
				'link' => 'https://formidablepro.com/front-end-editing-tip',
				'tip'  => __( 'A site with dynamic, maintainable, user-generated content is within reach.', 'formidable' ),
				'call' => __( 'Add front-end editing.', 'formidable' ),
			),
			array(
				'link' => 'https://formidablepro.com/save-drafts-tip',
				'tip'  => __( 'Have a long form that takes time to complete?', 'formidable' ),
				'call' => __( 'Let logged-in users save a draft and return later.', 'formidable' ),
			),
		);
		return self::get_random_tip( $tips );
	}

	public static function get_form_action_tip() {
		$tips = array(
			array(
				'link' => 'https://formidablepro.com/email-routing-tip',
				'tip'  => __( 'Save time by sending the email to the right person automatically.', 'formidable' ),
				'call' => __( 'Add email routing.', 'formidable' ),
			),
			array(
				'link' => 'https://formidablepro.com/create-posts-tip',
				'tip'  => __( 'Allow anyone to create a blog post using your form.', 'formidable' ),
				'call' => __( 'Upgrade to Pro.', 'formidable' ),
			),
			array(
				'link' => 'https://formidablepro.com/downloads/mailchimp/',
				'tip'  => __( 'Grow your business with automated email follow-up.', 'formidable' ),
				'call' => __( 'Send leads straight to MailChimp.', 'formidable' ),
			),
			array(
				'link' => 'https://formidablepro.com/downloads/paypal-standard/',
				'tip'  => __( 'Save hours and increase revenue by collecting payments with every submission.', 'formidable' ),
				'call' => __( 'Use PayPal with this form.', 'formidable' ),
			),
			array(
				'link' => 'https://formidablepro.com/downloads/registration-lite/',
				'tip'  => __( 'Start building up your site membership.', 'formidable' ),
				'call' => __( 'Automatically create user accounts.', 'formidable' ),
			),
			array(
				'link' => 'https://formidablepro.com/downloads/twilio/',
				'tip'  => __( 'Want a text when this form is submitted or when a payment is received?', 'formidable' ),
				'call' => __( 'Use Twilio with this form.', 'formidable' ),
			),
		);

		return self::get_random_tip( $tips );
	}

	public static function get_styling_tip() {
		$tips = array(
			array(
				'link' => 'https://formidablepro.com/visual-styling-tip',
				'tip'  => __( 'Want your sidebar or footer form to look different from the rest?', 'formidable' ),
				'call' => __( 'Use multiple stylesheets.', 'formidable' ),
			),
		);
		return $tips[0];
	}

	public static function get_entries_tip() {
		$tips = array(
			array(
				'link' => 'https://formidablepro.com/manage-entries-tip',
				'tip'  => __( 'Do you want to edit or delete form submissions?', 'formidable' ),
				'call' => __( 'Add entry management.', 'formidable' ),
			),
			array(
				'link' => 'https://formidablepro.com/search-entries-tip',
				'tip'  => __( 'Want to search submitted entries?', 'formidable' ),
				'call' => __( 'Upgrade to Pro.', 'formidable' ),
			),
		);
		return self::get_random_tip( $tips );
	}

	public static function get_import_tip() {
		$tips = array(
			array(
				'link' => 'https://formidablepro.com/import-entries-tip/',
				'tip'  => __( 'Want to import entries into your forms?', 'formidable' ),
				'call' => __( 'Upgrade to Pro.', 'formidable' ),
			),
		);
		return $tips[0];
	}

	public static function get_random_tip( $tips ) {
		$random = rand( 0, count( $tips ) - 1 );
		return $tips[ $random ];
	}
}
