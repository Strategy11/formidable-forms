<?php

/**
 * @since 2.03.05
 */
abstract class FrmFieldAbstract {

	/**
	 * @var int
	 * @since 2.03.05
	 */
	protected $id = 0;

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $key = '';

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $type = '';

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $nice_name = '';

	/**
	 * @var int
	 * @since 2.03.05
	 */
	protected $form_id = 0;

	/**
	 * @var FrmFieldSettings
	 * @since 2.03.05
	 */
	protected $settings = null;

	/**
	 * @var FrmFieldOptions
	 * @since 2.03.05
	 */
	protected $options = null;

	/**
	 * @var object
	 * @since 2.03.05
	 */
	protected $db_row = null;

	/**
	 * FrmField constructor.
	 *
	 * @since 2.03.05
	 */
	public function __construct( $id ) {
		$this->set_id( $id );

		if ( $this->id === 0 ) {
			return;
		}

		$this->set_db_row();

		if ( ! $this->db_row ) {
			return;
		}

		$this->set_key();
		$this->set_type();
		$this->set_form_id();
		$this->set_settings();
		$this->set_options();
	}

	/**
	 * Set the id property
	 *
	 * @since 2.03.05
	 *
	 * @param $id
	 */
	private function set_id( $id ) {
		if ( is_numeric( $id ) ) {
			$this->id = (int) $id;
		}
	}

	/**
	 * Get the id property
	 *
	 * @since 2.03.05
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the db_row property
	 *
	 * @since 2.03.05
	 */
	private function set_db_row() {
		$where = array(
			'id' => $this->id,
		);

		$this->db_row = FrmDb::get_row( 'frm_fields', $where );
	}

	/**
	 * Get the db_row property
	 *
	 * @since 2.03.05
	 *
	 * @return object
	 */
	public function get_db_row() {
		return $this->db_row;
	}

	/**
	 * Set the key property
	 *
	 * @since 2.03.05
	 */
	private function set_key() {
		$this->key = $this->db_row->field_key;
	}

	/**
	 * Set the type property
	 *
	 * @since 2.03.05
	 */
	private function set_type() {
		$this->type = $this->db_row->type;
	}

	/**
	 * Get the type property
	 *
	 * @since 2.03.05
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Set the form_id property
	 *
	 * @since 2.03.05
	 */
	private function set_form_id() {
		$this->key = $this->db_row->form_id;
	}

	/**
	 * Get the form_id property
	 *
	 * @since 2.03.04
	 * @return int
	 */
	public function get_form_id() {
		return $this->form_id;
	}

	/**
	 * Set the settings property
	 *
	 * @since 2.03.05
	 */
	protected function set_settings() {
		$this->settings = new FrmFieldSettings( maybe_unserialize( $this->db_row->field_options ) );
	}

	/**
	 * Get the settings property
	 *
	 * @since 2.03.05
	 *
	 * @return FrmFieldSettings
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Set the options property
	 *
	 * @since 2.03.05
	 */
	protected function set_options() {
		$this->options = new FrmFieldOptions( $this );
	}

	/**
	 * Display the field value selector
	 * Used in field conditional logic, action conditional logic, MailChimp action, etc.
	 *
	 * @since 2.03.05
	 *
	 * @param string $html_name
	 * @param string $selected_value
	 * @param string $source
	 */
	public function display_field_value_selector( $html_name, $selected_value, $source ) {
		$this->options->display_field_value_selector( $html_name, $selected_value, '' );
	}
}