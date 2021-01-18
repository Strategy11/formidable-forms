<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * A class for the field value selector
 * Used in field conditional logic, action conditional logic, MailChimp action, etc.
 *
 * @since 2.03.05
 */
class FrmFieldValueSelector {

	/**
	 * @var int
	 *
	 * @since 2.03.05
	 */
	protected $field_id = 0;

	/**
	 * @var string
	 *
	 * @since 2.03.05
	 */
	protected $field_key = '';

	/**
	 * @var null
	 *
	 * @since 2.03.05
	 */
	protected $field_settings = null;

	/**
	 * @var array
	 *
	 * @since 2.03.05
	 */
	protected $options = array();

	/**
	 * @var string
	 *
	 * @since 2.03.05
	 */
	protected $html_name = '';

	/**
	 * @var string
	 *
	 * @since 2.03.05
	 */
	protected $value = '';

	/**
	 * @var string
	 *
	 * @since 2.03.05
	 */
	protected $source = 'unknown';

	/**
	 * @var string
	 *
	 * @since 2.03.05
	 */
	protected $blank_option_label = '';

	/**
	 * @var object
	 *
	 * @since 2.03.05
	 */
	protected $db_row = null;

	/**
	 * FrmFieldValueSelector constructor
	 *
	 * @param int|string $field_id
	 */
	public function __construct( $field_id, $args ) {
		$this->set_html_name( $args );
		$this->set_value( $args );
		$this->set_source( $args );

		$this->field_id = (int) $field_id;
		if ( $this->field_id === 0 ) {
			return;
		}

		$this->set_db_row();

		if ( $this->has_db_row() ) {
			$this->set_field_key();
			$this->set_field_settings();
			$this->set_options();
		}
	}

	/**
	 * Set the db_row property
	 *
	 * @since 2.03.05
	 */
	private function set_db_row() {
		$where = array(
			'id' => $this->field_id,
		);

		$this->db_row = FrmDb::get_row( 'frm_fields', $where );

		if ( ! is_object( $this->db_row ) ) {
			$this->db_row = null;
		}
	}

	/**
	 * Set the field_key property
	 *
	 * @since 2.03.05
	 */
	private function set_field_key() {
		$this->field_key = $this->db_row->field_key;
	}

	/**
	 * Set the field_settings property
	 *
	 * @since 2.03.05
	 */
	protected function set_field_settings() {
		// Leave as null for free version
	}

	/**
	 * Set the options property
	 *
	 * @since 2.03.05
	 */
	protected function set_options() {
		$field_obj     = FrmFieldFactory::get_field_object( $this->db_row );
		$this->options = $field_obj->get_options( array() );
	}

	/**
	 * Set the html_name property
	 *
	 * @since 2.03.05
	 *
	 * @param array $args
	 */
	protected function set_html_name( $args ) {
		if ( isset( $args['html_name'] ) ) {
			$this->html_name = (string) $args['html_name'];
		}
	}

	/**
	 * Set the selected_value property
	 *
	 * @since 2.03.05
	 *
	 * @param array $args
	 */
	protected function set_value( $args ) {
		if ( isset( $args['value'] ) ) {
			$this->value = (string) $args['value'];
		}
	}

	/**
	 * Set the source property
	 *
	 * @since 2.03.05
	 *
	 * @param array $args
	 */
	protected function set_source( $args ) {
		if ( isset( $args['source'] ) ) {
			$this->source = (string) $args['source'];
		}
	}

	/**
	 * Check if object has any options
	 *
	 * @since 2.03.05
	 *
	 * @return bool
	 */
	final protected function has_options() {
		return ! empty( $this->options );
	}

	/**
	 * Check if a field is connected to the value selector
	 *
	 * @since 2.03.05
	 *
	 * @return bool
	 */
	final protected function has_db_row() {
		return $this->db_row !== null;
	}

	/**
	 * Display the field value selector (dropdown or text field)
	 *
	 * @since 2.03.05
	 */
	public function display() {
		if ( $this->has_options() ) {
			$this->display_dropdown();
		} else {
			$this->display_text_box();
		}
	}

	/**
	 * Print the field value text box
	 *
	 * @since 2.03.05
	 */
	public function display_text_box() {
		echo '<input type="text" name="' . esc_attr( $this->html_name ) . '" value="' . esc_attr( trim( $this->value ) ) . '" />';
	}

	/**
	 * Display the field value selector
	 *
	 * @since 2.03.05
	 */
	protected function display_dropdown() {
		echo '<select name="' . esc_attr( $this->html_name ) . '">';
		echo '<option value="">' . esc_attr( $this->blank_option_label ) . '</option>';

		if ( ! empty( $this->options ) ) {
			foreach ( $this->options as $key => $value ) {
				if ( $value == '' ) {
					continue;
				}

				$option = $this->get_single_field_option( $key, $value );
				$option->print_single_option( $this->value, 25 );
			}
		}

		echo '</select>';
	}

	/**
	 * Get an instance of FrmFieldOption
	 *
	 * @since 2.03.05
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return FrmFieldOption
	 */
	protected function get_single_field_option( $key, $value ) {
		return new FrmFieldOption( $key, $value );
	}
}
