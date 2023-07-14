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
	 * @var int
	 */
	private $current_field_count;

	/**
	 * @var string
	 */
	private $field_layout_class;

	/**
	 * @var int
	 */
	private $active_field_size;

	/**
	 * @var bool $is_frm_first flagged while calling get_field_layout_class, true if classes contain frm_first class.
	 */
	private $is_frm_first;

	/**
	 * @var stdClass
	 */
	private $field;

	/**
	 * @var FrmFieldGridHelper $section_helper
	 */
	private $section_helper;

	/**
	 * @var bool $nested
	 */
	private $nested;

	/**
	 * @var int $section_size
	 */
	private $section_size;

	private $section_is_open = false;

	public function __construct( $nested = false ) {
		$this->parent_li           = false;
		$this->current_list_size   = 0;
		$this->current_field_count = 0;
		$this->nested              = $nested;
	}

	/**
	 * @param stdClass $field
	 *
	 * @return void
	 */
	public function set_field( $field ) {
		$this->field = $field;

		if ( ! empty( $this->section_helper ) && 'end_divider' !== $field->type ) {
			$this->section_helper->set_field( $field );
			return;
		}

		if ( 'end_divider' === $field->type ) {
			$this->field_layout_class = '';
			$this->active_field_size  = $this->section_size;
			$this->section_is_open    = false;
			$this->maybe_close_section_helper();
		} else {
			$this->field_layout_class = $this->get_field_layout_class();
			$this->active_field_size  = $this->get_size_of_class( $this->field_layout_class );
		}

		if ( 'divider' === $field->type && empty( $this->nested ) ) {
			$this->section_size      = $this->active_field_size;
			$this->active_field_size = 0;
			$this->section_helper    = new self( true );
		}
	}

	/**
	 * @return void
	 */
	private function maybe_close_section_helper() {
		if ( empty( $this->section_helper ) ) {
			return;
		}
		$this->section_helper->force_close_field_wrapper();
		$this->section_helper = null;
	}

	/**
	 * @return string
	 */
	public function get_field_layout_class() {
		$field = FrmFieldsHelper::setup_edit_vars( $this->field );

		if ( empty( $field['classes'] ) ) {
			return '';
		}

		$split              = explode( ' ', $field['classes'] );
		$this->is_frm_first = in_array( 'frm_first', $split, true );
		$classes            = self::get_grid_classes();

		foreach ( $classes as $class ) {
			if ( in_array( $class, $split, true ) ) {
				return $class;
			}
		}

		return '';
	}

	/**
	 * @return void
	 */
	public function maybe_begin_field_wrapper() {
		if ( $this->should_first_close_the_active_field_wrapper() ) {
			$this->close_field_wrapper();
		}

		if ( false === $this->parent_li && 'end_divider' !== $this->field->type ) {
			$this->begin_field_wrapper();
		}

		if ( ! empty( $this->section_helper ) && $this->section_is_open ) {
			$this->section_helper->maybe_begin_field_wrapper();
		}
	}

	/**
	 * @return bool
	 */
	private function should_first_close_the_active_field_wrapper() {
		if ( false === $this->parent_li || ! empty( $this->section_helper ) ) {
			return false;
		}
		if ( 'end_divider' === $this->field->type ) {
			return false;
		}
		return ! $this->can_support_current_layout() || $this->is_frm_first;
	}

	/**
	 * @return void
	 */
	private function begin_field_wrapper() {
		echo '<li class="frm_field_box"><ul class="frm_grid_container frm_sorting">';
		$this->parent_li           = true;
		$this->current_list_size   = 0;
		$this->current_field_count = 0;
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
		}

		if ( 0 === strpos( $class, 'frm' ) ) {
			$substr = substr( $class, 3 );
			if ( is_numeric( $substr ) ) {
				return (int) $substr;
			}
		}

		// Anything missing a layout class should be a full width row.
		return 12;
	}

	/**
	 * @return void
	 */
	public function sync_list_size() {
		if ( ! isset( $this->field ) ) {
			return;
		}

		if ( 'divider' === $this->field->type ) {
			$this->section_is_open = true;
		}

		if ( ! empty( $this->section_helper ) ) {
			$this->section_helper->sync_list_size();
			if ( 'end_divider' === $this->field->type ) {
				$this->maybe_close_section_helper();
			}
			return;
		}

		if ( false !== $this->parent_li ) {
			$this->current_field_count ++;
			$this->current_list_size += $this->active_field_size;
			if ( 12 === $this->current_list_size ) {
				$this->close_field_wrapper();
			}
		}
	}

	/**
	 * It is possible that there was still space for another field so the wrapper could still be open after looping the fields.
	 * If it is, make sure it's closed now.
	 *
	 * @return void
	 */
	public function force_close_field_wrapper() {
		if ( false !== $this->parent_li ) {
			$this->close_field_wrapper();
		}
	}

	/**
	 * @return void
	 */
	private function close_field_wrapper() {
		$this->maybe_close_section_helper();
		echo '</ul></li>';
		$this->parent_li           = false;
		$this->current_list_size   = 0;
		$this->current_field_count = 0;
	}

	/**
	 * @return bool
	 */
	private function can_support_current_layout() {
		if ( 'end_divider' === $this->field->type ) {
			return true;
		}
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
			'frm_full',
			'frm_half',
			'frm_third',
			'frm_two_thirds',
			'frm_fourth',
			'frm_three_fourths',
			'frm_sixth',
			'frm1',
			'frm2',
			'frm3',
			'frm4',
			'frm5',
			'frm6',
			'frm7',
			'frm8',
			'frm9',
			'frm10',
			'frm11',
			'frm12',
		);
	}
}
