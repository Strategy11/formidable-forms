<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFieldGridHelper {

	private $parent_li;

	/**
	 * @var int
	 */
	private $current_list_size;

	/**
	 * @var string
	 */
	private $field_layout_class;

	/**
	 * @var bool
	 */
	private $has_field_layout_class;

	/**
	 * @var stdClass
	 */
	private $field;

	public function __construct() {
		$this->parent_li = false;
	}

	/**
	 * @param stdClass $field
	 */
	public function set_field( $field ) {
		$this->field                  = $field;
		$this->field_layout_class     = $this->get_field_layout_class();
		$this->has_field_layout_class = false !== $this->field_layout_class;
	}

	/**
	 * @return string|false
	 */
	public function get_field_layout_class() {
		$field = FrmFieldsHelper::setup_edit_vars( $this->field );

		if ( empty( $field['classes'] ) ) {
			return false;
		}

		$split   = explode( ' ', $field['classes'] );
		$classes = self::get_grid_classes();

		foreach ( $classes as $class ) {
			if ( in_array( $class, $split, true ) ) {
				return $class;
			}
		}

		return '';
	}

	public function maybe_begin_field_wrapper() {
		if ( $this->has_field_layout_class && false === $this->parent_li ) {
			// TODO make sure we're not filling past 12 columns here. if we hit 12, we want to break and open a new parent again.
			echo '<li><ul class="frm_grid_container">';
			$this->parent_li         = true;
			$this->current_list_size = $this->get_size_of_class( $this->field_layout_class );
		}
	}

	/**
	 * @param string $class
	 * @return int
	 */
	private static function get_size_of_class( $class ) {
		switch ( $class ) {
			case 'frm_half':
				return 6;
			case 'frm_third':
				return 4;
			case 'frm_two_thirds':
				return 8;
			case 'frm_fourth':
				return 3;
			case 'frm_three_fourths':
				return 9;
			case 'frm_sixth':
				return 2;
			case 'frm10':
				return 10;
			case 'frm12':
				return 12;
		}
		return 0;
	}

	private function get_size_of_current_class() {
		return self::get_size_of_class( $this->field_layout_class );
	}

	public function maybe_close_field_wrapper() {
		if ( false !== $this->parent_li ) {
			$this->current_list_size += $this->get_size_of_class( $this->field_layout_class );
			if ( ! $this->can_support_current_layout() ) {
				$this->close_field_wrapper();
			}
		}
	}

	/**
	 * It is possible that there was still space for another field so the wrapper could still be open after looping the fields.
	 * If it is, make sure it's closed now.
	 */
	public function force_close_field_wrapper() {
		if ( false !== $this->parent_li ) {
			$this->close_field_wrapper();
		}
	}

	private function close_field_wrapper() {
		echo '</ul></li>';
		$this->parent_li = false;
	}

	private function can_support_current_layout() {
		return $this->can_support_an_additional_layout( $this->field_layout_class );
	}

	/**
	 * @param string $class
	 * @return bool
	 */
	private function can_support_an_additional_layout( $class ) {
		$size = $this->get_size_of_class( $class );
		return $this->current_list_size + $size <= 12;
	}

	/**
	 * @return array<string>
	 */
	private static function get_grid_classes() {
		return array(
			'frm_half',
			'frm_third',
			'frm_two_thirds',
			'frm_fourth',
			'frm_three_fourths',
			'frm_sixth',
			'frm10',
			'frm12',
		);
	}
}
