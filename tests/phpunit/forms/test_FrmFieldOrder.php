<?php

/**
 * @covers FrmForm
 */
class test_FrmFieldOrder extends FrmUnitTest {

	public function test_order_save_preserves_settings_and_restricts_forms() {
		$form_id  = $this->factory->form->create();
		$child_id = $this->factory->form->create( array( 'parent_form_id' => $form_id ) );
		$other_id = $this->factory->form->create();
		$active   = $this->factory->field->create(
			array(
				'form_id'     => $form_id,
				'type'        => 'text',
				'field_order' => 1,
			)
		);
		$inactive = $this->factory->field->create(
			array(
				'form_id'       => $form_id,
				'type'          => 'text',
				'field_order'   => 2,
				'default_value' => 'Keep me',
			)
		);
		$child    = $this->factory->field->create(
			array(
				'form_id'     => $child_id,
				'type'        => 'text',
				'field_order' => 3,
			)
		);
		$other    = $this->factory->field->create(
			array(
				'form_id'     => $other_id,
				'type'        => 'text',
				'field_order' => 4,
			)
		);
		$before   = FrmField::getOne( $inactive );
		FrmField::get_all_for_form( $child_id );
		FrmField::get_all_for_form( $form_id );

		FrmForm::update(
			$form_id,
			array(
				'frm_fields_submitted' => array( $active ),
				'field_options'        => array(
					'name_' . $active        => 'Edited active field',
					'field_order_' . $active => 1,
				),
				'frm_field_order'      => wp_json_encode(
					array(
						$active   => 3,
						$inactive => 1,
						$child    => 2,
						$other    => 99,
					)
				),
			)
		);

		$this->assertSame( 'Edited active field', FrmField::getOne( $active )->name, 'Active settings should save.' );
		$this->assertSame( 3, (int) FrmField::getOne( $active )->field_order, 'The order map should override stale active order.' );
		$after = FrmField::getOne( $inactive );
		$this->assertSame( 1, (int) $after->field_order, 'Inactive fields should be reordered.' );
		$this->assertSame( $before->default_value, $after->default_value, 'Inactive defaults should be preserved.' );
		$this->assertSame( $before->field_options, $after->field_options, 'Inactive options should be preserved.' );
		$this->assertSame( 2, (int) FrmField::getOne( $child )->field_order, 'Repeater child fields should be reordered.' );
		$child_fields = FrmField::get_all_for_form( $child_id );
		$child_orders = wp_list_pluck( $child_fields, 'field_order', 'id' );
		$this->assertSame( 2, (int) $child_orders[ $child ], 'Child form caches should refresh.' );
		$this->assertSame( 4, (int) FrmField::getOne( $other )->field_order, 'Unrelated forms should be protected.' );
	}

	public function test_large_order_map_uses_one_field_update_query() {
		global $wpdb;
		$form_id = $this->factory->form->create();
		$ids     = $this->factory->field->create_many(
			1001,
			array(
				'form_id' => $form_id,
				'type'    => 'text',
			)
		);
		$orders  = array();

		foreach ( $ids as $index => $id ) {
			$orders[ $id ] = count( $ids ) - $index;
		}

		$queries     = array();
		$track_query = static function ( $query ) use ( &$queries, $wpdb ) {
			if ( str_starts_with( $query, 'UPDATE `' . $wpdb->prefix . 'frm_fields`' ) ) {
				$queries[] = $query;
			}
			return $query;
		};
		add_filter( 'query', $track_query );
		FrmForm::update( $form_id, array( 'frm_field_order' => wp_json_encode( $orders ) ) );
		remove_filter( 'query', $track_query );

		$this->assertCount( 1, $queries, 'A large reorder should use one field update query.' );
		$this->assertSame( 1001, (int) FrmField::getOne( $ids[0] )->field_order, 'The first field should move to the end.' );
		$this->assertSame( 1, (int) FrmField::getOne( end( $ids ) )->field_order, 'The last field should move to the start.' );
	}

	public function test_invalid_order_values_are_ignored() {
		$form_id  = $this->factory->form->create();
		$field_id = $this->factory->field->create(
			array(
				'form_id'     => $form_id,
				'type'        => 'text',
				'field_order' => 5,
			)
		);

		foreach ( array( 'invalid json', 'null', '[]', wp_json_encode( array( $field_id => array( 1 ) ) ), wp_json_encode( array( $field_id => -1 ) ) ) as $orders ) {
			FrmForm::update( $form_id, array( 'frm_field_order' => $orders ) );
			$this->assertSame( 5, (int) FrmField::getOne( $field_id )->field_order, 'Malformed maps should leave orders unchanged.' );
		}
	}

	public function test_invalid_form_id_cannot_reorder_top_level_forms() {
		$form_id  = $this->factory->form->create();
		$field_id = $this->factory->field->create(
			array(
				'form_id'     => $form_id,
				'type'        => 'text',
				'field_order' => 5,
			)
		);
		FrmForm::update( 0, array( 'frm_field_order' => wp_json_encode( array( $field_id => 1 ) ) ) );
		$this->assertSame( 5, (int) FrmField::getOne( $field_id )->field_order, 'Invalid form IDs should not allow updates across top-level forms.' );
	}
}
