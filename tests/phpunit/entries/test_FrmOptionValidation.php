<?php

/**
 * @group entries
 * @group free
 *
 * @covers FrmEntryValidate
 */
#[\PHPUnit\Framework\Attributes\Group( 'entries' )]
#[\PHPUnit\Framework\Attributes\Group( 'free' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmEntryValidate::class )]
class test_FrmOptionValidation extends FrmUnitTest {

	/** @var int The field whose setup values should change. */
	private $option_field_id;

	/** @var array Setup values supplied by custom code. */
	private $setup_values = array();

	/**
	 * Exercise the public field validation path for static and dynamic choices.
	 *
	 * @dataProvider option_validation_cases
	 *
	 * @param string $type     The choice field type.
	 * @param bool   $editing  Whether an entry is being edited.
	 * @param array  $scenario Saved options, setup changes, submitted value, and expected validity.
	 *
	 * @return void
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'option_validation_cases' )]
	public function test_option_validation_preserves_compatibility( $type, $editing, $scenario ) {
		$form_id               = $this->factory->form->create();
		$field                 = $this->factory->field->create_and_get(
			array(
				'form_id'       => $form_id,
				'required'      => $scenario['required'] ?? '0',
				'type'          => $type,
				'options'       => $scenario['options'] ?? array( 'Alpha', 'Beta' ),
				'field_options' => $scenario['field_options'] ?? array(),
			)
		);
		$this->option_field_id = (int) $field->id;
		$this->setup_values    = $scenario['setup'] ?? array();
		$hook                  = $scenario['hook'] ?? ( $editing ? 'frm_setup_edit_fields_vars' : 'frm_setup_new_fields_vars' );
		$entry_id              = $editing ? $this->factory->entry->create( array( 'form_id' => $form_id ) ) : 0;
		$errors                = array();
		$values                = array(
			'form_id'   => $form_id,
			'item_meta' => array( $field->id => $scenario['value'] ),
		);

		add_filter( $hook, array( $this, 'filter_setup_values' ), 20 );
		add_filter( 'frm_field_value_saved', array( $this, 'filter_option_value' ), 20, 3 );
		add_shortcode( 'frm_option_validation_test', array( $this, 'shortcode_option' ) );

		try {
			FrmEntryValidate::validate_field( $field, $errors, $values, array( 'entry_id' => $entry_id ) );
			$this->assertSame( $scenario['valid'], ! isset( $errors[ 'field' . $field->id ] ), 'Option validity should preserve the compatibility contract.' );
		} finally {
			remove_filter( $hook, array( $this, 'filter_setup_values' ), 20 );
			remove_filter( 'frm_field_value_saved', array( $this, 'filter_option_value' ), 20 );
			remove_shortcode( 'frm_option_validation_test' );
		}
	}

	/**
	 * @return Generator
	 */
	public static function option_validation_cases() {
		$cases = array_merge( self::static_option_cases(), self::dynamic_option_cases(), self::formatted_option_cases(), self::keyed_option_cases() );

		foreach ( array( 'select', 'radio', 'checkbox' ) as $type ) {
			foreach ( array( false, true ) as $editing ) {
				foreach ( $cases as $name => $scenario ) {
					yield $type . ( $editing ? ' edit: ' : ' new: ' ) . $name => array( $type, $editing, $scenario );
				}
			}
		}
	}

	/**
	 * @return array
	 */
	private static function static_option_cases() {
		return array(
			'saved value'             => array(
				'value' => 'Alpha',
				'valid' => true,
			),
			'saved whitespace'        => array(
				'value' => ' Alpha ',
				'valid' => true,
			),
			'unknown static value'    => array(
				'value' => 'Unknown',
				'valid' => false,
			),
			'blank optional value'    => array(
				'value' => '',
				'valid' => true,
			),
			'empty selection'         => array(
				'value' => array(),
				'valid' => true,
			),
			'all saved selections'    => array(
				'value' => array( 'Alpha', 'Beta' ),
				'valid' => true,
			),
			'mixed static selections' => array(
				'value' => array( 'Alpha', 'Unknown' ),
				'valid' => false,
			),
			'unknown selection first' => array(
				'value' => array( 'Unknown', 'Alpha' ),
				'valid' => false,
			),
			'unchanged hook options'  => array(
				'setup' => array( 'options' => array( 'Alpha', 'Beta' ) ),
				'value' => 'Unknown',
				'valid' => false,
			),
			'unrelated setup change'  => array(
				'setup' => array( 'description' => 'Custom description' ),
				'value' => 'Unknown',
				'valid' => false,
			),
		);
	}

