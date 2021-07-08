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
	 * @var bool
	 */
	private $has_field_layout_class;

	/**
	 * @var int
	 */
	private $active_field_size;

	/**
	 * @var int used to track the previous current_list_size value when looping inside of sections.
	 */
	private $parent_list_size;

	/**
	 * @var bool $is_frm_first flagged while calling get_field_layout_class, true if classes contain frm_first class.
	 */
	private $is_frm_first;

	/**
	 * @var stdClass
	 */
	private $field;

	public function __construct() {
		$this->parent_li           = false;
		$this->current_list_size   = 0;
		$this->current_field_count = 0;
	}

	/**
	 * @param stdClass $field
	 */
	public function set_field( $field ) {
		$this->field                  = $field;
		$this->field_layout_class     = $this->get_field_layout_class();
		$this->active_field_size      = $this->get_size_of_class( $this->field_layout_class );
		$this->has_field_layout_class = ! ! $this->field_layout_class;

		if ( $this->is_frm_first ) {
			$this->force_close_field_wrapper();
		}

		if ( in_array( $field->type, array( 'divider', 'end_divider' ), true ) ) {
			$field_size              = $this->active_field_size;
			$this->active_field_size = 0;

			if ( 'divider' === $field->type ) {
				$this->parent_list_size  = $this->current_list_size;
				$this->current_list_size = 0;
			} elseif ( 'end_divider' === $field->type ) {
				$this->current_list_size = $field_size + $this->parent_list_size;
			}
		}
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

	public function maybe_begin_field_wrapper() {
		if ( false !== $this->parent_li && ! $this->can_support_current_layout() ) {
			$this->close_field_wrapper();
		}

		if ( false === $this->parent_li ) {
			$this->begin_field_wrapper();
		}
	}

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

	public function sync_list_size() {
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
	 */
	public function force_close_field_wrapper() {
		if ( false !== $this->parent_li ) {
			$this->close_field_wrapper();
		}
	}

	private function close_field_wrapper() {
		$this->echo_field_group_controls();
		echo '</ul></li>';
		$this->parent_li           = false;
		$this->current_list_size   = 0;
		$this->current_field_count = 0;
	}

	private function echo_field_group_controls() {
		// TODO current_field_count doesn't seem to be totally accurate. I'm seeing 2 when my number is 5. I think sections need to track this.
		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-group-controls.php';
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
