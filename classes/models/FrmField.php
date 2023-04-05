<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmField {

	public static $use_cache = true;
	public static $transient_size = 200;

	public static function field_selection() {
		$frm_settings   = FrmAppHelper::get_settings();
		$active_captcha = $frm_settings->active_captcha;
		if ( ! FrmFieldCaptcha::should_show_captcha() ) {
			$captcha_name = 'Captcha';
		} elseif ( $active_captcha === 'recaptcha' ) {
			$captcha_name = 'reCAPTCHA';
		} else {
			$captcha_name = 'hCaptcha';
		}
		$fields = array(
			'text'     => array(
				'name' => __( 'Text', 'formidable' ),
				'icon' => 'frm_icon_font frm_text2_icon',
			),
			'textarea' => array(
				'name' => __( 'Paragraph', 'formidable' ),
				'icon' => 'frm_icon_font frm_paragraph_icon',
			),
			'checkbox' => array(
				'name' => __( 'Checkboxes', 'formidable' ),
				'icon' => 'frm_icon_font frm_check_square_icon',
			),
			'radio'    => array(
				'name' => __( 'Radio Buttons', 'formidable' ),
				'icon' => 'frm_icon_font frm_radio_checked_icon',
			),
			'select'   => array(
				'name' => __( 'Dropdown', 'formidable' ),
				'icon' => 'frm_icon_font frm_caret_square_down_icon',
			),
			'email'    => array(
				'name' => __( 'Email', 'formidable' ),
				'icon' => 'frm_icon_font frm_email_icon',
			),
			'url'      => array(
				'name' => __( 'Website/URL', 'formidable' ),
				'icon' => 'frm_icon_font frm_link_icon',
			),
			'number'   => array(
				'name' => __( 'Number', 'formidable' ),
				'icon' => 'frm_icon_font frm_hashtag_icon',
			),
			'name'     => array(
				'name' => __( 'Name', 'formidable' ),
				'icon' => 'frm_icon_font frm_user_name_icon',
			),
			'phone'    => array(
				'name' => __( 'Phone', 'formidable' ),
				'icon' => 'frm_icon_font frm_phone_icon',
			),
			'html'     => array(
				'name' => __( 'HTML', 'formidable' ),
				'icon' => 'frm_icon_font frm_code_icon',
			),
			'hidden'   => array(
				'name' => __( 'Hidden', 'formidable' ),
				'icon' => 'frm_icon_font frm_eye_slash_icon',
			),
			'user_id'  => array(
				'name' => __( 'User ID', 'formidable' ),
				'icon' => 'frm_icon_font frm_user_icon',
			),
			'captcha'  => array(
				'name' => $captcha_name,
				'icon' => 'frm_icon_font frm_shield_check_icon',
			),
		);

		return apply_filters( 'frm_available_fields', $fields );
	}

	public static function pro_field_selection() {
		$images_url = FrmAppHelper::plugin_url() . '/images/';
		$fields     = array(
			'file'           => array(
				'name' => __( 'File Upload', 'formidable' ),
				'icon' => 'frm_icon_font frm_upload_icon',
				'message' => 'Add file uploads to save time and cut down on back-and-forth. Upgrade to Pro to get Upload fields and more.',
			),
			'rte'            => array(
				'name' => __( 'Rich Text', 'formidable' ),
				'icon' => 'frm_icon_font frm_align_right_icon',
			),
			'date'           => array(
				'name' => __( 'Date', 'formidable' ),
				'icon' => 'frm_icon_font frm_calendar_icon',
			),
			'time'           => array(
				'name' => __( 'Time', 'formidable' ),
				'icon' => 'frm_icon_font frm_clock_icon',
			),
			'scale'          => array(
				'name' => __( 'Scale', 'formidable' ),
				'icon' => 'frm_icon_font frm_linear_scale_icon',
				'message' => 'Add a set of radio buttons with whatever range you choose. <img src="' . esc_attr( $images_url ) . 'scale_field.png" alt="Scale Field" />',
			),
			'star'           => array(
				'name' => __( 'Star Rating', 'formidable' ),
				'icon' => 'frm_icon_font frm_star_icon',
			),
			'range'          => array(
				'name' => __( 'Slider', 'formidable' ),
				'icon' => 'frm_icon_font frm_code_commit_icon',
			),
			'toggle'         => array(
				'name' => __( 'Toggle', 'formidable' ),
				'icon' => 'frm_icon_font frm_toggle_on_icon',
			),
			'data'           => array(
				'name' => __( 'Dynamic', 'formidable' ),
				'icon' => 'frm_icon_font frm_sitemap_icon',
				'message' => 'Create relationships between multiple forms. You can link a member to a team, a rating to a product, a comment to a submission, and much more.',
			),
			'lookup'         => array(
				'name' => __( 'Lookup', 'formidable' ),
				'icon' => 'frm_icon_font frm_search_icon',
				'message' => 'Filter the options in the next field and automatically add values to other fields. Upgrade to Pro to get Lookup fields and more. <img src="' . esc_attr( $images_url ) . 'look-up_year-make-model.gif" alt="cascading lookup fields" />',
			),
			'divider|repeat' => array(
				'name' => __( 'Repeater', 'formidable' ),
				'icon' => 'frm_icon_font frm_repeater_icon',
				'message' => 'Allow your visitors to add new sets of fields while filling out forms. Increase conversions while saving building time and server resources. <img src="' . esc_attr( $images_url ) . 'repeatable-section_frontend.gif" alt="Dynamically Add Form Fields with repeatable sections" />',
			),
			'end_divider'    => array(
				'name'        => __( 'Section Buttons', 'formidable' ),
				'switch_from' => 'divider',
			),
			'divider'        => array(
				'name' => __( 'Section', 'formidable' ),
				'icon' => 'frm_icon_font frm_header_icon',
			),
			'break'          => array(
				'name' => __( 'Page Break', 'formidable' ),
				'icon' => 'frm_icon_font frm_page_break_icon',
				'message' => 'Get multi-paged forms with progress bars. Did you know you can upgrade to PRO to unlock multi-step forms with more awesome features?',
			),
			'form'           => array(
				'name' => __( 'Embed Form', 'formidable' ),
				'icon' => 'frm_icon_font frm_file_text_icon',
			),
			'likert'         => array(
				'name'  => __( 'Likert Scale', 'formidable' ),
				'icon'  => 'frm_icon_font frm_likert_scale frm_show_upgrade',
				'addon' => 'surveys',
			),
			'nps'            => array(
				'name'  => __( 'NPS', 'formidable' ),
				'icon'  => 'frm_icon_font frm_nps frm_show_upgrade',
				'addon' => 'surveys',
			),
			'password'       => array(
				'name' => __( 'Password', 'formidable' ),
				'icon' => 'frm_icon_font frm_lock_open_icon',
			),
			'tag'            => array(
				'name' => __( 'Tags', 'formidable' ),
				'icon' => 'frm_icon_font frm_price_tags_icon',
			),
			'credit_card'    => array(
				'name'  => __( 'Credit Card', 'formidable' ),
				'icon'  => 'frm_icon_font frm_credit_card_icon frm_show_upgrade',
				'addon' => 'stripe',
			),
			'address'        => array(
				'name' => __( 'Address', 'formidable' ),
				'icon' => 'frm_icon_font frm_location_icon',
			),
			'summary' => array(
				'name'  => __( 'Summary', 'formidable' ),
				'icon'  => 'frm_icon_font frm_file_text_icon',
				'message' => 'Allow visitors to review their responses before a form is submitted. Upgrade to Pro to get Summary fields and more.',
			),
			'signature' => array(
				'name'  => __( 'Signature', 'formidable' ),
				'icon'  => 'frm_icon_font frm_signature_icon frm_show_upgrade',
				'addon' => 'signature',
			),
			'ai' => array(
				'name'  => __( 'AI', 'formidable' ),
				'icon'  => 'frm_icon_font frm_eye_icon frm_show_upgrade',
				'addon' => 'ai',
				'message' => 'Streamline workflows and reclaim valuable time with the power of AI. You can effortlessly respond to your visitors in real-time with ChatGPT as your automated assistant. Upgrade to Pro and unlock AI-powered fields.',
			),
			'ssa-appointment' => array(
				'name'    => __( 'Appointment', 'formidable' ),
				'icon'    => 'frm_icon_font frm_calendar_icon frm_show_upgrade',
				'require' => 'Simply Schedule Appointments',
				'message' => 'Appointment fields are an integration with <a href="https://simplyscheduleappointments.com/meet/formidable/">Simply Schedule Appointments</a>. Get started now to schedule appointments directly from your forms.
					<img src="' . esc_attr( $images_url ) . 'appointments.png" alt="Scheduling" />',
				'link'    => 'https://simplyscheduleappointments.com/meet/formidable/',
			),
			'product' => array(
				'name'    => __( 'Product', 'formidable' ),
				'icon'    => 'frm_icon_font frm_product_icon',
				'section' => 'pricing',
			),
			'quantity' => array(
				'name'    => __( 'Quantity', 'formidable' ),
				'icon'    => 'frm_icon_font frm_quantity_icon',
				'section' => 'pricing',
			),
			'total' => array(
				'name'    => __( 'Total', 'formidable' ),
				'icon'    => 'frm_icon_font frm_total_icon',
				'section' => 'pricing',
			),
		);

		if ( ! FrmAppHelper::show_new_feature( 'ai' ) ) {
			unset( $fields['ai'] );
		}

		// Since the signature field may be in a different section, don't show it twice.
		$lite_fields = self::field_selection();
		if ( isset( $lite_fields['signature'] ) ) {
			unset( $fields['signature'] );
		}

		return apply_filters( 'frm_pro_available_fields', $fields );
	}

	/**
	 * @since 4.0
	 */
	public static function all_field_selection() {
		$pro_field_selection = self::pro_field_selection();
		return array_merge( $pro_field_selection, self::field_selection() );
	}

	public static function create( $values, $return = true ) {
		global $wpdb, $frm_duplicate_ids;

		$new_values              = array();
		$key                     = isset( $values['field_key'] ) ? $values['field_key'] : $values['name'];
		$new_values['field_key'] = FrmAppHelper::get_unique_key( $key, $wpdb->prefix . 'frm_fields', 'field_key' );

		$values = FrmAppHelper::maybe_filter_array( $values, array( 'name', 'description' ) );

		foreach ( array( 'name', 'description', 'type', 'default_value' ) as $col ) {
			$new_values[ $col ] = $values[ $col ];
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
				if ( $k === 'default_value' ) {
					$new_values[ $k ] = FrmAppHelper::maybe_json_encode( $v );
				} else {
					$new_values[ $k ] = serialize( $v );
				}
			}
			unset( $k, $v );
		}

		$query_results = $wpdb->insert( $wpdb->prefix . 'frm_fields', $new_values );
		$new_id        = 0;
		if ( $query_results ) {
			self::delete_form_transient( $new_values['form_id'] );
			$new_id = $wpdb->insert_id;
		}

		if ( ! $return ) {
			return false;
		}

		if ( $query_results ) {
			if ( isset( $values['id'] ) ) {
				$frm_duplicate_ids[ $values['id'] ] = $new_id;
			}

			return $new_id;
		} else {
			return false;
		}
	}

	/**
	 * @since 5.0.08
	 *
	 * @param array $options
	 * @return array
	 */
	private static function maybe_filter_options( $options ) {
		return FrmAppHelper::maybe_filter_array( $options, array( 'custom_html' ) );
	}

	/**
	 * Process the field duplication.
	 *
	 * @since 5.0.05
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
		}
	}

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
			$values = apply_filters( 'frm_clean_' . $values['type'] . '_field_options_before_update', $values );

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
	 * @param $values array - pass by reference
	 */
	private static function preserve_format_option_backslashes( &$values ) {
		if ( isset( $values['field_options']['format'] ) ) {
			$values['field_options']['format'] = FrmAppHelper::preserve_backslashes( $values['field_options']['format'] );
		}
	}

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
	 */
	public static function maybe_get_field( &$field ) {
		if ( ! is_object( $field ) ) {
			$field = self::getOne( $field );
		}
	}

	/**
	 * @param string|int $id The field id or key.
	 * @param bool $filter When true, run the frm_field filter.
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
	 * @param bool   $filter When true, run the frm_field filter.
	 * @param object $results
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
	 * @param int|string The field id or key
	 * @param mixed $col The name of the column in the fields database table
	 */
	public static function get_type( $id, $col = 'type' ) {
		$field = FrmDb::check_cache( $id, 'frm_field' );
		if ( $field ) {
			$type = $field->{$col};
		} else {
			$where = array(
				'or'        => 1,
				'id'        => $id,
				'field_key' => $id,
			);
			$type  = FrmDb::get_var( 'frm_fields', $where, $col );
		}

		return $type;
	}

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
				$count ++;
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
		}

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
				$count ++;
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
	 * @param array $where - pass by reference
	 */
	private static function maybe_include_repeating_fields( $inc_repeat, &$where ) {
		if ( $inc_repeat == 'include' ) {
			$form_id = $where['fi.form_id'];
			$where[] = array(
				'or'                => 1,
				'fi.form_id'        => $form_id,
				'fr.parent_form_id' => $form_id,
			);
			unset( $where['fi.form_id'] );
		}
	}

	public static function include_sub_fields( &$results, $inc_embed, $type = 'all', $form_id = '' ) {
		$no_sub_forms = empty( $results ) && $type === 'all';
		if ( 'include' != $inc_embed || $no_sub_forms ) {
			return;
		}

		$form_fields = $results;
		$should_get_subforms = ( $type !== 'all' && $type !== 'form' && ! empty( $form_id ) );
		if ( $should_get_subforms ) {
			$form_fields = self::get_all_types_in_form( $form_id, 'form' );
		}

		$index_offset = 1;
		foreach ( $form_fields as $k => $field ) {
			if ( 'form' != $field->type || ! isset( $field->field_options['form_select'] ) ) {
				continue;
			}

			if ( $type == 'all' ) {
				$sub_fields = self::get_all_for_form( $field->field_options['form_select'] );
			} else {
				$sub_fields = self::get_all_types_in_form( $field->field_options['form_select'], $type );
			}

			if ( ! empty( $sub_fields ) ) {
				$index        = $k + $index_offset;
				$index_offset += count( $sub_fields );
				array_splice( $results, $index, 0, $sub_fields );
			}
			unset( $field, $sub_fields );
		}
	}

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
			global $wpmuBaseTablePrefix;
			if ( $wpmuBaseTablePrefix ) {
				$prefix = $wpmuBaseTablePrefix . $blog_id . '_';
			} else {
				$prefix = $wpdb->get_blog_prefix( $blog_id );
			}

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

		$query      = "SELECT fi.*, fr.name as form_name  FROM {$table_name} fi LEFT OUTER JOIN {$form_table_name} fr ON fi.form_id=fr.id";
		$query_type = ( $limit == ' LIMIT 1' || $limit == 1 ) ? 'row' : 'results';

		if ( is_array( $where ) ) {
			$args    = array(
				'order_by' => $order_by,
				'limit'    => $limit,
			);
			$results = FrmDb::get_var( $table_name . ' fi LEFT OUTER JOIN ' . $form_table_name . ' fr ON fi.form_id=fr.id', $where, 'fi.*, fr.name as form_name', $args, '', $query_type );
		} else {
			// if the query is not an array, then it has already been prepared
			$query .= FrmDb::prepend_and_or_where( ' WHERE ', $where ) . $order_by . $limit;

			$function_name = ( $query_type == 'row' ) ? 'get_row' : 'get_results';
			$results       = $wpdb->$function_name( $query );
		}
		unset( $where );

		self::format_field_results( $results );

		FrmDb::set_cache( $cache_key, $results, 'frm_field' );

		return wp_unslash( $results );
	}

	/**
	 * @since 2.0.8
	 */
	private static function format_field_results( &$results ) {
		if ( is_array( $results ) ) {
			foreach ( $results as $r_key => $result ) {
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
	 * Unserialize all the serialized field data
	 *
	 * @since 2.0
	 */
	private static function prepare_options( &$results ) {
		FrmAppHelper::unserialize_or_decode( $results->field_options );
		FrmAppHelper::unserialize_or_decode( $results->options );

		// Allow a single box to be checked for the default value.
		$before = $results->default_value;
		FrmAppHelper::unserialize_or_decode( $results->default_value );
		if ( $before === $results->default_value && ! is_array( $before ) && strpos( $before, '["' ) === 0 ) {
			$results->default_value = FrmAppHelper::maybe_json_decode( $results->default_value );
		}
	}

	/**
	 * If a form has too many fields, thay won't all save into a single transient.
	 * We'll break them into groups of 200
	 *
	 * @since 2.0.1
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
	 */
	private static function get_next_transient( &$fields, $base_name, $next = 0 ) {
		$name        = $next ? $base_name . $next : $base_name;
		$next_fields = get_transient( $name );

		if ( $next_fields ) {
			$fields = array_merge( $fields, $next_fields );

			if ( count( $next_fields ) >= self::$transient_size ) {
				// if this transient is full, check for another
				$next ++;
				self::get_next_transient( $fields, $base_name, $next );
			}
		}
	}

	/**
	 * Save the transients in chunks for large forms
	 *
	 * @since 2.0.1
	 */
	private static function set_field_transient( &$fields, $form_id, $next = 0, $args = array() ) {
		$base_name    = 'frm_form_fields_' . $form_id . $args['inc_embed'] . $args['inc_repeat'];
		$field_chunks = array_chunk( $fields, self::$transient_size );

		foreach ( $field_chunks as $field ) {
			$name = $next ? $base_name . $next : $base_name;
			$set  = set_transient( $name, $field, 60 * 60 * 6 );
			if ( ! $set ) {
				// the transient didn't save
				if ( $name != $base_name ) {
					// if the first saved an others fail, this will show an incmoplete form
					self::delete_form_transient( $form_id );
				}

				return;
			}

			$next ++;
		}
	}

	public static function is_no_save_field( $type ) {
		return in_array( $type, self::no_save_fields() );
	}

	public static function no_save_fields() {
		return array( 'divider', 'end_divider', 'captcha', 'break', 'html', 'form', 'summary' );
	}

	/**
	 * Check if this field can hold an array of values
	 *
	 * @since 2.0.9
	 *
	 * @param array|object $field
	 *
	 * @return boolean
	 */
	public static function is_field_with_multiple_values( $field ) {
		if ( ! $field ) {
			return false;
		}

		$field_type = self::get_original_field_type( $field );

		$is_multi_value_field = (
			self::is_checkbox( $field ) ||
			$field_type == 'address' ||
			self::is_multiple_select( $field )
		);

		return $is_multi_value_field;
	}

	/**
	 * @since 3.0
	 * @return string
	 */
	public static function get_field_type( $field ) {
		return is_array( $field ) ? $field['type'] : $field->type;
	}

	/**
	 * @since 3.0
	 * @return string
	 */
	public static function get_original_field_type( $field ) {
		$field_type    = self::get_field_type( $field );
		$original_type = self::get_option( $field, 'original_type' );

		if ( ! empty( $original_type ) && $original_type != $field_type ) {
			$field_type = $original_type; // check the original type for arrays
		}

		return $field_type;
	}

	/**
	 * Check if this is a multiselect dropdown field
	 *
	 * @since 2.0.9
	 * @return boolean
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
	 */
	public static function is_read_only( $field ) {
		global $frm_vars;

		return ( self::is_option_true( $field, 'read_only' ) && ( ! isset( $frm_vars['readonly'] ) || $frm_vars['readonly'] != 'disabled' ) );
	}

	/**
	 * @since 2.0.9
	 */
	public static function is_required( $field ) {
		$required = ( $field['required'] != '0' );
		$required = apply_filters( 'frm_is_field_required', $required, $field );

		return $required;
	}

	/**
	 * @since 2.0.9
	 */
	public static function is_option_true( $field, $option ) {
		if ( is_array( $field ) ) {
			return self::is_option_true_in_array( $field, $option );
		} else {
			return self::is_option_true_in_object( $field, $option );
		}
	}

	/**
	 * @since 2.0.9
	 */
	public static function is_option_empty( $field, $option ) {
		if ( is_array( $field ) ) {
			return self::is_option_empty_in_array( $field, $option );
		} else {
			return self::is_option_empty_in_object( $field, $option );
		}
	}

	public static function is_option_true_in_array( $field, $option ) {
		return isset( $field[ $option ] ) && $field[ $option ];
	}

	public static function is_option_true_in_object( $field, $option ) {
		return isset( $field->field_options[ $option ] ) && $field->field_options[ $option ];
	}

	public static function is_option_empty_in_array( $field, $option ) {
		return ! isset( $field[ $option ] ) || empty( $field[ $option ] );
	}

	public static function is_option_empty_in_object( $field, $option ) {
		return ! isset( $field->field_options[ $option ] ) || empty( $field->field_options[ $option ] );
	}

	public static function is_option_value_in_object( $field, $option ) {
		return isset( $field->field_options[ $option ] ) && $field->field_options[ $option ] != '';
	}

	/**
	 * @since 2.0.18
	 */
	public static function get_option( $field, $option ) {
		if ( is_array( $field ) ) {
			$option = self::get_option_in_array( $field, $option );
		} else {
			$option = self::get_option_in_object( $field, $option );
		}

		return $option;
	}

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

	public static function get_option_in_object( $field, $option ) {
		return isset( $field->field_options[ $option ] ) ? $field->field_options[ $option ] : '';
	}

	/**
	 * @since 2.0.09
	 */
	public static function is_repeating_field( $field ) {
		if ( is_array( $field ) ) {
			$is_repeating_field = ( 'divider' == $field['type'] );
		} else {
			$is_repeating_field = ( 'divider' == $field->type );
		}

		return ( $is_repeating_field && self::is_option_true( $field, 'repeat' ) );
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
	 * @return null|string
	 */
	public static function get_key_by_id( $id ) {
		return FrmDb::get_var( 'frm_fields', array( 'id' => $id ), 'field_key' );
	}

	public static function is_image( $field ) {
		$type = self::get_field_type( $field );

		return ( $type == 'url' && self::get_option( $field, 'show_image' ) );
	}

	/**
	 * Check if field is radio or Dynamic radio
	 *
	 * @since 3.0
	 *
	 * @param array|object $field
	 *
	 * @return boolean true if field type is radio or Dynamic radio
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
	 * @return boolean true if field type is checkbox or Dynamic checkbox
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
	 * @param string $is_type Options include radio, checkbox, text
	 *
	 * @return boolean true if field type is checkbox or Dynamic checkbox
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
	 * @return bool
	 */
	public static function is_combo_field( $field ) {
		$field_type_obj = FrmFieldFactory::get_field_factory( $field );

		return ! empty( $field_type_obj->is_combo_field );
	}
}