	/**
	 * @return array
	 */
	private static function dynamic_option_cases() {
		return array(
			'new hook render time value' => array(
				'hook'  => 'frm_setup_new_fields_vars',
				'setup' => array( 'options' => array( 'Current option' ) ),
				'value' => 'Previous option',
				'valid' => true,
			),
			'dynamic required blank'     => array(
				'required' => '1',
				'setup'    => array( 'options' => array( 'Current option' ) ),
				'value'    => '',
				'valid'    => false,
			),
			'hook adds submitted value'  => array(
				'setup' => array( 'options' => array( 'Alpha', 'Beta', 'Gamma' ) ),
				'value' => 'Gamma',
				'valid' => true,
			),
			'render time value'          => array(
				'setup' => array( 'options' => array( 'Current time option' ) ),
				'value' => 'Previous time option',
				'valid' => true,
			),
			'URL value unavailable'      => array(
				'setup' => array( 'options' => array() ),
				'value' => 'URL option from render',
				'valid' => true,
			),
			'mixed dynamic selections'   => array(
				'setup' => array( 'options' => array( 'Current option' ) ),
				'value' => array( 'Alpha', 'Previous option' ),
				'valid' => true,
			),
			'dynamic unknown first'      => array(
				'setup' => array( 'options' => array( 'Current option' ) ),
				'value' => array( 'Previous option', 'Alpha' ),
				'valid' => true,
			),
			'saved option still valid'   => array(
				'setup' => array( 'options' => array( 'Replacement' ) ),
				'value' => 'Alpha',
				'valid' => true,
			),
			'other allows custom text'   => array(
				'options' => array(
					'Alpha',
					'other_1' => 'Other',
				),
				'value'   => 'Custom text',
				'valid'   => true,
			),
			'hook other option'          => array(
				'setup' => array( 'options' => array( 'other_1' => 'Other' ) ),
				'value' => 'Custom text',
				'valid' => true,
			),
		);
	}

	/**
	 * @return array
	 */
	private static function formatted_option_cases() {
		$separate_options = array(
			array(
				'label' => 'Alpha label',
				'value' => 'alpha-value',
			),
			array(
				'label' => 'Beta label',
				'value' => 'beta-value',
			),
		);
		return array(
			'separate saved value'      => array(
				'options'       => $separate_options,
				'field_options' => array( 'separate_value' => '1' ),
				'value'         => 'alpha-value',
				'valid'         => true,
			),
			'separate label rejected'   => array(
				'options'       => $separate_options,
				'field_options' => array( 'separate_value' => '1' ),
				'value'         => 'Alpha label',
				'valid'         => false,
			),
			'label without separate'    => array(
				'options' => $separate_options,
				'value'   => 'Alpha label',
				'valid'   => true,
			),
			'Unicode value'             => array(
				'options' => array( 'Montréal 日本語' ),
				'value'   => 'Montréal 日本語',
				'valid'   => true,
			),
			'shortcode option'          => array(
				'options' => array( '[frm_option_validation_test]' ),
				'value'   => 'Shortcode option',
				'valid'   => true,
			),
			'legacy integer padding'    => array(
				'options' => array( '101' ),
				'value'   => '0101',
				'valid'   => true,
			),
			'legacy integer truncation' => array(
				'options' => array( '101' ),
				'value'   => '101.9',
				'valid'   => true,
			),
			'legacy negative number'    => array(
				'options' => array( '-101' ),
				'value'   => '-101.9',
				'valid'   => true,
			),
			'legacy decimal option'     => array(
				'options' => array( '12.5' ),
				'value'   => '12.9',
				'valid'   => true,
			),
		);
	}

	/**
	 * @return array
	 */
	private static function keyed_option_cases() {
		return array(
			'new hook keyed option'     => array(
				'hook'    => 'frm_setup_new_fields_vars',
				'options' => array( 101 => 'Alpha' ),
				'setup'   => array( 'use_key' => true ),
				'value'   => '101',
				'valid'   => true,
			),
			'keyed option'              => array(
				'options' => array(
					101 => 'Alpha',
					102 => 'Beta',
				),
				'setup'   => array( 'use_key' => true ),
				'value'   => '101',
				'valid'   => true,
			),
			'keyed padded option'       => array(
				'options' => array( 101 => 'Alpha' ),
				'setup'   => array( 'use_key' => true ),
				'value'   => '0101',
				'valid'   => true,
			),
			'unknown key'               => array(
				'options' => array( 101 => 'Alpha' ),
				'setup'   => array( 'use_key' => true ),
				'value'   => '999',
				'valid'   => false,
			),
			'fractional key'            => array(
				'options' => array( 101 => 'Alpha' ),
				'setup'   => array( 'use_key' => true ),
				'value'   => '101.9',
				'valid'   => false,
			),
			'string key'                => array(
				'options' => array( 'key-alpha' => 'Alpha' ),
				'setup'   => array( 'use_key' => true ),
				'value'   => 'key-alpha',
				'valid'   => true,
			),
			'key without custom setup'  => array(
				'options' => array( 101 => 'Alpha' ),
				'value'   => '101',
				'valid'   => false,
			),
			'custom saved value filter' => array(
				'setup' => array( 'test_value_prefix' => 'custom-' ),
				'value' => 'custom-Alpha',
				'valid' => true,
			),
		);
	}

