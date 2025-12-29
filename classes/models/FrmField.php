<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmField {

	/**
	 * @var bool
	 */
	public static $use_cache = true;

	/**
	 * @var int
	 */
	public static $transient_size = 200;

	/**
	 * @return array
	 */
	public static function field_selection() {
		$fields = array(
			'text'                         => array(
				'name' => __( 'Text', 'formidable' ),
				'icon' => 'frmfont frm_text2_icon',
			),
			'textarea'                     => array(
				'name' => __( 'Paragraph', 'formidable' ),
				'icon' => 'frmfont frm_paragraph_icon',
			),
			'checkbox'                     => array(
				'name' => __( 'Checkboxes', 'formidable' ),
				'icon' => 'frmfont frm_check_square_icon',
			),
			'radio'                        => array(
				'name' => __( 'Radio Buttons', 'formidable' ),
				'icon' => 'frmfont frm_radio_checked_icon',
			),
			'select'                       => array(
				'name' => __( 'Dropdown', 'formidable' ),
				'icon' => 'frmfont frm_dropdown_icon',
			),
			'email'                        => array(
				'name' => __( 'Email', 'formidable' ),
				'icon' => 'frmfont frm_email2_icon',
			),
			'url'                          => array(
				'name' => __( 'Website/URL', 'formidable' ),
				'icon' => 'frmfont frm_link2_icon',
			),
			'number'                       => array(
				'name' => __( 'Number', 'formidable' ),
				'icon' => 'frmfont frm_hashtag_icon',
			),
			'name'                         => array(
				'name' => __( 'Name', 'formidable' ),
				'icon' => 'frmfont frm_user_name_icon',
			),
			'phone'                        => array(
				'name' => __( 'Phone', 'formidable' ),
				'icon' => 'frmfont frm_phone_icon',
			),
			'html'                         => array(
				'name' => __( 'HTML', 'formidable' ),
				'icon' => 'frmfont frm_code2_icon',
			),
			'hidden'                       => array(
				'name' => __( 'Hidden', 'formidable' ),
				'icon' => 'frmfont frm_eye_slash2_icon',
			),
			'user_id'                      => array(
				'name' => __( 'User ID', 'formidable' ),
				'icon' => 'frmfont frm_user2_icon',
			),
			'captcha'                      => array(
				'name' => self::get_captcha_field_name(),
				'icon' => 'frmfont frm_shield_check2_icon',
			),
			'credit_card'                  => array(
				'name' => __( 'Payment', 'formidable' ),
				'icon' => 'frmfont frm_credit_card2_icon',
			),
			FrmSubmitHelper::FIELD_TYPE    => array(
				'name' => __( 'Submit', 'formidable' ),
				'hide' => true,
			),
			FrmFieldGdprHelper::FIELD_TYPE => array(
				'name' => __( 'GDPR', 'formidable' ),
				'icon' => 'frmfont frm-gdpr-icon',
				'hide' => FrmFieldGdprHelper::hide_gdpr_field(),
			),
		);

		/**
		 * @param array $fields
		 */
		return apply_filters( 'frm_available_fields', $fields );
	}

	/**
	 * Get the name of the Captcha field based on the global Captcha setting.
	 *
	 * @return string
	 */
	private static function get_captcha_field_name() {
		return 'Captcha';
	}

	/**
	 * @return array
	 */
	public static function pro_field_selection() {
		$upsell_images_url = FrmAppHelper::plugin_url() . '/images/upsell/';
		$fields            = array(
			'file'            => array(
				'name'         => __( 'File Upload', 'formidable' ),
				'icon'         => 'frmfont frm_upload3_icon',
				'message'      => __( 'Add file uploads to save time and cut down on back-and-forth. Upgrade to Pro to get Upload fields and more.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'file-upload-field-preview.webp',
				'learn-more'   => 'features/wordpress-multiple-file-upload-form',
			),
			'ranking'         => array(
				'name'         => __( 'Ranking', 'formidable' ),
				'icon'         => 'frmfont frm_chart_bar_icon frm_show_upgrade',
				'message'      => __( 'Now you can effortlessly gather insights, preferences, and opinions by allowing users to rank options.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'ranking-field-preview.webp',
				'addon'        => 'surveys',
				'learn-more'   => 'ranking-survey',
			),
			'rte'             => array(
				'name'         => __( 'Rich Text', 'formidable' ),
				'icon'         => 'frmfont frm_align_right_icon',
				'message'      => __( 'Go beyond plain text, let your users format their content with bolding, italics, links, lists and more.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'rich-text-field-preview.webp',
				'learn-more'   => 'rich-text',
			),
			'date'            => array(
				'name'         => __( 'Date', 'formidable' ),
				'icon'         => 'frmfont frm_calendar2_icon',
				'message'      => __( 'Capture exact calendar dates effortlessly with a sleek pop-up date picker, ensuring clarity and consistency for scheduling, tracking deadlines, and more.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'date-field-preview.webp',
				'learn-more'   => 'date',
			),
			'time'            => array(
				'name'         => __( 'Time', 'formidable' ),
				'icon'         => 'frmfont frm_clock_icon',
				'message'      => __( 'Precisely log important moments with an intuitive time selection tool, perfect for managing events, availability, or operational hours.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'time-field-preview.webp',
				'learn-more'   => 'time',
			),
			'scale'           => array(
				'name'         => __( 'Scale', 'formidable' ),
				'icon'         => 'frmfont frm_linear_scale_icon',
				'message'      => esc_html__( 'Easily measure satisfaction or quantity using a simple, clear numerical scale, giving you fast, quantifiable insights into user feedback.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'scale-field-preview.webp',
				'learn-more'   => 'scale',
			),
			'star'            => array(
				'name'         => __( 'Star Rating', 'formidable' ),
				'icon'         => 'frmfont frm_star2_icon',
				'message'      => esc_html__( 'Capture instant, appealing feedback with a familiar visual star system, providing immediate and engaging quality assessments from your users.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'star-field-preview.webp',
				'learn-more'   => 'star-ratings',
			),
			'range'           => array(
				'name'         => __( 'Slider', 'formidable' ),
				'icon'         => 'frmfont frm_code_commit_icon',
				'message'      => esc_html__( 'Let users quickly select values within a range using a dynamic, interactive slider, creating a modern and enjoyable data input experience.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'slider-field-preview.webp',
				'learn-more'   => 'slider',
			),
			'toggle'          => array(
				'name'         => __( 'Toggle', 'formidable' ),
				'icon'         => 'frmfont frm_toggle_on_icon',
				'message'      => esc_html__( 'Quickly and easily create cascading fields, populate fields by search and more.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'toggle-field-preview.webp',
				'learn-more'   => 'toggle',
			),
			'data'            => array(
				'name'         => __( 'Dynamic', 'formidable' ),
				'icon'         => 'frmfont frm_dynamic_icon',
				'message'      => __( 'Link entries together, dynamic display information, and even make selections from data entered on another form.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'dynamic-field-preview.webp',
				'learn-more'   => 'dynamic',
			),
			'lookup'          => array(
				'name'         => __( 'Lookup', 'formidable' ),
				'icon'         => 'frmfont frm_search_icon',
				'message'      => esc_html__( 'Link entries together, dynamic display information, and even make selections from data entered on another form.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'lookup-field-preview.webp',
				'learn-more'   => 'lookup',
			),
			'divider|repeat'  => array(
				'name'         => __( 'Repeater', 'formidable' ),
				'icon'         => 'frmfont frm_refresh_icon',
				'message'      => esc_html__( 'Allow users to add rows of fields dynamically as needed (like for multiple attendees or items), making complex data entry flexible and intuitive.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'repeater-field-preview.webp',
				'learn-more'   => 'repeatable-section',
			),
			'end_divider'     => array(
				'name'        => __( 'Section Buttons', 'formidable' ),
				'switch_from' => 'divider',
			),
			'divider'         => array(
				'name'         => __( 'Section', 'formidable' ),
				'icon'         => 'frmfont frm_header_icon',
				'message'      => esc_html__( 'Enhance the user experience and improve completion rates by dividing long surveys into clean, manageable steps with distinct pages.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'section-field-preview.webp',
				'learn-more'   => 'section-heading',
			),
			'break'           => array(
				'name'         => __( 'Page Break', 'formidable' ),
				'icon'         => 'frmfont frm_page_break_icon',
				'message'      => esc_html__( 'Enhance the user experience and improve completion rates by dividing long surveys into clean, manageable steps with distinct pages.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'page-break-field-preview.webp',
				'learn-more'   => 'page-breaks',
			),
			'form'            => array(
				'name'         => __( 'Embed Form', 'formidable' ),
				'icon'         => 'frmfont frm_file_text2_icon',
				'message'      => esc_html__( 'Seamlessly integrate other existing forms or external content directly inside your current form, creating powerful, interconnected workflows.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'embed-form-field-preview.webp',
				'learn-more'   => 'embed-form',
			),
			'likert'          => array(
				'name'         => __( 'Likert Scale', 'formidable' ),
				'icon'         => 'frmfont frm_likert_scale frm_show_upgrade',
				'addon'        => 'surveys',
				'message'      => esc_html__( 'Get nuanced, detailed opinions using standardized agreement scales, unlocking deeper insights beyond simple yes/no answers.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'likert-field-preview.webp',
				'learn-more'   => 'likert-scale',
			),
			'nps'             => array(
				'name'         => __( 'NPS', 'formidable' ),
				'icon'         => 'frmfont frm_nps frm_show_upgrade',
				'addon'        => 'surveys',
				'message'      => esc_html__( 'Directly measure customer loyalty with the industry-standard Net Promoter Score® field, giving you a single, vital metric for business growth.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'nps-field-preview.webp',
				'learn-more'   => 'net-promoter-score',
			),
			'password'        => array(
				'name'         => __( 'Password', 'formidable' ),
				'icon'         => 'frmfont frm_lock_closed2_icon',
				'message'      => esc_html__( 'Confidently collect sensitive credentials or create secure sections with a dedicated field that masks input for privacy and security.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'password-field-preview.webp',
				'learn-more'   => 'password-2',
			),
			'tag'             => array(
				'name'         => __( 'Tags', 'formidable' ),
				'icon'         => 'frmfont frm_price_tags2_icon',
				'message'      => esc_html__( 'Facilitate easy categorization and analysis by letting users select or create multiple relevant keywords or labels for their entry.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'tags-field-preview.webp',
				'learn-more'   => 'tags',
			),
			// This is no longer a Pro field, but without this here, Pro triggers "undefined index" notices.
			// Right now it leaves a gap. Maybe we can skip anything without a name or something.
			'credit_card'     => array(
				'name' => '',
				'icon' => '',
			),
			'address'         => array(
				'name'         => __( 'Address', 'formidable' ),
				'icon'         => 'frmfont frm_location2_icon',
				'message'      => esc_html__( 'Instantly capture location information, simplifying logistics and contact collection. (Or go further with our Geolocation add-on!)', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'address-field-preview.webp',
				'learn-more'   => 'address',
			),
			'summary'         => array(
				'name'         => __( 'Summary', 'formidable' ),
				'icon'         => 'frmfont frm_file_text3_icon',
				'message'      => __( 'Provide users with a clean, final review of all their entered data before submission, reducing errors and improving data quality.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'summary-field-preview.webp',
				'learn-more'   => 'summary-review-before-submit',
			),
			'signature'       => array(
				'name'         => __( 'Signature', 'formidable' ),
				'icon'         => 'frmfont frm_signature_icon frm_show_upgrade',
				'addon'        => 'signature',
				'message'      => __( 'Capture digital e-signatures directly within your form, making official agreements and authorizations straightforward.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'signature-field-preview.webp',
				'learn-more'   => 'formidable-signature',
			),
			'ai'              => array(
				'name'         => __( 'AI', 'formidable' ),
				'icon'         => 'frmfont frm-ai-icon frm_show_upgrade',
				'addon'        => 'ai',
				'message'      => __( 'Harness artificial intelligence by taking user input and let the model of your choice create an output, quickly and easily.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'ai-field-preview.webp',
				'learn-more'   => 'form-ai',
			),
			'ssa-appointment' => array(
				'name'         => __( 'Appointment', 'formidable' ),
				'icon'         => 'frmfont frm_schedule_icon frm_show_upgrade',
				'require'      => 'Simply Schedule Appointments',
				'message'      => sprintf(
					/* translators: %1$s: Link opening HTML, %2$s: Link tag closing */
					esc_html__( 'Appointment fields are an integration with %1$sSimply Schedule Appointments%2$s. Get started now to schedule appointments directly from your forms.', 'formidable' ),
					'<a class="frm-inline-flex" href="https://simplyscheduleappointments.com/meet/formidable/" target="_blank" rel="noopener">',
					'</a>'
				),
				'link'         => 'https://simplyscheduleappointments.com/meet/formidable/',
				'upsell_image' => $upsell_images_url . 'appointment-field-preview.webp',
				'learn-more'   => 'simply-schedule-appointments-forms',
			),
			'product'         => array(
				'name'         => __( 'Product', 'formidable' ),
				'icon'         => 'frmfont frm_product2_icon',
				'section'      => 'pricing',
				'message'      => __( 'Turn your form into a simple storefront by adding fields to list products, choose quantities, and calculate totals, enabling direct sales.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'product-field-preview.webp',
				'learn-more'   => 'features/pricing-fields',
			),
			'quantity'        => array(
				'name'         => __( 'Quantity', 'formidable' ),
				'icon'         => 'frmfont frm_quantity_icon',
				'section'      => 'pricing',
				'message'      => __( 'Turn your form into a simple storefront by adding fields to list products, choose quantities, and calculate totals, enabling direct sales.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'quantity-field-preview.webp',
				'learn-more'   => 'features/pricing-fields',
			),
			'total'           => array(
				'name'         => __( 'Total', 'formidable' ),
				'icon'         => 'frmfont frm_total2_icon',
				'section'      => 'pricing',
				'message'      => __( 'Turn your form into a simple storefront by adding fields to list products, choose quantities, and calculate totals, enabling direct sales.', 'formidable' ),
				'upsell_image' => $upsell_images_url . 'total-field-preview.webp',
				'learn-more'   => 'features/pricing-fields',
			),
		);

		// Since the signature field may be in a different section, don't show it twice.
		$lite_fields = self::field_selection();

		if ( isset( $lite_fields['signature'] ) ) {
			unset( $fields['signature'] );
		}

		return apply_filters( 'frm_pro_available_fields', $fields );
	}

	/**
	 * Consider a field new for 90 days after the release date.
	 *
	 * @since 6.8.3
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	private static function field_is_new( $type ) {
		$release_dates = array(
			'ranking' => '2024-03-12',
		);

		if ( ! isset( $release_dates[ $type ] ) ) {
			return false;
		}

		$release_date = $release_dates[ $type ];

		$three_months_after_release = gmdate( 'Y-m-d', strtotime( $release_date . ' + 90 days' ) );
		return gmdate( 'Y-m-d' ) < $three_months_after_release;
	}

	/**
	 * @since 4.0
	 *
	 * @return array
	 */
	public static function all_field_selection() {
		$pro_field_selection = self::pro_field_selection();
		return array_merge( $pro_field_selection, self::field_selection() );
	}

	/**
	 * Create a field.
	 *
	 * @param array $values
	 * @param bool  $return
	 *
	 * @return false|int
	 */
	public static function create( $values, $return = true ) {
		global $wpdb, $frm_duplicate_ids;

		$new_values              = array();
		$key                     = $values['field_key'] ?? $values['name'];
		$new_values['field_key'] = FrmAppHelper::get_unique_key( $key, $wpdb->prefix . 'frm_fields', 'field_key' );

		$values = FrmAppHelper::maybe_filter_array( $values, array( 'name', 'description' ) );

		foreach ( array( 'name', 'description', 'type', 'default_value' ) as $col ) {
			if ( isset( $values[ $col ] ) ) {
				$new_values[ $col ] = $values[ $col ];
			}
		}

		$new_values['options']       = self::maybe_filter_options( $values['options'] );
		$new_values['field_order']   = isset( $values['field_order'] ) ? (int) $values['field_order'] : null;
		$new_values['required']      = isset( $values['required'] ) ? (int) $values['required'] : 0;
		$new_values['form_id']       = isset( $values['form_id'] ) ? (int) $values['form_id'] : null;
		$new_values['field_options'] = $values['field_options'];
		$new_values['created_at']    = current_time( 'mysql', 1 );

		if ( isset( $values['id'] ) ) {
			$frm_duplicate_ids[ $values['field_key'] ] = $new_values['field_key'];
			$new_values                                = apply_filters( 'frm_duplicated_field', $new_values );
		}

		self::preserve_format_option_backslashes( $new_values );

		foreach ( $new_values as $k => $v ) {
			if ( is_array( $v ) ) {
				$new_values[ $k ] = $k === 'default_value' ? FrmAppHelper::maybe_json_encode( $v ) : serialize( $v );
			}
			unset( $k, $v );
		}

		$query_results = $wpdb->insert( $wpdb->prefix . 'frm_fields', $new_values );

		if ( ! $query_results ) {
			return false;
		}

		self::delete_form_transient( $new_values['form_id'] );
		$new_id = $wpdb->insert_id;

		if ( ! $return ) {
			return false;
		}

		if ( isset( $values['id'] ) ) {
			$frm_duplicate_ids[ $values['id'] ] = $new_id;
		}

		return $new_id;
	}

	/**
	 * Maybe filter HTML in field options data.
	 * HTML is only filtered when unsafe HTML is disallowed.
	 * See FrmAppHelper::allow_unfiltered_html.
	 *
	 * @since 5.0.08
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	private static function maybe_filter_options( $options ) {
		$options = FrmAppHelper::maybe_filter_array( $options, array( 'custom_html' ) );

		if ( ! empty( $options['custom_html'] ) ) {
			$options['custom_html'] = self::maybe_filter_custom_html_input_attributes( $options['custom_html'] );
		}

		if ( ! empty( $options['classes'] ) ) {
			$options['classes'] = implode( ' ', array_map( 'FrmFormsHelper::sanitize_layout_class', explode( ' ', $options['classes'] ) ) );
		}

		return $options;
	}

	/**
	 * Prevent users who do not have permission to insert JavaScript attributes in input elements.
	 * This is triggered when a field is updated.
	 *
	 * @since 6.11.2
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	private static function maybe_filter_custom_html_input_attributes( $html ) {
		if ( FrmAppHelper::allow_unfiltered_html() ) {
			return $html;
		}

		$pattern = get_shortcode_regex( array( 'input' ) );
		return preg_replace_callback(
			"/$pattern/",
			/**
			 * @param array $match Shortcode data.
			 *
			 * @return string
			 */
			function ( $match ) {
				$attr = shortcode_parse_atts( $match[3] );

				if ( ! is_array( $attr ) ) {
					// In old versions of WordPress (older than 6.5), this might not be an array.
					return '[input]';
				}

				$safe_atts = array();

				foreach ( $attr as $attr_key => $att ) {
					if ( ! is_numeric( $attr_key ) ) {
						// opt=1 without parentheses for example is mapped like 'opt' => 1.
						$key   = $attr_key;
						$value = $att;
					} else {
						// Some data is mapped like 0 => 'placeholder="Placeholder"'.
						$split = explode( '=', $att, 2 );

						if ( 2 !== count( $split ) ) {
							continue;
						}

						$key   = trim( $split[0] );
						$value = trim( $split[1], '"' );
					}

					if ( FrmAppHelper::input_key_is_safe( $key, 'update' ) ) {
						$safe_atts[ $key ] = $value;
					}
				}//end foreach

				if ( ! $safe_atts ) {
					return '[input]';
				}

				return '[input ' . FrmAppHelper::array_to_html_params( $safe_atts ) . ']';
			},
			$html
		);
	}

	/**
	 * Process the field duplication.
	 *
	 * @since 5.0.05
	 *
	 * @param int|string $field_id Field ID.
	 * @param int|string $form_id  Form ID.
	 *
	 * @return array|false
	 */
	public static function duplicate_single_field( $field_id, $form_id ) {
		$copy_field = self::getOne( $field_id );

		if ( ! $copy_field ) {
			return false;
		}

		do_action( 'frm_duplicate_field', $copy_field, $form_id );
		do_action( 'frm_duplicate_field_' . $copy_field->type, $copy_field, $form_id );

		$values = array(
			'id' => $copy_field->id,
		);
		FrmFieldsHelper::fill_field( $values, $copy_field, $copy_field->form_id );
		$values = apply_filters( 'frm_prepare_single_field_for_duplication', $values );

		$field_id = self::create( $values );

		/**
		 * Fires after duplicating a field.
		 *
		 * @since 5.0.04
		 *
		 * @param array $args {
		 *     The arguments.
		 *
		 *     @type int    $field_id   New field ID.
		 *     @type array  $values     Values before inserting.
		 *     @type object $copy_field Copy field data.
		 *     @type int    $form_id    Form ID.
		 * }
		 */
		do_action( 'frm_after_duplicate_field', compact( 'field_id', 'values', 'copy_field', 'form_id' ) );

		return compact( 'field_id', 'values' );
	}

	/**
	 * @param int|string $old_form_id
	 * @param int|string $form_id
	 * @param bool       $copy_keys
	 * @param false|int  $blog_id
	 *
	 * @return void
	 */
	public static function duplicate( $old_form_id, $form_id, $copy_keys = false, $blog_id = false ) {
		global $frm_duplicate_ids;

		$where  = array(
			array(
				'or'                => 1,
				'fi.form_id'        => $old_form_id,
				'fr.parent_form_id' => $old_form_id,
			),
		);
		$fields = self::getAll( $where, 'field_order', '', $blog_id );

		foreach ( (array) $fields as $field ) {
			$new_key = $copy_keys ? $field->field_key : '';

			if ( $copy_keys && substr( $field->field_key, - 1 ) == 2 ) {
				$new_key = rtrim( $new_key, 2 );
			}

			$values = array();
			FrmFieldsHelper::fill_field( $values, $field, $form_id, $new_key );

			// If this is a repeating section, create new form
			if ( self::is_repeating_field( $field ) ) {
				// create the repeatable form
				$new_repeat_form_id = apply_filters(
					'frm_create_repeat_form',
					0,
					array(
						'parent_form_id' => $form_id,
						'field_name'     => $field->name,
					)
				);

				// Save old form_select
				$old_repeat_form_id = $field->field_options['form_select'];

				// Update form_select for repeating field
				$values['field_options']['form_select'] = $new_repeat_form_id;
			}

			// If this is a field inside of a repeating section, associate it with the correct form
			if ( $field->form_id != $old_form_id && isset( $old_repeat_form_id ) && isset( $new_repeat_form_id ) && $field->form_id == $old_repeat_form_id ) {
				$values['form_id'] = $new_repeat_form_id;
			}

			$values['description'] = FrmFieldsHelper::switch_field_ids( $values['description'] );

			$values                                 = apply_filters( 'frm_duplicated_field', $values );
			$new_id                                 = self::create( $values );
			$frm_duplicate_ids[ $field->id ]        = $new_id;
			$frm_duplicate_ids[ $field->field_key ] = $new_id;
			unset( $field );
		}//end foreach
	}

	/**
	 * @param int|string $id
	 * @param array      $values
	 *
	 * @return false|int
	 */
	public static function update( $id, $values ) {
		global $wpdb;

		$id     = absint( $id );
		$values = FrmAppHelper::maybe_filter_array( $values, array( 'name', 'description' ) );

		if ( isset( $values['field_key'] ) ) {
			$values['field_key'] = FrmAppHelper::get_unique_key( $values['field_key'], $wpdb->prefix . 'frm_fields', 'field_key', $id );
		}

		if ( isset( $values['required'] ) ) {
			$values['required'] = (int) $values['required'];
		}

		self::preserve_format_option_backslashes( $values );

		if ( isset( $values['type'] ) ) {
			if ( 'dropdown' === $values['type'] ) {
				// To avoid conflicts with security plugins the value "dropdown" is sent for select fields.
				// This is because "select" gets matched for SQL injection attempts.
				$values['type'] = 'select';
			}

			/**
			 * @since 6.9 The Field ID param was added.
			 *
			 * @param array $values
			 * @param int   $id Field ID.
			 */
			$values = apply_filters( 'frm_clean_' . $values['type'] . '_field_options_before_update', $values, $id );

			if ( $values['type'] === 'hidden' && isset( $values['field_options'] ) && isset( $values['field_options']['clear_on_focus'] ) ) {
				// don't keep the old placeholder setting for hidden fields
				$values['field_options']['clear_on_focus'] = 0;
			}
		}

		// serialize array values
		foreach ( array( 'field_options', 'options' ) as $opt ) {
			if ( isset( $values[ $opt ] ) && is_array( $values[ $opt ] ) ) {
				if ( 'field_options' === $opt ) {
					$values[ $opt ] = self::maybe_filter_options( $values[ $opt ] );
				}
				$values[ $opt ] = serialize( $values[ $opt ] );
			}
		}

		if ( isset( $values['default_value'] ) && is_array( $values['default_value'] ) ) {
			$values['default_value'] = json_encode( $values['default_value'] );
		}

		$query_results = $wpdb->update( $wpdb->prefix . 'frm_fields', $values, array( 'id' => $id ) );

		$form_id = 0;

		if ( isset( $values['form_id'] ) ) {
			$form_id = absint( $values['form_id'] );
		} else {
			$field = self::getOne( $id );

			if ( $field ) {
				$form_id = $field->form_id;
			}
			unset( $field );
		}
		unset( $values );

		if ( $query_results ) {
			wp_cache_delete( $id, 'frm_field' );

			if ( $form_id ) {
				self::delete_form_transient( $form_id );
			}
		}

		return $query_results;
	}

	/**
	 * Keep backslashes in the phone format option
	 *
	 * @since 2.0.8
	 *
	 * @param array $values Pass by reference.
	 *
	 * @return void
	 */
	private static function preserve_format_option_backslashes( &$values ) {
		if ( isset( $values['field_options']['format'] ) ) {
			$values['field_options']['format'] = FrmAppHelper::preserve_backslashes( $values['field_options']['format'] );
		}
	}

	/**
	 * @param int|string $id
	 *
	 * @return bool|int
	 */
	public static function destroy( $id ) {
		global $wpdb;

		do_action( 'frm_before_destroy_field', $id );

		wp_cache_delete( $id, 'frm_field' );
		$field = self::getOne( $id );

		if ( ! $field ) {
			return false;
		}

		self::delete_form_transient( $field->form_id );

		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_item_metas WHERE field_id=%d', $id ) );

		return $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_fields WHERE id=%d', $id ) );
	}

	/**
	 * @param int|string $form_id
	 *
	 * @return void
	 */
	public static function delete_form_transient( $form_id ) {
		$form_id = absint( $form_id );
		delete_transient( 'frm_form_fields_' . $form_id . 'excludeinclude' );
		delete_transient( 'frm_form_fields_' . $form_id . 'includeinclude' );
		delete_transient( 'frm_form_fields_' . $form_id . 'includeexclude' );
		delete_transient( 'frm_form_fields_' . $form_id . 'excludeexclude' );

		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s', '_transient_timeout_frm_form_fields_' . $form_id . 'ex%', '_transient_frm_form_fields_' . $form_id . 'ex%', '_transient_timeout_frm_form_fields_' . $form_id . 'in%', '_transient_frm_form_fields_' . $form_id . 'in%' ) );

		FrmDb::cache_delete_group( 'frm_field' );

		$form = FrmForm::getOne( $form_id );

		if ( $form && $form->parent_form_id && $form->parent_form_id != $form_id ) {
			self::delete_form_transient( $form->parent_form_id );
		}
	}

	/**
	 * If $field is numeric, get the field object
	 *
	 * @param int|object|string $field
	 *
	 * @return void
	 */
	public static function maybe_get_field( &$field ) {
		if ( ! is_object( $field ) ) {
			$field = self::getOne( $field );
		}
	}

	/**
	 * @param int|string $id The field id or key.
	 * @param bool       $filter When true, run the frm_field filter.
	 *
	 * @return object|null
	 */
	public static function getOne( $id, $filter = false ) {
		if ( empty( $id ) ) {
			return null;
		}

		global $wpdb;

		$where = is_numeric( $id ) ? 'id=%d' : 'field_key=%s';
		$query = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'frm_fields WHERE ' . $where, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$results = FrmDb::check_cache( $id, 'frm_field', $query, 'get_row', 0 );

		if ( empty( $results ) ) {
			self::filter_field( $filter, $results );
			return $results;
		}

		if ( is_numeric( $id ) ) {
			FrmDb::set_cache( $results->field_key, $results, 'frm_field' );
		} elseif ( $results ) {
			FrmDb::set_cache( $results->id, $results, 'frm_field' );
		}

		self::prepare_options( $results );
		self::filter_field( $filter, $results );

		return wp_unslash( $results );
	}

	/**
	 * @since 3.06.01
	 *
	 * @param bool   $filter When true, run the frm_field filter.
	 * @param object $results
	 *
	 * @return void
	 */
	private static function filter_field( $filter, &$results ) {
		if ( $filter ) {
			/**
			 * @since 3.06.01
			 */
			$results = apply_filters( 'frm_field', $results );
		}
	}

	/**
	 * Get the field type by key or id
	 *
	 * @param int|string $id  The field id or key.
	 * @param mixed      $col The name of the column in the fields database table.
	 *
	 * @return mixed
	 */
	public static function get_type( $id, $col = 'type' ) {
		$field = FrmDb::check_cache( $id, 'frm_field' );

		if ( $field ) {
			return $field->{$col};
		}

		$where = is_numeric( $id ) ? array( 'id' => $id ) : array( 'field_key' => $id );

		return FrmDb::get_var( 'frm_fields', $where, $col );
	}

	/**
	 * @param int|string $form_id
	 * @param string     $type
	 * @param int|string $limit
	 * @param string     $inc_sub
	 *
	 * @return array|object
	 */
	public static function get_all_types_in_form( $form_id, $type, $limit = '', $inc_sub = 'exclude' ) {
		if ( ! $form_id ) {
			return array();
		}

		$results = self::get_fields_from_transients(
			$form_id,
			array(
				'inc_embed'  => $inc_sub,
				'inc_repeat' => $inc_sub,
			)
		);

		if ( ! empty( $results ) ) {
			$fields = array();
			$count  = 0;

			foreach ( $results as $result ) {
				if ( $type != $result->type ) {
					continue;
				}

				$fields[ $result->id ] = $result;
				++$count;

				if ( $limit == 1 ) {
					$fields = $result;
					break;
				}

				if ( ! empty( $limit ) && $count >= $limit ) {
					break;
				}

				unset( $result );
			}

			return wp_unslash( $fields );
		}//end if

		self::$use_cache = false;

		$where = array(
			'fi.form_id' => (int) $form_id,
			'fi.type'    => $type,
		);
		self::maybe_include_repeating_fields( $inc_sub, $where );
		$results         = self::getAll( $where, 'field_order', $limit );
		self::$use_cache = true;
		self::include_sub_fields( $results, $inc_sub, $type, $form_id );

		return $results;
	}

	/**
	 * @param int|string $form_id
	 * @param int|string $limit
	 * @param string     $inc_embed
	 * @param string     $inc_repeat
	 *
	 * @return array
	 */
	public static function get_all_for_form( $form_id, $limit = '', $inc_embed = 'exclude', $inc_repeat = 'include' ) {
		if ( ! (int) $form_id ) {
			return array();
		}

		$results = self::get_fields_from_transients( $form_id, compact( 'inc_embed', 'inc_repeat' ) );

		if ( ! empty( $results ) ) {
			if ( empty( $limit ) ) {
				return $results;
			}

			$fields = array();
			$count  = 0;

			foreach ( $results as $result ) {
				++$count;
				$fields[ $result->id ] = $result;

				if ( ! empty( $limit ) && $count >= $limit ) {
					break;
				}
			}

			return $fields;
		}

		self::$use_cache = false;

		$where = array( 'fi.form_id' => absint( $form_id ) );
		self::maybe_include_repeating_fields( $inc_repeat, $where );
		$results = self::getAll( $where, 'field_order', $limit );

		self::$use_cache = true;

		self::include_sub_fields( $results, $inc_embed, 'all', $form_id );

		if ( empty( $limit ) ) {
			self::set_field_transient( $results, $form_id, 0, compact( 'inc_embed', 'inc_repeat' ) );
		}

		return $results;
	}

	/**
	 * If repeating fields should be included, adjust $where accordingly
	 *
	 * @param string $inc_repeat
	 * @param array  $where      Pass by reference.
	 *
	 * @return void
	 */
	private static function maybe_include_repeating_fields( $inc_repeat, &$where ) {
		if ( $inc_repeat === 'include' ) {
			$form_id = $where['fi.form_id'];
			$where[] = array(
				'or'                => 1,
				'fi.form_id'        => $form_id,
				'fr.parent_form_id' => $form_id,
			);
			unset( $where['fi.form_id'] );
		}
	}

	/**
	 * @param array      $results
	 * @param string     $inc_embed
	 * @param string     $type
	 * @param int|string $form_id
	 *
	 * @return void
	 */
	public static function include_sub_fields( &$results, $inc_embed, $type = 'all', $form_id = '' ) {
		$no_sub_forms = empty( $results ) && $type === 'all';

		if ( 'include' != $inc_embed || $no_sub_forms ) {
			return;
		}

		$form_fields         = $results;
		$should_get_subforms = ( $type !== 'all' && $type !== 'form' && ! empty( $form_id ) );

		if ( $should_get_subforms ) {
			$form_fields = self::get_all_types_in_form( $form_id, 'form' );
		}

		$index_offset = 1;

		foreach ( $form_fields as $k => $field ) {
			if ( 'form' != $field->type || ! isset( $field->field_options['form_select'] ) ) {
				continue;
			}

			if ( $type === 'all' ) {
				$sub_fields = self::get_all_for_form( $field->field_options['form_select'] );
			} else {
				$sub_fields = self::get_all_types_in_form( $field->field_options['form_select'], $type );
			}

			if ( ! empty( $sub_fields ) ) {
				$index         = $k + $index_offset;
				$index_offset += count( $sub_fields );
				array_splice( $results, $index, 0, $sub_fields );
			}
			unset( $field, $sub_fields );
		}
	}

	/**
	 * @param array|string $where
	 * @param string       $order_by
	 * @param string       $limit
	 * @param false|int    $blog_id
	 *
	 * @return array|object|null
	 */
	public static function getAll( $where = array(), $order_by = '', $limit = '', $blog_id = false ) {
		$cache_key = FrmAppHelper::maybe_json_encode( $where ) . $order_by . 'l' . $limit . 'b' . $blog_id;

		if ( self::$use_cache ) {
			// make sure old cache doesn't get saved as a transient
			$results = wp_cache_get( $cache_key, 'frm_field' );

			if ( false !== $results ) {
				return wp_unslash( $results );
			}
		}

		global $wpdb;

		if ( $blog_id && is_multisite() ) {
			$prefix = $wpdb->get_blog_prefix( $blog_id );

			$table_name      = $prefix . 'frm_fields';
			$form_table_name = $prefix . 'frm_forms';
		} else {
			$table_name      = $wpdb->prefix . 'frm_fields';
			$form_table_name = $wpdb->prefix . 'frm_forms';
		}

		if ( ! empty( $order_by ) && strpos( $order_by, 'ORDER BY' ) === false ) {
			$order_by = ' ORDER BY ' . $order_by;
		}

		$limit = FrmDb::esc_limit( $limit );

		$query      = "SELECT fi.*, fr.name as form_name FROM {$table_name} fi JOIN {$form_table_name} fr ON fi.form_id=fr.id";
		$query_type = $limit === ' LIMIT 1' || $limit == 1 ? 'row' : 'results';

		if ( is_array( $where ) ) {
			$args    = array(
				'order_by' => $order_by,
				'limit'    => $limit,
			);
			$results = FrmDb::get_var( $table_name . ' fi JOIN ' . $form_table_name . ' fr ON fi.form_id=fr.id', $where, 'fi.*, fr.name as form_name', $args, '', $query_type );
		} else {
			// if the query is not an array, then it has already been prepared
			$query .= FrmDb::prepend_and_or_where( ' WHERE ', $where ) . $order_by . $limit;

			$function_name = $query_type === 'row' ? 'get_row' : 'get_results';
			$results       = $wpdb->$function_name( $query );
		}
		unset( $where );

		self::format_field_results( $results );

		FrmDb::set_cache( $cache_key, $results, 'frm_field' );

		return wp_unslash( $results );
	}

	/**
	 * @since 2.0.8
	 *
	 * @param array|object|null $results Results.
	 *
	 * @return void
	 */
	private static function format_field_results( &$results ) {
		if ( is_array( $results ) ) {
			foreach ( $results as $r_key => $result ) {
				self::add_slashes_to_format_before_setting_field_cache( $result );

				FrmDb::set_cache( $result->id, $result, 'frm_field' );
				FrmDb::set_cache( $result->field_key, $result, 'frm_field' );

				self::prepare_options( $result );
				$results[ $r_key ]->field_options = $result->field_options;
				$results[ $r_key ]->options       = $result->options;
				$results[ $r_key ]->default_value = $result->default_value;

				unset( $r_key, $result );
			}
		} elseif ( $results ) {
			FrmDb::set_cache( $results->id, $results, 'frm_field' );
			FrmDb::set_cache( $results->field_key, $results, 'frm_field' );

			self::prepare_options( $results );
		}
	}

	/**
	 * When $result->field_options is an array and not a serialized string there is only a single backslash.
	 * Cached results are unslashed in FrmField::getAll, so we need to make sure that the cached object has an extra backslash.
	 * Otherwise the backslash is stripped away on load.
	 *
	 * @since 6.15
	 *
	 * @param stdClass $result
	 *
	 * @return void
	 */
	private static function add_slashes_to_format_before_setting_field_cache( $result ) {
		if ( ! isset( $result->field_options ) || ! is_array( $result->field_options ) || empty( $result->field_options['format'] ) ) {
			return;
		}

		$result->field_options['format'] = addslashes( $result->field_options['format'] );
	}

	/**
	 * Unserialize all the serialized field data
	 *
	 * @since 2.0
	 *
	 * @param object $results
	 *
	 * @return void
	 */
	private static function prepare_options( &$results ) {
		FrmAppHelper::unserialize_or_decode( $results->field_options );
		FrmAppHelper::unserialize_or_decode( $results->options );

		// Allow a single box to be checked for the default value.
		$before = $results->default_value;

		$field_object = FrmFieldFactory::get_field_type( $results->type );

		if ( $field_object->should_unserialize_value() ) {
			FrmAppHelper::unserialize_or_decode( $results->default_value );

			if ( $before === $results->default_value && is_string( $before ) && strpos( $before, '["' ) === 0 ) {
				$results->default_value = FrmAppHelper::maybe_json_decode( $results->default_value );
			}
		}
	}

	/**
	 * If a form has too many fields, they won't all save into a single transient.
	 * We'll break them into groups of 200
	 *
	 * @since 2.0.1
	 *
	 * @param int|string $form_id Form ID.
	 * @param array      $args    Additional arguments.
	 *
	 * @return array
	 */
	private static function get_fields_from_transients( $form_id, $args ) {
		$fields = array();
		self::get_next_transient( $fields, 'frm_form_fields_' . $form_id . $args['inc_embed'] . $args['inc_repeat'] );

		return $fields;
	}

	/**
	 * Called by get_fields_from_transients
	 *
	 * @since 2.0.1
	 *
	 * @param array  $fields    Array of fields.
	 * @param string $base_name Base name.
	 * @param int    $next      Next transient number.
	 *
	 * @return void
	 */
	private static function get_next_transient( &$fields, $base_name, $next = 0 ) {
		$name        = $next ? $base_name . $next : $base_name;
		$next_fields = get_transient( $name );

		if ( $next_fields ) {
			$fields = array_merge( $fields, $next_fields );

			if ( count( $next_fields ) >= self::$transient_size ) {
				// if this transient is full, check for another
				++$next;
				self::get_next_transient( $fields, $base_name, $next );
			}
		}
	}

	/**
	 * Save the transients in chunks for large forms
	 *
	 * @since 2.0.1
	 *
	 * @param array      $fields    Array of fields.
	 * @param int|string $form_id   Form ID.
	 * @param int        $next      Next transient number.
	 * @param array      $args      Additional arguments.
	 *
	 * @return void
	 */
	private static function set_field_transient( &$fields, $form_id, $next = 0, $args = array() ) {
		$base_name    = 'frm_form_fields_' . $form_id . $args['inc_embed'] . $args['inc_repeat'];
		$field_chunks = array_chunk( $fields, self::$transient_size );

		foreach ( $field_chunks as $field ) {
			$name = $next ? $base_name . $next : $base_name;
			$set  = set_transient( $name, $field, 60 * 60 * 6 );

			if ( ! $set ) {
				// the transient didn't save
				if ( $name !== $base_name ) {
					// if the first saved an others fail, this will show an incomplete form
					self::delete_form_transient( $form_id );
				}

				return;
			}

			++$next;
		}
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function is_no_save_field( $type ) {
		return in_array( $type, self::no_save_fields(), true );
	}

	/**
	 * @return string[]
	 */
	public static function no_save_fields() {
		return array( 'divider', 'end_divider', 'captcha', 'break', 'html', 'form', 'summary', FrmSubmitHelper::FIELD_TYPE );
	}

	/**
	 * Check if this field can hold an array of values
	 *
	 * @since 2.0.9
	 *
	 * @param array|object $field
	 *
	 * @return bool
	 */
	public static function is_field_with_multiple_values( $field ) {
		if ( ! $field ) {
			return false;
		}

		$field_type = self::get_original_field_type( $field );

		return self::is_checkbox( $field ) ||
			$field_type === 'address' ||
			self::is_multiple_select( $field );
	}

	/**
	 * @since 3.0
	 *
	 * @param array|object $field
	 *
	 * @return string
	 */
	public static function get_field_type( $field ) {
		return is_array( $field ) ? $field['type'] : $field->type;
	}

	/**
	 * @since 3.0
	 *
	 * @param array|object $field
	 *
	 * @return string
	 */
	public static function get_original_field_type( $field ) {
		$field_type    = self::get_field_type( $field );
		$original_type = self::get_option( $field, 'original_type' );

		if ( ! empty( $original_type ) && $original_type != $field_type ) {
			// Check the original type for arrays.
			$field_type = $original_type;
		}

		return $field_type;
	}

	/**
	 * Check if this is a multiselect dropdown field
	 *
	 * @since 2.0.9
	 *
	 * @param array|object $field Field object.
	 *
	 * @return bool
	 */
	public static function is_multiple_select( $field ) {
		$field_type  = self::get_field_type( $field );
		$is_multiple = self::is_option_true( $field, 'multiple' ) && self::is_field_type( $field, 'select' ) && $field_type !== 'hidden';

		return apply_filters( 'frm_is_multiple_select', $is_multiple, $field );
	}

	/**
	 * Check if a field is read only. Read only can be set in the field options,
	 * but disabled with the shortcode options
	 *
	 * @since 2.0.9
	 *
	 * @param array|object $field
	 *
	 * @return bool
	 */
	public static function is_read_only( $field ) {
		global $frm_vars;
		return self::is_option_true( $field, 'read_only' ) && ( ! isset( $frm_vars['readonly'] ) || $frm_vars['readonly'] !== 'disabled' );
	}

	/**
	 * @since 2.0.9
	 *
	 * @param array $field
	 *
	 * @return bool
	 */
	public static function is_required( $field ) {
		$required = $field['required'] != '0';

		/**
		 * @param bool  $required
		 * @param array $field
		 */
		$required = (bool) apply_filters( 'frm_is_field_required', $required, $field );

		return $required;
	}

	/**
	 * @since 2.0.9
	 *
	 * @param array|object $field
	 * @param string       $option
	 *
	 * @return bool
	 */
	public static function is_option_true( $field, $option ) {
		if ( is_array( $field ) ) {
			return self::is_option_true_in_array( $field, $option );
		}
		return self::is_option_true_in_object( $field, $option );
	}

	/**
	 * @since 2.0.9
	 *
	 * @param array|object $field
	 * @param string       $option
	 *
	 * @return bool
	 */
	public static function is_option_empty( $field, $option ) {
		if ( is_array( $field ) ) {
			return self::is_option_empty_in_array( $field, $option );
		}
		return self::is_option_empty_in_object( $field, $option );
	}

	/**
	 * @param array  $field
	 * @param string $option
	 *
	 * @return bool
	 */
	public static function is_option_true_in_array( $field, $option ) {
		return ! empty( $field[ $option ] );
	}

	/**
	 * @param object $field
	 * @param string $option
	 *
	 * @return bool
	 */
	public static function is_option_true_in_object( $field, $option ) {
		return isset( $field->field_options[ $option ] ) && $field->field_options[ $option ];
	}

	/**
	 * @param array  $field
	 * @param string $option
	 *
	 * @return bool
	 */
	public static function is_option_empty_in_array( $field, $option ) {
		return empty( $field[ $option ] );
	}

	/**
	 * @param object $field
	 * @param string $option
	 *
	 * @return bool
	 */
	public static function is_option_empty_in_object( $field, $option ) {
		return empty( $field->field_options[ $option ] );
	}

	/**
	 * @param stdClass $field
	 * @param string   $option
	 *
	 * @return bool
	 */
	public static function is_option_value_in_object( $field, $option ) {
		return isset( $field->field_options[ $option ] ) && $field->field_options[ $option ] != '';
	}

	/**
	 * @since 2.0.18
	 *
	 * @param array|object $field
	 * @param string       $option
	 *
	 * @return mixed
	 */
	public static function get_option( $field, $option ) {
		return is_array( $field ) ? self::get_option_in_array( $field, $option ) : self::get_option_in_object( $field, $option );
	}

	/**
	 * @param array  $field
	 * @param string $option
	 *
	 * @return mixed
	 */
	public static function get_option_in_array( $field, $option ) {
		if ( isset( $field[ $option ] ) ) {
			$this_option = $field[ $option ];
		} elseif ( isset( $field['field_options'] ) && is_array( $field['field_options'] ) && isset( $field['field_options'][ $option ] ) ) {
			$this_option = $field['field_options'][ $option ];
		} else {
			$this_option = '';
		}

		return $this_option;
	}

	/**
	 * @param object $field
	 * @param string $option
	 *
	 * @return mixed
	 */
	public static function get_option_in_object( $field, $option ) {
		return $field->field_options[ $option ] ?? '';
	}

	/**
	 * @since 2.0.09
	 *
	 * @param array|object $field
	 *
	 * @return bool
	 */
	public static function is_repeating_field( $field ) {
		$is_repeating_field = is_array( $field ) ? 'divider' === $field['type'] : 'divider' === $field->type;

		return $is_repeating_field && self::is_option_true( $field, 'repeat' );
	}

	/**
	 * @param string $key
	 *
	 * @return int field id
	 */
	public static function get_id_by_key( $key ) {
		$id = FrmDb::get_var( 'frm_fields', array( 'field_key' => sanitize_title( $key ) ) );

		return (int) $id;
	}

	/**
	 * @param string $id
	 *
	 * @return string|null
	 */
	public static function get_key_by_id( $id ) {
		return FrmDb::get_var( 'frm_fields', array( 'id' => $id ), 'field_key' );
	}

	/**
	 * @param array|object $field
	 *
	 * @return bool
	 */
	public static function is_image( $field ) {
		$type = self::get_field_type( $field );

		return ( $type === 'url' && self::get_option( $field, 'show_image' ) );
	}

	/**
	 * Check if field is radio or Dynamic radio
	 *
	 * @since 3.0
	 *
	 * @param array|object $field
	 *
	 * @return bool true if field type is radio or Dynamic radio
	 */
	public static function is_radio( $field ) {
		return self::is_field_type( $field, 'radio' );
	}

	/**
	 * Check if field is checkbox or Dynamic checkbox
	 *
	 * @since 3.0
	 *
	 * @param array|object $field
	 *
	 * @return bool true if field type is checkbox or Dynamic checkbox
	 */
	public static function is_checkbox( $field ) {
		return self::is_field_type( $field, 'checkbox' );
	}

	/**
	 * Check if field is checkbox or radio
	 *
	 * @since 3.0
	 *
	 * @param array|object $field
	 * @param string       $is_type Options include radio, checkbox, text.
	 *
	 * @return bool true if field type is checkbox or Dynamic checkbox
	 */
	public static function is_field_type( $field, $is_type ) {
		$field_type = self::get_original_field_type( $field );
		$data_type  = self::get_option( $field, 'data_type' );

		$is_field_type = (
			$is_type === $field_type ||
			( 'data' === $field_type && $is_type === $data_type ) ||
			( 'lookup' === $field_type && $is_type === $data_type ) ||
			( 'product' === $field_type && $is_type === $data_type )
		);

		/**
		 * When a field type is checked, allow individual fields
		 * to set the type.
		 *
		 * @since 4.04
		 */
		return apply_filters( 'frm_is_field_type', $is_field_type, compact( 'field', 'is_type' ) );
	}

	/**
	 * Checks if the given field array is a combo field.
	 *
	 * @since 4.10.02
	 *
	 * @param array $field Field array.
	 *
	 * @return bool
	 */
	public static function is_combo_field( $field ) {
		$field_type_obj = FrmFieldFactory::get_field_factory( $field );

		return ! empty( $field_type_obj->is_combo_field );
	}
}
