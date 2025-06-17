<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmHoneypot extends FrmValidate {

	/**
	 * Track the printed selectors so we do not print the same CSS twice.
	 *
	 * @since 6.22
	 * @var array
	 */
	private static $printed_honeypot_selectors = array();

	/**
	 * Option type.
	 *
	 * @since 6.21
	 *
	 * @var string
	 */
	protected $option_type = 'global';

	/**
	 * @return string
	 */
	protected function get_option_key() {
		return 'honeypot';
	}

	private static function is_enabled() {
		$frm_settings = FrmAppHelper::get_settings();
		return $frm_settings->honeypot;
	}

	/**
	 * @return bool
	 */
	public function validate() {
		if ( ! $this->is_option_on() || ! $this->check_honeypot_filter() ) {
			// never flag as honeypot spam if disabled.
			return true;
		}
		return ! $this->is_honeypot_spam();
	}

	/**
	 * @return bool
	 */
	private function is_honeypot_spam() {
		$is_honeypot_spam = $this->is_legacy_honeypot_spam();
		if ( ! $is_honeypot_spam ) {

			$field_id = $this->get_honeypot_field_id();
			if ( ! $field_id ) {
				return false;
			}

			$value            = $this->get_honeypot_field_value( $field_id );
			$is_honeypot_spam = '' !== $value;
		}

		$atts = array(
			'form' => $this->get_form(),
		);

		/**
		 * Filters the honeypot spam check.
		 *
		 * @param bool  $is_honeypot_spam Set to `true` if is spam.
		 * @param array $atts             Contains `form` and `fields`.
		 */
		return apply_filters( 'frm_process_honeypot', $is_honeypot_spam, $atts );
	}

	/**
	 * Check the old frm_verify key. We'll continue to consider any entry with an frm_verify value as spam.
	 *
	 * @return bool
	 */
	private function is_legacy_honeypot_spam() {
		$legacy_honeypot_value = FrmAppHelper::get_param( 'frm_verify', '', 'get', 'sanitize_text_field' );
		return '' !== $legacy_honeypot_value;
	}

	/**
	 * @return mixed either true, or false.
	 */
	private function check_honeypot_filter() {
		$form = $this->get_form();
		return apply_filters( 'frm_run_honeypot', true, compact( 'form' ) );
	}

	/**
	 * @param int $form_id Form ID.
	 *
	 * @return void
	 */
	public static function maybe_render_field( $form_id ) {
		$honeypot = new self( $form_id );
		if ( ! $honeypot->should_render_field() ) {
			return;
		}

		$max_field_id = FrmDb::get_var(
			'frm_fields',
			array(),
			'id',
			array(
				'order_by' => 'id DESC',
			)
		);

		global $frm_vars;
		$offset            = isset( $frm_vars['honeypot_selectors'] ) ? count( $frm_vars['honeypot_selectors'] ) + 1 : 1;
		$honeypot_field_id = $max_field_id ? $max_field_id + $offset : $offset;

		$class = class_exists( 'FrmProFormState' ) ? 'FrmProFormState' : 'FrmFormState';
		$class::set_initial_value( 'honeypot_field_id', $honeypot_field_id );

		$honeypot->render_field( $honeypot_field_id );
		self::maybe_print_honeypot_css();
	}

	/**
	 * Maybe print honeypot JS.
	 *
	 * @since 6.21
	 */
	public static function maybe_print_honeypot_js() {
		if ( FrmAppHelper::is_admin() || ! self::is_enabled() ) {
			return;
		}

		$css = self::get_honeypot_field_css();
		if ( ! $css ) {
			return;
		}

		// There must be no empty lines inside the script. Otherwise, wpautop adds <p> tags which break script execution.
		printf(
			"<script>
				( function() {
					const style = document.createElement( 'style' );
					style.appendChild( document.createTextNode( '%s' ) );
					document.head.appendChild( style );
					document.currentScript?.remove();
				} )();
			</script>",
			esc_js( $css )
		);

		global $frm_vars;
		self::$printed_honeypot_selectors = $frm_vars['honeypot_selectors'];
	}

	/**
	 * Maybe print honeypot CSS in case JS doesn't run.
	 *
	 * @since 6.21
	 */
	public static function maybe_print_honeypot_css() {
		// Print the CSS if form is loaded by API.
		if ( ! FrmFormsHelper::form_is_loaded_by_api() ) {
			return;
		}

		$css = self::get_honeypot_field_css();
		if ( $css ) {
			echo '<style>' . esc_html( $css ) . '</style>';
		}
	}

	/**
	 * Gets honeypot field CSS.
	 *
	 * @return string
	 */
	private static function get_honeypot_field_css() {
		global $frm_vars;
		if ( empty( $frm_vars['honeypot_selectors'] ) ) {
			return '';
		}

		$selectors = $frm_vars['honeypot_selectors'];
		if ( self::$printed_honeypot_selectors ) {
			$selectors = array_diff( $selectors, self::$printed_honeypot_selectors );
			if ( ! $selectors ) {
				return '';
			}
		}

		return sprintf(
			'%s {visibility:hidden;overflow:hidden;width:0;height:0;position:absolute;}',
			implode( ',', $selectors )
		);
	}

	/**
	 * @return bool
	 */
	public function should_render_field() {
		return $this->is_option_on() && $this->check_honeypot_filter();
	}

	/**
	 * @param int $honeypot_field_id
	 * @return void
	 */
	public function render_field( $honeypot_field_id = 0 ) {
		if ( ! $honeypot_field_id ) {
			return;
		}

		$field_id    = $honeypot_field_id;
		$field_key   = $this->get_honeypot_field_key();
		$input_attrs = array(
			'id'    => 'field_' . $field_key,
			'type'  => 'text',
			'class' => 'frm_form_field form-field frm_verify',
			'name'  => 'item_meta[' . $field_id . ']',
			'value' => $this->get_honeypot_field_value( $field_id ),
		);

		$container_id = 'frm_field_' . $field_id . '_container';
		$this->track_html_id( $container_id );
		?>
		<div id="<?php echo esc_attr( $container_id ); ?>">
			<label for="<?php echo esc_attr( $input_attrs['id'] ); ?>" <?php FrmFormsHelper::maybe_hide_inline(); ?>>
				<?php esc_html_e( 'If you are human, leave this field blank.', 'formidable' ); ?>
			</label>
			<input <?php FrmAppHelper::array_to_html_params( $input_attrs, true ); ?> <?php FrmFormsHelper::maybe_hide_inline(); ?> />
		</div>
		<?php
	}

	private function track_html_id( $html_id ) {
		global $frm_vars;
		if ( ! isset( $frm_vars['honeypot_selectors'] ) ) {
			$frm_vars['honeypot_selectors'] = array();
		}

		$frm_vars['honeypot_selectors'][] = '#' . $html_id;
	}

	private function get_honeypot_field_id() {
		$class             = class_exists( 'FrmProFormState' ) ? 'FrmProFormState' : 'FrmFormState';
		$honeypot_field_id = $class::get_from_request( 'honeypot_field_id', 0 );
		return $honeypot_field_id;
	}

	private function get_honeypot_field_key() {
		return FrmAppHelper::generate_new_key( 5 );
	}

	/**
	 * Gets honeypot field value.
	 *
	 * @param string $field_id Field ID.
	 *
	 * @return string
	 */
	private function get_honeypot_field_value( $field_id ) {
		$item_meta = FrmAppHelper::get_simple_request(
			array(
				'param'   => 'item_meta',
				'default' => array(),
				'type'    => 'post',
			)
		);

		if ( ! $item_meta || ! is_array( $item_meta ) ) {
			return '';
		}

		return isset( $item_meta[ $field_id ] ) ? $item_meta[ $field_id ] : '';
	}

	/**
	 * Generate a random class name for our honeypot so it is less easy to detect.
	 *
	 * @return string The generated class name.
	 */
	public static function generate_class_name() {
		$class_name = self::get_honeypot_class_name();
		if ( 'frm_verify' !== $class_name ) {
			// Re-use the option.
			// We can't generate a new class too often or the field may not be hidden.
			return $class_name;
		}

		$prefix     = 'frm__';
		$class_name = $prefix . uniqid();
		update_option( 'frm_honeypot_class', $class_name );
		return $class_name;
	}

	/**
	 * @return string The current class name to use the for Honeypot field.
	 */
	private static function get_honeypot_class_name() {
		$option = get_option( 'frm_honeypot_class' );
		if ( ! is_string( $option ) ) {
			// For backward compatibility use the old class name.
			return 'frm_verify';
		}
		return $option;
	}
}