	/**
	 * Check that validate() forwards a real entry ID to the edit setup hook.
	 *
	 * @return void
	 */
	public function test_validate_routes_setup_hooks_by_entry_context() {
		$form_id               = $this->factory->form->create();
		$field                 = $this->factory->field->create_and_get(
			array(
				'form_id' => $form_id,
				'type'    => 'select',
				'options' => array( 'Saved' ),
			)
		);
		$entry_id              = $this->factory->entry->create( array( 'form_id' => $form_id ) );
		$this->option_field_id = (int) $field->id;
		$this->setup_values    = array( 'options' => array( 'Edit option' ) );
		$values                = array(
			'form_id'   => $form_id,
			'item_meta' => array( $field->id => 'Render time option' ),
		);
		add_filter( 'frm_setup_edit_fields_vars', array( $this, 'filter_setup_values' ), 20 );

		try {
			$this->assertArrayHasKey( 'field' . $field->id, FrmEntryValidate::validate( $values ), 'A new entry should not use the edit hook.' );
			$values['id'] = $entry_id;
			$errors       = FrmEntryValidate::validate( $values );
			$this->assertArrayNotHasKey( 'field' . $field->id, $errors, 'An edit should preserve render time dynamic values.' );
		} finally {
			remove_filter( 'frm_setup_edit_fields_vars', array( $this, 'filter_setup_values' ), 20 );
		}
	}

	/**
	 * Changing one field must not disable option validation for another field.
	 *
	 * @return void
	 */
	public function test_dynamic_setup_does_not_bypass_other_fields() {
		$form_id               = $this->factory->form->create();
		$dynamic               = $this->factory->field->create_and_get(
			array(
				'form_id' => $form_id,
				'type'    => 'select',
				'options' => array( 'Saved' ),
			)
		);
		$static                = $this->factory->field->create_and_get(
			array(
				'form_id' => $form_id,
				'type'    => 'select',
				'options' => array( 'Saved' ),
			)
		);
		$this->option_field_id = (int) $dynamic->id;
		$this->setup_values    = array( 'options' => array( 'Current option' ) );
		add_filter( 'frm_setup_new_fields_vars', array( $this, 'filter_setup_values' ), 20 );

		try {
			$errors = FrmEntryValidate::validate(
				array(
					'form_id'   => $form_id,
					'item_meta' => array(
						$dynamic->id => 'Previous option',
						$static->id  => 'Unknown',
					),
				)
			);
			$this->assertArrayNotHasKey( 'field' . $dynamic->id, $errors, 'The dynamic field should preserve its bypass.' );
			$this->assertArrayHasKey( 'field' . $static->id, $errors, 'An unrelated static field should still be validated.' );
		} finally {
			remove_filter( 'frm_setup_new_fields_vars', array( $this, 'filter_setup_values' ), 20 );
		}
	}

	/**
	 * @param array $values The prepared field values.
	 *
	 * @return array
	 */
	public function filter_setup_values( $values ) {
		if ( (int) $values['id'] === $this->option_field_id ) {
			return array_merge( $values, $this->setup_values );
		}
		return $values;
	}

	/**
	 * Mirror custom saved-value filters and Pro's use_key support without requiring Pro.
	 *
	 * @param array|string $option The option being rendered.
	 * @param int|string   $key    The option key.
	 * @param array        $field  The prepared field values.
	 *
	 * @return array|int|string
	 */
	public function filter_option_value( $option, $key, $field ) {
		if ( (int) $field['id'] !== $this->option_field_id ) {
			return $option;
		}

		if ( ! empty( $field['use_key'] ) ) {
			return $key;
		}

		if ( ! empty( $field['test_value_prefix'] ) && is_string( $option ) ) {
			return $field['test_value_prefix'] . $option;
		}

		return $option;
	}

	/** @return string */
	public function shortcode_option() {
		return 'Shortcode option';
	}
}
