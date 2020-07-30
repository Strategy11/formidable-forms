<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormsHelper {

	/**
	 * @since 2.2.10
	 */
	public static function form_error_class() {
		return apply_filters( 'frm_form_error_class', 'frm_error_style' );
	}

	public static function get_direct_link( $key, $form = false ) {
		$target_url = esc_url( admin_url( 'admin-ajax.php?action=frm_forms_preview&form=' . $key ) );
		$target_url = apply_filters( 'frm_direct_link', $target_url, $key, $form );

		return $target_url;
	}

	public static function forms_dropdown( $field_name, $field_value = '', $args = array() ) {
		$defaults = array(
			'blank'        => true,
			'field_id'     => false,
			'onchange'     => false,
			'exclude'      => false,
			'class'        => '',
			'inc_children' => 'exclude',
		);
		$args     = wp_parse_args( $args, $defaults );

		if ( ! $args['field_id'] ) {
			$args['field_id'] = $field_name;
		}

		$query = array();
		if ( $args['exclude'] ) {
			$query['id !'] = $args['exclude'];
		}

		$where    = apply_filters( 'frm_forms_dropdown', $query, $field_name );
		$forms    = FrmForm::get_published_forms( $where, 999, $args['inc_children'] );
		$add_html = array();
		self::add_html_attr( $args['onchange'], 'onchange', $add_html );
		self::add_html_attr( $args['class'], 'class', $add_html );

		?>
		<select name="<?php echo esc_attr( $field_name ); ?>"
			id="<?php echo esc_attr( $args['field_id'] ); ?>"
			<?php echo wp_strip_all_tags( implode( ' ', $add_html ) ); // WPCS: XSS ok. ?>>
			<?php if ( $args['blank'] ) { ?>
				<option value=""><?php echo ( $args['blank'] == 1 ) ? ' ' : '- ' . esc_attr( $args['blank'] ) . ' -'; ?></option>
			<?php } ?>
			<?php foreach ( $forms as $form ) { ?>
				<option value="<?php echo esc_attr( $form->id ); ?>" <?php selected( $field_value, $form->id ); ?>>
					<?php echo esc_html( '' === $form->name ? __( '(no title)', 'formidable' ) : FrmAppHelper::truncate( $form->name, 50 ) . ( $form->parent_form_id ? __( ' (child)', 'formidable' ) : '' ) ); ?>
				</option>
			<?php } ?>
		</select>
		<?php
	}

	/**
	 * @param string $class
	 * @param string $param
	 * @param array $add_html
	 *
	 * @since 2.0.6
	 */
	public static function add_html_attr( $class, $param, &$add_html ) {
		if ( ! empty( $class ) ) {
			$add_html[ $param ] = sanitize_title( $param ) . '="' . esc_attr( trim( sanitize_text_field( $class ) ) ) . '"';
		}
	}

	/**
	 * @param string|object $selected - The label for the placeholder, or the form object.
	 */
	public static function form_switcher( $selected = false ) {
		$where = apply_filters( 'frm_forms_dropdown', array(), '' );
		$forms = FrmForm::get_published_forms( $where );

		$args = array(
			'id'   => 0,
			'form' => 0,
		);
		if ( isset( $_GET['id'] ) && ! isset( $_GET['form'] ) ) {
			unset( $args['form'] );
		} elseif ( isset( $_GET['form'] ) && ! isset( $_GET['id'] ) ) {
			unset( $args['id'] );
		}

		$frm_action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		if ( FrmAppHelper::is_admin_page( 'formidable-entries' ) && in_array( $frm_action, array( 'edit', 'show', 'destroy', 'destroy_all' ) ) ) {
			$args['frm_action'] = 'list';
			$args['form']       = 0;
		} elseif ( FrmAppHelper::is_admin_page( 'formidable' ) && in_array( $frm_action, array( 'new', 'duplicate' ) ) ) {
			$args['frm_action'] = 'edit';
		} elseif ( isset( $_GET['post'] ) ) {
			$args['form'] = 0;
			$base         = admin_url( 'edit.php?post_type=frm_display' );
		}

		$form_id = 0;
		if ( is_object( $selected ) ) {
			$form_id  = $selected->id;
			$selected = $selected->name;
		}

		$name = ( $selected === false ) ? __( 'Switch Form', 'formidable' ) : $selected;
		$name = '' === $name ? __( '(no title)', 'formidable' ) : strip_tags( $name );
		$truncated_name = FrmAppHelper::truncate( $name, 25 );

		if ( count( $forms ) < 2 ) {
			?>
			<div id="frm_bs_dropdown">
				<h1>
					<span class="frm_bstooltip" title="<?php echo esc_attr( $truncated_name === $name ? '' : $name ); ?>" data-placement="right">
						<?php echo esc_html( $name ); ?>
					</span>
				</h1>
			</div>
			<?php
			return;
		}
		?>
		<div id="frm_bs_dropdown" class="dropdown <?php echo esc_attr( is_rtl() ? 'pull-right' : 'pull-left' ); ?>">
			<a href="#" id="frm-navbarDrop" class="frm-dropdown-toggle" data-toggle="dropdown">
				<h1>
					<span class="frm_bstooltip" title="<?php echo esc_attr( $truncated_name === $name ? '' : $name ); ?>" data-placement="right">
						<?php echo esc_html( $name ); ?>
					</span>
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown4_icon', array( 'aria-hidden' => 'true' ) ); ?>
				</h1>
			</a>
			<ul class="frm-dropdown-menu frm-on-top frm-inline-modal frm_code_list frm-full-hover" role="menu" aria-labelledby="frm-navbarDrop">
				<?php if ( count( $forms ) > 8 ) { ?>
				<li class="frm-with-search">
					<?php
					FrmAppHelper::show_search_box(
						array(
							'input_id'    => 'dropform',
							'placeholder' => __( 'Search Forms', 'formidable' ),
							'tosearch'    => 'frm-dropdown-form',
						)
					);
					?>
				</li>
				<?php } ?>
				<?php
				foreach ( $forms as $form ) {
					if ( $form->id === $form_id ) {
						// Don't include the selected form in the switcher since it does nothing.
						continue;
					}

					if ( isset( $args['id'] ) ) {
						$args['id'] = $form->id;
					}
					if ( isset( $args['form'] ) ) {
						$args['form'] = $form->id;
					}

					$url       = isset( $base ) ? add_query_arg( $args, $base ) : add_query_arg( $args );
					$form_name = empty( $form->name ) ? __( '(no title)', 'formidable' ) : $form->name;
					?>
					<li class="frm-dropdown-form">
						<a href="<?php echo esc_url( $url ); ?>" tabindex="-1">
							<span class="frm-sub-label">
							<?php
							printf(
								/* translators: %d: Form ID */
								esc_html__( '(ID %d)', 'formidable' ),
								esc_attr( $form->id )
							);
							?>
							</span>
							<?php echo esc_html( $form_name ); ?>
							<span class="frm_hidden"><?php echo esc_html( $form->form_key ); ?></span>
						</a>
					</li>
					<?php
					unset( $form );
				}
				?>
			</ul>
		</div>
		<?php
	}

	/**
	 * @since 3.05
	 * @deprecated 4.0
	 *
	 * @param array $values - The form array
	 */
	public static function builder_submit_button( $values ) {
		FrmDeprecated::builder_submit_button( $values );
	}

	public static function get_sortable_classes( $col, $sort_col, $sort_dir ) {
		echo ( $sort_col == $col ) ? 'sorted' : 'sortable';
		echo ( $sort_col == $col && $sort_dir == 'desc' ) ? ' asc' : ' desc';
	}

	/**
	 * @since 3.0
	 */
	public static function get_field_link_name( $field_type ) {
		if ( is_array( $field_type ) ) {
			$field_label = $field_type['name'];
		} else {
			$field_label = $field_type;
		}

		return $field_label;
	}

	/**
	 * @since 3.0
	 */
	public static function get_field_link_icon( $field_type ) {
		if ( is_array( $field_type ) && isset( $field_type['icon'] ) ) {
			$icon = $field_type['icon'];
		} else {
			$icon = 'frm_icon_font frm_pencil_icon';
		}

		return $icon;
	}

	/**
	 * Get the invalid form error message
	 *
	 * @since 2.02.07
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function get_invalid_error_message( $args ) {
		$settings_args = $args;
		if ( isset( $args['form'] ) ) {
			$settings_args['current_form'] = $args['form']->id;
		}

		$frm_settings = FrmAppHelper::get_settings( $settings_args );
		$invalid_msg = do_shortcode( $frm_settings->invalid_msg );
		return apply_filters( 'frm_invalid_error_message', $invalid_msg, $args );
	}

	public static function get_success_message( $atts ) {
		$message = apply_filters( 'frm_content', $atts['message'], $atts['form'], $atts['entry_id'] );
		$message = do_shortcode( FrmAppHelper::use_wpautop( $message ) );
		$message = '<div class="' . esc_attr( $atts['class'] ) . '" role="status">' . $message . '</div>';

		return $message;
	}

	/**
	 * Used when a form is created
	 */
	public static function setup_new_vars( $values = array() ) {
		global $wpdb;

		if ( ! empty( $values ) ) {
			$post_values = $values;
		} else {
			$values      = array();
			$post_values = isset( $_POST ) ? $_POST : array();
		}

		$defaults = array(
			'name'        => '',
			'description' => '',
		);
		foreach ( $defaults as $var => $default ) {
			if ( ! isset( $values[ $var ] ) ) {
				$values[ $var ] = FrmAppHelper::get_param( $var, $default, 'get', 'sanitize_text_field' );
			}
		}

		$values['description'] = FrmAppHelper::use_wpautop( $values['description'] );

		$defaults = array(
			'form_id'        => '',
			'logged_in'      => '',
			'editable'       => '',
			'is_template'    => 0,
			'status'         => 'published',
			'parent_form_id' => 0,
		);
		foreach ( $defaults as $var => $default ) {
			if ( ! isset( $values[ $var ] ) ) {
				$values[ $var ] = FrmAppHelper::get_param( $var, $default, 'get', 'sanitize_text_field' );
			}
		}
		unset( $defaults );

		if ( ! isset( $values['form_key'] ) ) {
			$values['form_key'] = ( $post_values && isset( $post_values['form_key'] ) ) ? $post_values['form_key'] : FrmAppHelper::get_unique_key( '', $wpdb->prefix . 'frm_forms', 'form_key' );
		}

		$values                 = self::fill_default_opts( $values, false, $post_values );
		$values['custom_style'] = FrmAppHelper::custom_style_value( $post_values );

		return apply_filters( 'frm_setup_new_form_vars', $values );
	}

	/**
	 * Used when editing a form
	 */
	public static function setup_edit_vars( $values, $record, $post_values = array() ) {
		if ( empty( $post_values ) ) {
			$post_values = wp_unslash( $_POST );
		}

		$values['form_key']    = isset( $post_values['form_key'] ) ? $post_values['form_key'] : $record->form_key;
		$values['is_template'] = isset( $post_values['is_template'] ) ? $post_values['is_template'] : $record->is_template;
		$values['status']      = $record->status;

		$values = self::fill_default_opts( $values, $record, $post_values );

		return apply_filters( 'frm_setup_edit_form_vars', $values );
	}

	public static function fill_default_opts( $values, $record, $post_values ) {

		$defaults = self::get_default_opts();
		foreach ( $defaults as $var => $default ) {
			if ( is_array( $default ) ) {
				if ( ! isset( $values[ $var ] ) ) {
					$values[ $var ] = ( $record && isset( $record->options[ $var ] ) ) ? $record->options[ $var ] : array();
				}

				foreach ( $default as $k => $v ) {
					$values[ $var ][ $k ] = ( $post_values && isset( $post_values[ $var ][ $k ] ) ) ? $post_values[ $var ][ $k ] : ( ( $record && isset( $record->options[ $var ] ) && isset( $record->options[ $var ][ $k ] ) ) ? $record->options[ $var ][ $k ] : $v );

					if ( is_array( $v ) ) {
						foreach ( $v as $k1 => $v1 ) {
							$values[ $var ][ $k ][ $k1 ] = ( $post_values && isset( $post_values[ $var ][ $k ][ $k1 ] ) ) ? $post_values[ $var ][ $k ][ $k1 ] : ( ( $record && isset( $record->options[ $var ] ) && isset( $record->options[ $var ][ $k ] ) && isset( $record->options[ $var ][ $k ][ $k1 ] ) ) ? $record->options[ $var ][ $k ][ $k1 ] : $v1 );
							unset( $k1, $v1 );
						}
					}

					unset( $k, $v );
				}
			} else {
				$values[ $var ] = ( $post_values && isset( $post_values['options'][ $var ] ) ) ? $post_values['options'][ $var ] : ( ( $record && isset( $record->options[ $var ] ) ) ? $record->options[ $var ] : $default );
			}

			unset( $var, $default );
		}

		return $values;
	}

	public static function get_default_opts() {
		$frm_settings = FrmAppHelper::get_settings();

		return array(
			'submit_value'   => $frm_settings->submit_value,
			'success_action' => 'message',
			'success_msg'    => $frm_settings->success_msg,
			'show_form'      => 0,
			'akismet'        => '',
			'no_save'        => 0,
			'ajax_load'      => 0,
			'js_validate'    => 0,
			'form_class'     => '',
			'custom_style'   => 1,
			'before_html'    => self::get_default_html( 'before' ),
			'after_html'     => '',
			'submit_html'    => self::get_default_html( 'submit' ),
		);
	}

	/**
	 * @param array $options
	 * @param array $values
	 *
	 * @since 2.0.6
	 */
	public static function fill_form_options( &$options, $values ) {
		$defaults = self::get_default_opts();
		foreach ( $defaults as $var => $default ) {
			$options[ $var ] = isset( $values['options'][ $var ] ) ? $values['options'][ $var ] : $default;
			unset( $var, $default );
		}
	}

	/**
	 * @param string $loc
	 */
	public static function get_default_html( $loc ) {
		if ( $loc == 'submit' ) {
			$draft_link   = self::get_draft_link();
			$default_html = <<<SUBMIT_HTML
<div class="frm_submit">
[if back_button]<button type="submit" name="frm_prev_page" formnovalidate="formnovalidate" class="frm_prev_page" [back_hook]>[back_label]</button>[/if back_button]
<button class="frm_button_submit" type="submit"  [button_action]>[button_label]</button>
$draft_link
</div>
SUBMIT_HTML;
		} elseif ( $loc == 'before' ) {
			$default_html = <<<BEFORE_HTML
<legend class="frm_screen_reader">[form_name]</legend>
[if form_name]<h3 class="frm_form_title">[form_name]</h3>[/if form_name]
[if form_description]<div class="frm_description">[form_description]</div>[/if form_description]
BEFORE_HTML;
		} else {
			$default_html = '';
		}

		return $default_html;
	}

	public static function get_draft_link() {
		$link = '[if save_draft]<a href="#" tabindex="0" class="frm_save_draft" [draft_hook]>[draft_label]</a>[/if save_draft]';

		return $link;
	}

	public static function get_custom_submit( $html, $form, $submit, $form_action, $values ) {
		$button = self::replace_shortcodes( $html, $form, $submit, $form_action, $values );
		if ( ! strpos( $button, '[button_action]' ) ) {
			echo $button; // WPCS: XSS ok.

			return;
		}

		$button_parts = explode( '[button_action]', $button );

		$classes = apply_filters( 'frm_submit_button_class', array(), $form );
		if ( ! empty( $classes ) ) {
			$classes      = implode( ' ', $classes );
			$button_class = ' class="frm_button_submit';
			if ( strpos( $button_parts[0], $button_class ) !== false ) {
				$button_parts[0] = str_replace( $button_class, $button_class . ' ' . esc_attr( $classes ), $button_parts[0] );
			} else {
				$button_parts[0] .= ' class="' . esc_attr( $classes ) . '"';
			}
		}

		echo $button_parts[0]; // WPCS: XSS ok.
		do_action( 'frm_submit_button_action', $form, $form_action );
		echo $button_parts[1]; // WPCS: XSS ok.
	}

	/**
	 * @since 4.0
	 */
	public static function html_shortcodes() {
		$codes = array(
			'id'             => array(
				'label' => __( 'Field ID', 'formidable' ),
				'class' => 'show_field_custom_html',
			),
			'key'            => array(
				'label' => __( 'Field Key', 'formidable' ),
				'class' => 'show_field_custom_html',
			),
			'field_name'     => array(
				'label' => __( 'Field Name', 'formidable' ),
				'class' => 'show_field_custom_html',
			),
			'description'    => array(
				'label' => __( 'Field Description', 'formidable' ),
				'class' => 'show_field_custom_html',
			),
			'label_position' => array(
				'label' => __( 'Label Position', 'formidable' ),
				'class' => 'show_field_custom_html',
			),
			'required_label' => array(
				'label' => __( 'Required Label', 'formidable' ),
				'class' => 'show_field_custom_html',
			),
			'input'          => array(
				'label' => __( 'Input Field', 'formidable' ),
				'class' => 'show_field_custom_html',
			),
			'input opt=1'    => array(
				'label' => __( 'Single Option', 'formidable' ),
				'title' => __( 'Show a single radio or checkbox option by replacing 1 with the order of the option', 'formidable' ),
				'class' => 'show_field_custom_html',
			),
			'input label=0'  => array(
				'label' => __( 'Hide Option Label', 'formidable' ),
				'class' => 'show_field_custom_html',
			),
			'required_class' => array(
				'label' => __( 'Required Class', 'formidable' ),
				'title' => __( 'Add class name if field is required', 'formidable' ),
				'class' => 'show_field_custom_html',
			),
			'error_class'    => array(
				'label' => __( 'Error Class', 'formidable' ),
				'title' => __( 'Add class name if field has an error on form submit', 'formidable' ),
				'class' => 'show_field_custom_html',
			),

			'form_name'        => array(
				'label' => __( 'Form Name', 'formidable' ),
				'class' => 'show_before_html show_after_html',
			),
			'form_description' => array(
				'label' => __( 'Form Description', 'formidable' ),
				'class' => 'show_before_html show_after_html',
			),
			'form_key'         => array(
				'label' => __( 'Form Key', 'formidable' ),
				'class' => 'show_before_html show_after_html',
			),
			'deletelink'       => array(
				'label' => __( 'Delete Entry Link', 'formidable' ),
				'class' => 'show_before_html show_after_html',
			),

			'button_label' => array(
				'label' => __( 'Button Label', 'formidable' ),
				'class' => 'show_submit_html',
			),
			'button_action' => array(
				'label' => __( 'Button Hook', 'formidable' ),
				'class' => 'show_submit_html',
			),
		);

		/**
		 * @since 4.0
		 */
		return apply_filters( 'frm_html_codes', $codes );
	}

	/**
	 * @since 4.0
	 * @param array $args
	 */
	public static function insert_opt_html( $args ) {
		$class  = isset( $args['class'] ) ? $args['class'] : '';
		$fields = FrmField::all_field_selection();
		$field  = isset( $fields[ $args['type'] ] ) ? $fields[ $args['type'] ] : array();

		self::prepare_field_type( $field );

		if ( ! isset( $field['icon'] ) ) {
			$field['icon'] = 'frmfont frm_pencil_icon';
		}

		$possible_email_field = FrmFieldFactory::field_has_property( $args['type'], 'holds_email_values' );
		if ( $possible_email_field ) {
			$class .= ' show_frm_not_email_to';
		}
		?>
		<li class="<?php echo esc_attr( $class ); ?>">
			<a href="javascript:void(0)" class="frmids frm_insert_code"
				data-code="<?php echo esc_attr( $args['id'] ); ?>">
				<span>[<?php echo esc_attr( isset( $args['id_label'] ) ? $args['id_label'] : $args['id'] ); ?>]</span>
				<?php FrmAppHelper::icon_by_class( $field['icon'], array( 'aria-hidden' => 'true' ) ); ?>
				<?php echo esc_attr( FrmAppHelper::truncate( $args['name'], 60 ) ); ?>
			</a>
			<a href="javascript:void(0)" class="frmkeys frm_insert_code frm_hidden"
				data-code="<?php echo esc_attr( $args['key'] ); ?>">
				<span>[<?php echo esc_attr( FrmAppHelper::truncate( isset( $args['key_label'] ) ? $args['key_label'] : $args['key'], 7 ) ); ?>]</span>
				<?php if ( isset( $field['icon'] ) ) { ?>
					<?php FrmAppHelper::icon_by_class( $field['icon'], array( 'aria-hidden' => 'true' ) ); ?>
				<?php } ?>
				<?php echo esc_attr( FrmAppHelper::truncate( $args['name'], 60 ) ); ?>
			</a>
		</li>
		<?php
	}

	/**
	 * @since 4.0
	 * @param array $args
	 */
	public static function insert_code_html( $args ) {
		$defaults = array(
			'class' => '',
			'code'  => '',
			'label' => '',
			'title' => '',
		);

		$args        = array_merge( $defaults, $args );
		$has_tooltip = ! empty( $args['title'] );

		?>
		<li class="<?php echo esc_attr( $args['class'] ); ?>">
			<a href="javascript:void(0)" class="frm_insert_code <?php echo $has_tooltip ? 'frm_help' : ''; ?>"
				<?php echo $has_tooltip ? 'title="' . esc_attr( $args['title'] ) . '"' : ''; ?>
				data-code="<?php echo esc_attr( $args['code'] ); ?>">
				<span>
					[<?php echo esc_attr( FrmAppHelper::truncate( $args['code'], 10 ) ); ?>]
				</span>
				<?php echo esc_attr( FrmAppHelper::truncate( $args['label'], 60 ) ); ?>
			</a>
		</li>
		<?php
	}

	/**
	 * Some field types in add-ons may have been added with only
	 * a field type and name.
	 *
	 * @since 4.0
	 */
	public static function prepare_field_type( &$field ) {
		if ( ! is_array( $field ) ) {
			$field = array(
				'name' => $field,
				'icon' => 'frm_icon_font frm_pencil_icon',
			);
		}
	}

	/**
	 * Automatically add end section fields if they don't exist (2.0 migration)
	 *
	 * @since 2.0
	 *
	 * @param boolean $reset_fields
	 */
	public static function auto_add_end_section_fields( $form, $fields, &$reset_fields ) {
		if ( empty( $fields ) ) {
			return;
		}

		$end_section_values = apply_filters( 'frm_before_field_created', FrmFieldsHelper::setup_new_vars( 'end_divider', $form->id ) );
		$open               = false;
		$prev_order         = false;
		$add_order          = 0;
		$last_field         = false;
		foreach ( $fields as $field ) {
			if ( $prev_order === $field->field_order ) {
				$add_order ++;
			}

			if ( $add_order ) {
				$reset_fields       = true;
				$field->field_order = $field->field_order + $add_order;
				FrmField::update( $field->id, array( 'field_order' => $field->field_order ) );
			}

			switch ( $field->type ) {
				case 'divider':
					// Create an end section if open.
					self::maybe_create_end_section( $open, $reset_fields, $add_order, $end_section_values, $field, 'move' );

					// Mark it open for the next end section.
					$open = true;
					break;
				case 'break':
					self::maybe_create_end_section( $open, $reset_fields, $add_order, $end_section_values, $field, 'move' );
					break;
				case 'end_divider':
					if ( ! $open ) {
						// The section isn't open, so this is an extra field that needs to be removed.
						FrmField::destroy( $field->id );
						$reset_fields = true;
					}

					// There is already an end section here, so there is no need to create one.
					$open = false;
			}
			$prev_order = $field->field_order;

			$last_field = $field;
			unset( $field );
		}

		self::maybe_create_end_section( $open, $reset_fields, $add_order, $end_section_values, $last_field );
	}

	/**
	 * Create end section field if it doesn't exist. This is for migration from < 2.0
	 * Fix any ordering that may be messed up
	 */
	public static function maybe_create_end_section( &$open, &$reset_fields, &$add_order, $end_section_values, $field, $move = 'no' ) {
		if ( ! $open ) {
			return;
		}

		$end_section_values['field_order'] = $field->field_order + 1;

		FrmField::create( $end_section_values );

		if ( $move == 'move' ) {
			// bump the order of current field unless we're at the end of the form
			FrmField::update( $field->id, array( 'field_order' => $field->field_order + 2 ) );
		}

		$add_order    += 2;
		$open         = false;
		$reset_fields = true;
	}

	public static function replace_shortcodes( $html, $form, $title = false, $description = false, $values = array() ) {
		$codes = array(
			'form_name'        => $title,
			'form_description' => $description,
			'entry_key'        => true,
		);
		foreach ( $codes as $code => $show ) {
			if ( $code == 'form_name' ) {
				$replace_with = $form->name;
			} elseif ( $code == 'form_description' ) {
				$replace_with = FrmAppHelper::use_wpautop( $form->description );
			} elseif ( $code == 'entry_key' && isset( $_GET ) && isset( $_GET['entry'] ) ) {
				$replace_with = FrmAppHelper::simple_get( 'entry' );
			} else {
				$replace_with = '';
			}

			FrmShortcodeHelper::remove_inline_conditions( ( FrmAppHelper::is_true( $show ) && $replace_with != '' ), $code, $replace_with, $html );
		}

		//replace [form_key]
		$html = str_replace( '[form_key]', $form->form_key, $html );

		//replace [frmurl]
		$html = str_replace( '[frmurl]', FrmFieldsHelper::dynamic_default_values( 'frmurl' ), $html );

		if ( strpos( $html, '[button_label]' ) ) {
			add_filter( 'frm_submit_button', 'FrmFormsHelper::submit_button_label', 1 );
			$submit_label = apply_filters( 'frm_submit_button', $title, $form );
			$submit_label = esc_attr( do_shortcode( $submit_label ) );
			$html         = str_replace( '[button_label]', $submit_label, $html );
		}

		$html = apply_filters( 'frm_form_replace_shortcodes', $html, $form, $values );

		if ( strpos( $html, '[if back_button]' ) ) {
			$html = preg_replace( '/(\[if\s+back_button\])(.*?)(\[\/if\s+back_button\])/mis', '', $html );
		}

		if ( strpos( $html, '[if save_draft]' ) ) {
			$html = preg_replace( '/(\[if\s+save_draft\])(.*?)(\[\/if\s+save_draft\])/mis', '', $html );
		}

		if ( apply_filters( 'frm_do_html_shortcodes', true ) ) {
			$html = do_shortcode( $html );
		}

		return $html;
	}

	public static function submit_button_label( $submit ) {
		if ( ! $submit || empty( $submit ) ) {
			$frm_settings = FrmAppHelper::get_settings();
			$submit       = $frm_settings->submit_value;
		}

		return $submit;
	}

	/**
	 * If the Formidable styling isn't being loaded,
	 * use inline styling to hide the element
	 *
	 * @since 2.03.05
	 */
	public static function maybe_hide_inline() {
		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->load_style == 'none' ) {
			echo ' style="display:none;"';
		} elseif ( $frm_settings->load_style == 'dynamic' ) {
			FrmStylesController::enqueue_style();
		}
	}

	public static function get_form_style_class( $form = false ) {
		$style = self::get_form_style( $form );
		$class = ' with_frm_style';

		if ( empty( $style ) ) {
			if ( FrmAppHelper::is_admin_page( 'formidable-entries' ) ) {
				return $class;
			} else {
				return;
			}
		}

		// If submit button needs to be inline or centered.
		if ( is_object( $form ) ) {
			$form = $form->options;
		}

		$submit_align = isset( $form['submit_align'] ) ? $form['submit_align'] : '';

		if ( 'inline' === $submit_align ) {
			$class .= ' frm_inline_form';
			$class .= self::maybe_align_fields_top( $form );
		} elseif ( 'center' === $submit_align ) {
			$class .= ' frm_center_submit';
		}

		$class = apply_filters( 'frm_add_form_style_class', $class, $style );

		return $class;
	}

	/**
	 * Returns appropriate class if form has top labels
	 *
	 * @param $form
	 *
	 * @return string
	 */
	private static function maybe_align_fields_top( $form ) {
		return self::form_has_top_labels( $form ) ? ' frm_inline_top' : '';
	}

	/**
	 * Determine if a form has fields with top labels so submit button can be aligned properly
	 *
	 * @param $form
	 *
	 * @return bool
	 */
	private static function form_has_top_labels( $form ) {
		if ( ! isset( $form['fields'] ) ) {
			return false;
		}

		$fields = $form['fields'];
		if ( count( $fields ) <= 0 ) {
			return false;
		}

		$fields = array_reverse( $fields ); // start from the fields closest to the submit button
		foreach ( $fields as $field ) {
			$type      = isset( $field['original_type'] ) ? $field['original_type'] : $field['type'];
			$has_input = FrmFieldFactory::field_has_property( $type, 'has_input' );
			if ( $has_input ) {
				return self::field_has_top_label( $field, $form );
			}
		}

		return false;
	}

	/**
	 * Check if a field's label position is set to "top"
	 *
	 * @param $field
	 * @param $form
	 *
	 * @return bool
	 */
	private static function field_has_top_label( $field, $form ) {
		$label_position = FrmFieldsHelper::label_position( $field['label'], $field, $form );

		return in_array( $label_position, array( 'top', 'inside', 'hidden' ) );
	}

	/**
	 * @param object|string|boolean $form
	 *
	 * @return string
	 */
	public static function get_form_style( $form ) {
		$style = 1;
		if ( empty( $form ) || 'default' == 'form' ) {
			return $style;
		} elseif ( is_object( $form ) && $form->parent_form_id ) {
			// get the parent form if this is a child
			$form = $form->parent_form_id;
		} elseif ( is_array( $form ) && isset( $form['parent_form_id'] ) && $form['parent_form_id'] ) {
			$form = $form['parent_form_id'];
		} elseif ( is_array( $form ) && isset( $form['custom_style'] ) ) {
			$style = $form['custom_style'];
		}

		if ( $form && is_string( $form ) ) {
			$form = FrmForm::getOne( $form );
		}

		$style = ( $form && is_object( $form ) && isset( $form->options['custom_style'] ) ) ? $form->options['custom_style'] : $style;

		return $style;
	}

	/**
	 * Display the validation error messages when an entry is submitted
	 *
	 * @param array $args - includes img, errors
	 *
	 * @since 2.0.6
	 */
	public static function show_errors( $args ) {
		$invalid_msg = self::get_invalid_error_message( $args );

		if ( empty( $invalid_msg ) ) {
			$show_img = false;
		} else {
			echo wp_kses_post( $invalid_msg );
			$show_img = true;
		}

		self::show_error(
			array(
				'img'      => $args['img'],
				'errors'   => $args['errors'],
				'show_img' => $show_img,
			)
		);
	}

	/**
	 * Display the error message in the front-end along with the image if set
	 * The image was removed from the styling settings, but it may still be set with a hook
	 * If the message in the global settings is empty, show every validation message in the error box
	 *
	 * @param array $args - includes img, errors, and show_img
	 *
	 * @since 2.0.6
	 */
	public static function show_error( $args ) {
		// remove any blank messages
		$args['errors'] = array_filter( (array) $args['errors'] );

		$line_break_first = $args['show_img'];
		foreach ( $args['errors'] as $error_key => $error ) {
			if ( $line_break_first && ! is_numeric( $error_key ) && ( $error_key == 'cptch_number' || strpos( $error_key, 'field' ) === 0 ) ) {
				continue;
			}

			$id = str_replace( 'field', 'field_', $error_key );
			echo '<div id="' . esc_attr( $id ) . '_error">';

			if ( $args['show_img'] && ! empty( $args['img'] ) ) {
				echo '<img src="' . esc_attr( $args['img'] ) . '" alt="" />';
			} else {
				$args['show_img'] = true;
			}

			echo wp_kses_post( $error );

			echo '</div>';
		}
	}

	public static function maybe_get_scroll_js( $id ) {
		$offset = apply_filters( 'frm_scroll_offset', 4, array( 'form_id' => $id ) );
		if ( $offset != - 1 ) {
			self::get_scroll_js( $id );
		}
	}

	public static function get_scroll_js( $form_id ) {
		echo '<script type="text/javascript">document.addEventListener(\'DOMContentLoaded\',function(){frmFrontForm.scrollMsg(' . (int) $form_id . ');})</script>';
	}

	/**
	 * @since 3.0
	 */
	public static function actions_dropdown( $atts ) {
		if ( FrmAppHelper::is_admin_page( 'formidable' ) ) {
			$status     = $atts['status'];
			$form_id    = isset( $atts['id'] ) ? $atts['id'] : FrmAppHelper::get_param( 'id', 0, 'get', 'absint' );
			$trash_link = self::delete_trash_info( $form_id, $status );
			$links      = self::get_action_links( $form_id, $status );
			include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/actions-dropdown.php' );
		}
	}

	/**
	 * @since 3.0
	 */
	public static function get_action_links( $form_id, $form ) {
		if ( ! is_object( $form ) ) {
			$form = FrmForm::getOne( $form_id );
		}

		$actions     = array();
		$trash_links = self::delete_trash_links( $form_id );
		if ( 'trash' == $form->status ) {
			$actions['restore'] = $trash_links['restore'];

			if ( current_user_can( 'frm_delete_forms' ) ) {
				$actions['trash'] = $trash_links['delete'];
			}
		} elseif ( current_user_can( 'frm_edit_forms' ) ) {
			$duplicate_link = '?page=formidable&frm_action=duplicate&id=' . $form_id;
			if ( $form->is_template ) {
				$actions['frm_duplicate'] = array(
					'url'   => wp_nonce_url( $duplicate_link ),
					'label' => __( 'Create Form from Template', 'formidable' ),
					'icon'  => 'frm_icon_font frm_clone_icon',
				);
			} else {
				$actions['duplicate'] = array(
					'url'   => wp_nonce_url( $duplicate_link ),
					'label' => __( 'Duplicate Form', 'formidable' ),
					'icon'  => 'frm_icon_font frm_clone_icon',
				);
			}

			$actions['trash'] = self::delete_trash_info( $form_id, $form->status );
		}

		return $actions;
	}

	public static function edit_form_link( $form_id ) {
		if ( is_object( $form_id ) ) {
			$form    = $form_id;
			$name    = $form->name;
			$form_id = $form->id;
		} else {
			$name = FrmForm::getName( $form_id );
		}

		if ( $form_id ) {
			$val = '<a href="' . esc_url( FrmForm::get_edit_link( $form_id ) ) . '">' . ( '' == $name ? __( '(no title)', 'formidable' ) : FrmAppHelper::truncate( $name, 40 ) ) . '</a>';
		} else {
			$val = '';
		}

		return $val;
	}

	public static function delete_trash_link( $id, $status, $length = 'label' ) {
		$link_details = self::delete_trash_info( $id, $status );

		return self::format_link_html( $link_details, $length );
	}

	/**
	 * @since 3.0
	 */
	public static function format_link_html( $link_details, $length = 'label' ) {
		$link = '';
		if ( ! empty( $link_details ) ) {
			$link = '<a href="' . esc_url( $link_details['url'] ) . '" class="frm-trash-link"';
			if ( isset( $link_details['data'] ) ) {
				foreach ( $link_details['data'] as $data => $value ) {
					$link .= ' data-' . esc_attr( $data ) . '="' . esc_attr( $value ) . '"';
				}
			} elseif ( isset( $link_details['confirm'] ) ) {
				$link .= ' onclick="return confirm(\'' . esc_attr( $link_details['confirm'] ) . '\')"';
			}

			$label = ( isset( $link_details[ $length ] ) ? $link_details[ $length ] : $link_details['label'] );
			if ( $length == 'icon' && isset( $link_details[ $length ] ) ) {
				$label = '<span class="' . $label . '" title="' . esc_attr( $link_details['label'] ) . '" aria-hidden="true"></span>';
				$link  .= ' aria-label="' . esc_attr( $link_details['label'] ) . '"';
			}

			$link .= '>' . $label . '</a>';
		}

		return $link;
	}

	/**
	 * @since 3.0
	 */
	public static function delete_trash_info( $id, $status ) {
		$labels = self::delete_trash_links( $id );

		if ( 'trash' == $status ) {
			$info = $labels['restore'];
		} elseif ( current_user_can( 'frm_delete_forms' ) ) {
			if ( EMPTY_TRASH_DAYS ) {
				$info = $labels['trash'];
			} else {
				$info = $labels['delete'];
			}
		} else {
			$info = array();
		}

		return $info;
	}

	/**
	 * @since 3.0
	 */
	private static function delete_trash_links( $id ) {
		$current_page = FrmAppHelper::get_simple_request( array( 'param' => 'form_type' ) );
		$base_url     = '?page=formidable&form_type=' . $current_page . '&id=' . $id;

		return array(
			'restore' => array(
				'label' => __( 'Restore from Trash', 'formidable' ),
				'short' => __( 'Restore', 'formidable' ),
				'url'   => wp_nonce_url( $base_url . '&frm_action=untrash', 'untrash_form_' . absint( $id ) ),
			),
			'trash'   => array(
				'label' => __( 'Move Form to Trash', 'formidable' ),
				'short' => __( 'Trash', 'formidable' ),
				'url'   => wp_nonce_url( $base_url . '&frm_action=trash', 'trash_form_' . absint( $id ) ),
				'icon'  => 'frm_icon_font frm_delete_icon',
				'data'  => array( 'frmverify' => __( 'Do you want to move this form to the trash?', 'formidable' ) ),
			),
			'delete'  => array(
				'label'   => __( 'Delete Permanently', 'formidable' ),
				'short'   => __( 'Delete', 'formidable' ),
				'url'     => wp_nonce_url( $base_url . '&frm_action=destroy', 'destroy_form_' . absint( $id ) ),
				'confirm' => __( 'Are you sure you want to delete this form and all its entries?', 'formidable' ),
				'icon'    => 'frm_icon_font frm_delete_icon',
				'data'    => array( 'frmverify' => __( 'This will permanently delete the form and all its entries. This is irreversible. Are you sure you want to continue?', 'formidable' ) ),
			),
		);
	}

	/**
	 * @since 3.0
	 */
	public static function css_classes() {
		$classes = array(
			'frm_total'      => array(
				'label' => __( 'Total', 'formidable' ),
				'title' => __( 'Add this to a read-only field to display the text in bold without a border or background.', 'formidable' ),
			),
			'frm_total_big'  => array(
				'label' => __( 'Big Total', 'formidable' ),
				'title' => __( 'Add this to a read-only field to display the text in large, bold text without a border or background.', 'formidable' ),
			),
			'frm_scroll_box' => array(
				'label' => __( 'Scroll Box', 'formidable' ),
				'title' => __( 'If you have many checkbox or radio button options, you may add this class to allow your user to easily scroll through the options. Or add a scrolling area around content in an HTML field.', 'formidable' ),
			),
			'frm_first'      => array(
				'label' => __( 'First', 'formidable' ),
				'title' => __( 'Add this to the first field in each row along with a width. ie frm_first frm4', 'formidable' ),
			),
			'frm_alignright' => __( 'Right', 'formidable' ),
			'frm_grid_first' => __( 'First Grid Row', 'formidable' ),
			'frm_grid'       => __( 'Even Grid Row', 'formidable' ),
			'frm_grid_odd'   => __( 'Odd Grid Row', 'formidable' ),
			'frm_color_block' => array(
				'label' => __( 'Color Block', 'formidable' ),
				'title' => __( 'Add a background color to the field or section.', 'formidable' ),
			),
			'frm_capitalize' => array(
				'label' => __( 'Capitalize', 'formidable' ),
				'title' => __( 'Automatically capitalize the first letter in each word.', 'formidable' ),
			),
		);

		return apply_filters( 'frm_layout_classes', $classes );
	}

	public static function grid_classes() {
		$base = array(
			'frm_half' => '1/2',

			'frm_third'      => '1/3',
			'frm_two_thirds' => '2/3',

			'frm_fourth'        => '1/4',
			'frm_three_fourths' => '3/4',
		);

		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->old_css ) {
			$classes = array(
				'frm_sixth' => '1/6',
				'frm10'     => '5/6',

				'frm_full' => '100%',
			);
		} else {
			$classes = array(
				'frm_sixth' => '1/6',
				'frm10'     => '5/6',
				'frm12' => '100%',
			);
		}

		return array_merge( $base, $classes );
	}

	/**
	 * @since 3.0
	 */
	public static function style_class_label( $style, $class ) {
		$label = '';
		if ( empty( $style ) ) {
			$label = $class;
		} elseif ( ! is_array( $style ) ) {
			$label = $style;
		} elseif ( isset( $style['label'] ) ) {
			$label = $style['label'];
		}

		return $label;
	}

	public static function status_nice_name( $status ) {
		$nice_names = array(
			'draft'   => __( 'Draft', 'formidable' ),
			'trash'   => __( 'Trash', 'formidable' ),
			'publish' => __( 'Published', 'formidable' ),
		);

		if ( ! in_array( $status, array_keys( $nice_names ) ) ) {
			$status = 'publish';
		}

		$name = $nice_names[ $status ];

		return $name;
	}

	public static function template_icon( $categories ) {
		$ignore     = self::ignore_template_categories();
		$categories = array_diff( $categories, $ignore );

		$icons = array(
			'WooCommerce'       => array( 'woocommerce', 'var(--purple)' ),
			'Post'              => array( 'wordpress', 'rgb(0,160,210)' ),
			'User Registration' => array( 'register', 'var(--pink)' ),
			'PayPal'            => array( 'paypal' ),
			'Stripe'            => array( 'credit_card', 'var(--green)' ),
			'Twilio'            => array( 'sms', 'rgb(0,160,210)' ),
			'Calculator'        => array( 'calculator', 'var(--orange)' ),
			'Contact Form'      => array( 'address_card' ),
			'Survey'            => array( 'align_right', 'var(--pink)' ),
			'Application Form'  => array( 'align_right', 'rgb(0,160,210)' ),
			''                  => array( 'align_right' ),
		);

		$icon = $icons[''];

		if ( count( $categories ) === 1 ) {
			$category = reset( $categories );
			$icon     = isset( $icons[ $category ] ) ? $icons[ $category ] : $icon;
		} elseif ( ! empty( $categories ) ) {
			foreach ( $icons as $cat => $icon ) {
				if ( ! in_array( $cat, $categories ) ) {
					unset( $icons[ $cat ] );
				}
			}
			$icon = reset( $icons );
		}

		echo '<span class="frm-inner-circle" ' . ( isset( $icon[1] ) ? 'style="background-color:' . esc_attr( $icon[1] ) : '' ) . '">';
		FrmAppHelper::icon_by_class( 'frmfont frm_' . $icon[0] . '_icon' );
		echo '<span class="frm_hidden">';
		FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon' );
		echo '</span>';
		echo '</span>';
	}

	/**
	 * @since 4.03.01
	 */
	public static function ignore_template_categories() {
		return array( 'Business', 'Elite', 'Personal', 'Creator', 'Basic', 'free' );
	}

	/**
	 * @since 4.02
	 */
	public static function get_template_install_link( $template, $args ) {
		$defaults = array(
			'class' => 'install-now',
			'href'  => 'href',
			'atts'  => true,
		);

		if ( isset( $template['url'] ) && ! empty( $template['url'] ) ) {
			$link = array(
				'url'   => $template['url'],
				'label' => __( 'Create Form', 'formidable' ),
				'class' => 'frm-install-template',
				'href'  => 'rel',
				'atts'  => '',
			);
		} elseif ( self::plan_is_allowed( $args ) ) {
			$link = array(
				'url'   => FrmAppHelper::admin_upgrade_link( 'addons', 'account/downloads/' ) . '&utm_content=' . $template['slug'],
				'label' => __( 'Renew', 'formidable' ),
			);
		} else {
			$link = array(
				'url'   => $args['pricing'],
				'label' => __( 'Upgrade', 'formidable' ),
			);
		}

		return array_merge( $defaults, $link );
	}

	/**
	 * Is the template included with the license type?
	 *
	 * @since 4.02.02
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	private static function plan_is_allowed( $args ) {
		if ( empty( $args['license_type'] ) ) {
			return false;
		}

		$included = $args['license_type'] === strtolower( $args['plan_required'] );

		$plans = array( 'free', 'personal', 'business', 'elite' );
		if ( $included || ! in_array( strtolower( $args['plan_required'] ), $plans ) ) {
			return $included;
		}

		foreach ( $plans as $plan ) {
			if ( $included || $plan === $args['license_type'] ) {
				break;
			}
			$included = $plan === strtolower( $args['plan_required'] );
		}

		return $included;
	}

	/**
	 * @since 4.02
	 */
	public static function template_install_html( $link, $class = '' ) {
		$link['class'] .= ' ' . $class;
		echo '<a ' . esc_attr( $link['href'] ) . '="' . esc_url( $link['url'] ) . '" class="' . esc_attr( $link['class'] ) . ' " aria-label="' . esc_attr( $link['label'] ) . '"' . ( $link['atts'] ? ' target="_blank" rel="noopener"' : '' ) . '>';
	}

	/**
	 * If a template or add-on cannot be installed, show a message
	 * about which plan is required.
	 *
	 * @since 4.0
	 */
	public static function show_plan_required( $requires, $link ) {
		if ( empty( $requires ) ) {
			return;
		}

		?>
		<p class="frm_plan_required">
			<?php esc_html_e( 'License plan required:', 'formidable' ); ?>
			<a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener">
				<?php echo esc_html( $requires ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * @since 4.0
	 */
	public static function get_plan_required( &$item ) {
		if ( ! isset( $item['categories'] ) || ( isset( $item['url'] ) && ! empty( $item['url'] ) ) ) {
			return false;
		}

		$plans = array( 'free', 'Basic', 'Personal', 'Business', 'Elite' );

		foreach ( $item['categories'] as $k => $category ) {
			if ( in_array( $category, $plans ) ) {
				unset( $item['categories'][ $k ] );
				if ( $category === 'Personal' ) {
					// Show the current package name.
					$category = 'Basic';
				}
				return $category;
			}
		}

		return false;
	}

	/**
	 * Checks for warnings to be displayed after form settings are saved.
	 *
	 * @since 4.04
	 *
	 * @param array $values The $_POST array, which contains values submitted in a form.
	 *
	 * @return array An array of warnings or an empty array.
	 */
	public static function check_for_warnings( $values ) {
		$warnings = array();

		$redirect_warning = self::check_redirect_url_for_unsafe_params( $values );

		if ( $redirect_warning ) {
			$warnings[] = $redirect_warning;
		}

		return apply_filters( 'frm_check_for_warnings', $warnings, $values );
	}

	/**
	 * Checks the redirect URL for params whose names are reserved words.
	 *
	 * @since 4.04
	 *
	 * @param array $values The $_POST array, which contains the values submitted in a form.
	 *
	 * @return bool|string A warning message about unsafe params or false.
	 */
	private static function check_redirect_url_for_unsafe_params( $values ) {
		if ( ! isset( $values['options'] ) ) {
			return false;
		}

		$options = $values['options'];
		FrmAppHelper::sanitize_with_html( $options );

		if ( ( ! isset( $options['success_action'] ) ) || $options['success_action'] !== 'redirect' || ! isset( $options['success_url'] ) ) {
			return false;
		}

		$unsafe_params_in_redirect = self::get_unsafe_params( $options['success_url'] );

		return self::create_unsafe_param_warning( $unsafe_params_in_redirect );
	}

	/**
	 * Returns an array of params whose names are reserved words in the specified URL.
	 *
	 * @since 4.04
	 *
	 * @param string $url The URL whose params are being checked.
	 *
	 * @return array An array of params whose names are reserved words or an empty array.
	 */
	private static function get_unsafe_params( $url ) {
		$redirect_components = parse_url( $url );
		if ( empty( $redirect_components['query'] ) ) {
			return array();
		}
		parse_str( $redirect_components['query'], $redirect_params );
		$redirect_param_names      = array_keys( $redirect_params );
		$reserved_words            = self::reserved_words();
		$unsafe_params_in_redirect = array_intersect( $redirect_param_names, $reserved_words );

		return array_values( $unsafe_params_in_redirect );
	}

	/**
	 * Returns a warning if reserved words have been used as param names in the redirect URL.
	 *
	 * @since 4.04
	 *
	 * @param array $unsafe_params_in_redirect Array of params from the redirect URL whose names are reserved words.
	 *
	 * @return bool|string A string with an unsafe param message or false.
	 */
	private static function create_unsafe_param_warning( $unsafe_params_in_redirect ) {
		$count                = count( $unsafe_params_in_redirect );
		$caution              = esc_html__( 'Is this intentional?', 'formidable' );
		$reserved_words_intro = esc_html__( 'See the list of reserved words in WordPress.', 'formidable' );
		$reserved_words_link  = '<a href="https://codex.wordpress.org/WordPress_Query_Vars" target="_blank"> ' . $reserved_words_intro . '</a>';

		if ( $count === 0 ) {
			return false;
		}

		if ( $count == 1 ) {
			/* translators: %s: the name of a single parameter in the redirect URL */
			return sprintf( esc_html__( 'The redirect URL is using the parameter "%s", which is reserved by WordPress. ', 'formidable' ), $unsafe_params_in_redirect[0] ) . $caution . $reserved_words_link;
		}

		$unsafe_params_string = implode( '", "', $unsafe_params_in_redirect );

		/* translators: %s: the names of two or more parameters in the redirect URL, separated by commas */
		return sprintf( esc_html__( 'The redirect URL is using the parameters "%s", which are reserved by WordPress. ', 'formidable' ), $unsafe_params_string ) . $caution . $reserved_words_link;
	}

	/**
	 * Returns an array of common reserved words in WordPress.
	 *
	 * An edited list of reserved terms from the Codex.
	 * https://codex.wordpress.org/Reserved_Terms
	 *
	 * @since 4.04
	 *
	 * @return array Array of WordPress reserved words.
	 */
	public static function reserved_words() {
		return array(
			'id',
			'attachment',
			'author',
			'author_name',
			'calendar',
			'cat',
			'category',
			'category_name',
			'cpage',
			'custom',
			'day',
			'date',
			'error',
			'feed',
			'hour',
			'm',
			'minute',
			'more',
			'name',
			'order',
			'p',
			'page',
			'page_id',
			'paged',
			'pb',
			'post',
			'posts',
			'preview',
			's',
			'search',
			'second',
			'sentence',
			'tag',
			'taxonomy',
			'tb',
			'term',
			'terms',
			'theme',
			'title',
			'type',
			'w',
			'year',
		);
	}
}
