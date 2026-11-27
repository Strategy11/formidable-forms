<?php

/**
 * @group fields
 *
 * @covers FrmField
 */
class test_FrmFieldTransientCache extends FrmUnitTest {

	/**
	 * Object-cache backends should continue deleting the named transient keys directly.
	 */
	public function test_cleanup_with_external_object_cache() {
		$original_cache_backend_state = (bool) wp_using_ext_object_cache();
		$form                         = $this->factory->form->create_and_get();
		$name                         = 'frm_form_fields_' . $form->id . 'excludeexclude';
		wp_using_ext_object_cache( true );

		try {
			set_transient( $name, array( 'stale' ), HOUR_IN_SECONDS );
			FrmField::delete_form_transient( $form->id );
			$this->assertFalse( get_transient( $name ), 'External object caches should invalidate the transient key.' );
		} finally {
			wp_using_ext_object_cache( $original_cache_backend_state );
		}
	}

	/**
	 * Repeated invalidation should discover all absent variants with one query.
	 */
	public function test_empty_cache_cleanup_uses_one_query() {
		$original_cache_backend_state = (bool) wp_using_ext_object_cache();
		wp_using_ext_object_cache( false );

		try {
			$form = $this->factory->form->create_and_get();
			FrmField::delete_form_transient( $form->id );
			$queries_before_cleanup = get_num_queries();

			FrmField::delete_form_transient( $form->id );

			$this->assertSame( 1, get_num_queries() - $queries_before_cleanup, 'Empty cache cleanup should use one discovery query.' );
		} finally {
			wp_using_ext_object_cache( $original_cache_backend_state );
		}
	}

	/**
	 * Delete all variants and their cached chunks without matching another form's ID prefix.
	 */
	public function test_cleanup_invalidates_cached_chunks_and_orphaned_timeouts() {
		$original_cache_backend_state = (bool) wp_using_ext_object_cache();
		wp_using_ext_object_cache( false );

		try {
			$form       = $this->factory->form->create_and_get();
			$base       = 'frm_form_fields_' . $form->id;
			$other_name = $base . '0excludeexclude';
			$names      = array();

			foreach ( array( 'excludeinclude', 'includeinclude', 'includeexclude', 'excludeexclude' ) as $variant ) {
				$names[] = $base . $variant;
				$names[] = $base . $variant . '1';
			}

			foreach ( $names as $name ) {
				set_transient( $name, array( 'stale' ), HOUR_IN_SECONDS );
				get_transient( $name );
			}

			set_transient( $other_name, array( 'other form' ), HOUR_IN_SECONDS );
			$orphan = '_transient_timeout_' . $base . 'excludeexclude2';
			add_option( $orphan, time() + HOUR_IN_SECONDS, '', false );

			FrmField::delete_form_transient( $form->id );

			foreach ( $names as $name ) {
				$this->assertFalse( get_transient( $name ), 'Every cached field chunk should be invalidated.' );
				$this->assertFalse( get_option( '_transient_timeout_' . $name ), 'Every field chunk timeout should be removed.' );
			}
			$this->assertFalse( get_option( $orphan ), 'Orphaned field cache timeouts should be removed.' );
			$this->assertSame( array( 'other form' ), get_transient( $other_name ), 'Another form with the same ID prefix should keep its cache.' );
		} finally {
			wp_using_ext_object_cache( $original_cache_backend_state );
		}
	}

	/**
	 * One cached list answers every embed and repeater combination.
	 *
	 * @covers FrmField::get_all_for_form
	 * @covers FrmField::get_all_types_in_form
	 */
	public function test_one_cached_list_serves_every_variant() {
		list( $form_id, $ids ) = $this->create_form_with_repeater_and_embed();

		$this->assertSame( $ids['own'], $this->get_field_ids( $form_id, 'exclude', 'exclude' ), 'Only the form fields should be returned.' );
		$this->assertSame( $ids['with_repeat'], $this->get_field_ids( $form_id, 'exclude', 'include' ), 'Repeater fields should be included by default.' );
		$this->assertSame( $ids['with_embed'], $this->get_field_ids( $form_id, 'include', 'exclude' ), 'Embedded fields should follow their embed field.' );
		$this->assertSame( $ids['all'], $this->get_field_ids( $form_id, 'include', 'include' ), 'Every field should be returned.' );

		$this->forget_request_cache();

		$this->assertSame( $ids['own'], $this->get_field_ids( $form_id, 'exclude', 'exclude' ), 'The cached list should give the same fields in a new request.' );
		$this->assertSame( $ids['all'], $this->get_field_ids( $form_id, 'include', 'include' ), 'The cached list should give the same fields in a new request.' );
		$this->assertCount( 1, FrmField::get_all_types_in_form( $form_id, 'email' ), 'Only the form email field should be found.' );
		$this->assertCount( 3, FrmField::get_all_types_in_form( $form_id, 'email', '', 'include' ), 'Repeater and embedded email fields should be found.' );

		$this->assertNotFalse( get_transient( 'frm_form_fields_' . $form_id . 'includeinclude' ), 'The full list should be cached.' );
		$this->assertFalse( get_transient( 'frm_form_fields_' . $form_id . 'excludeinclude' ), 'No other variant should be cached.' );
		$this->assertFalse( get_transient( 'frm_form_fields_' . $form_id . 'excludeexclude' ), 'No other variant should be cached.' );
	}

	/**
	 * Calls in the same request read the transient once.
	 *
	 * @covers FrmField::get_all_for_form
	 */
	public function test_request_reads_the_cached_list_once() {
		list( $form_id ) = $this->create_form_with_repeater_and_embed();
		FrmField::get_all_for_form( $form_id );
		$this->forget_request_cache();

		$reads = 0;
		$count = function ( $pre ) use ( &$reads ) {
			++$reads;
			return $pre;
		};
		add_filter( 'pre_transient_frm_form_fields_' . $form_id . 'includeinclude', $count );

		FrmField::get_all_for_form( $form_id );
		FrmField::get_all_for_form( $form_id, '', 'include' );
		FrmField::get_all_for_form( $form_id, '', 'exclude', 'exclude' );
		FrmField::get_all_types_in_form( $form_id, 'text' );

		remove_filter( 'pre_transient_frm_form_fields_' . $form_id . 'includeinclude', $count );

		$this->assertSame( 1, $reads, 'The cached list should only be read once per request.' );
	}

	/**
	 * A caller changing a field must not change it for the next caller.
	 *
	 * @covers FrmField::get_all_for_form
	 */
	public function test_returned_fields_are_copies() {
		list( $form_id ) = $this->create_form_with_repeater_and_embed();

		$fields                              = FrmField::get_all_for_form( $form_id );
		$fields[0]->name                     = 'Changed';
		$fields[0]->field_options['changed'] = true;

		$fields = FrmField::get_all_for_form( $form_id );
		$this->assertNotSame( 'Changed', $fields[0]->name, 'The cached field name should not change.' );
		$this->assertArrayNotHasKey( 'changed', $fields[0]->field_options, 'The cached field options should not change.' );
	}

	/**
	 * Saving a field clears the list already loaded in this request.
	 *
	 * @covers FrmField::delete_form_transient
	 */
	public function test_new_field_is_seen_in_the_same_request() {
		list( $form_id, $ids ) = $this->create_form_with_repeater_and_embed();
		FrmField::get_all_for_form( $form_id );

		$field_id = $this->factory->field->create( array( 'form_id' => $form_id ) );

		$this->assertContains( (int) $field_id, $this->field_ids( FrmField::get_all_for_form( $form_id ) ), 'A new field should be returned right away.' );
		$this->assertCount( count( $ids['own'] ) + 1, FrmField::get_all_for_form( $form_id, '', 'exclude', 'exclude' ) );
	}

	/**
	 * Two forms that embed each other must not loop while the cache is built.
	 *
	 * @covers FrmField::get_all_for_form
	 */
	public function test_forms_embedding_each_other_do_not_loop() {
		$first_id  = $this->create_empty_form();
		$second_id = $this->create_empty_form();
		$this->create_field( $first_id, 'text' );
		$this->create_field( $second_id, 'text' );
		$this->create_field( $first_id, 'form', array( 'form_select' => $second_id ) );
		$this->create_field( $second_id, 'form', array( 'form_select' => $first_id ) );

		$this->assertCount( 2, FrmField::get_all_for_form( $first_id ), 'The form fields should be returned.' );
		$this->assertCount( 4, FrmField::get_all_for_form( $first_id, '', 'include' ), 'The embedded form fields should be returned once.' );
	}

	/**
	 * Build a form with its own fields, a repeater and an embedded form.
	 *
	 * @return array{0: int, 1: array<string, array<int>>} Form ID, then field IDs for each variant in order.
	 */
	private function create_form_with_repeater_and_embed() {
		$form_id     = $this->create_empty_form();
		$embedded_id = $this->create_empty_form();
		$child_id    = $this->create_empty_form( array( 'parent_form_id' => $form_id ) );

		$name        = $this->create_field( $form_id, 'text' );
		$email       = $this->create_field( $form_id, 'email' );
		$repeat      = $this->create_field( $child_id, 'email' );
		$embed       = $this->create_field( $form_id, 'form', array( 'form_select' => $embedded_id ) );
		$sub_text    = $this->create_field( $embedded_id, 'text' );
		$sub_email   = $this->create_field( $embedded_id, 'email' );
		$after_embed = $this->create_field( $form_id, 'text' );

		FrmField::delete_form_transient( $form_id );
		FrmField::delete_form_transient( $embedded_id );

		return array(
			$form_id,
			array(
				'own'         => array( $name, $email, $embed, $after_embed ),
				'with_repeat' => array( $name, $email, $repeat, $embed, $after_embed ),
				'with_embed'  => array( $name, $email, $embed, $sub_text, $sub_email, $after_embed ),
				'all'         => array( $name, $email, $repeat, $embed, $sub_text, $sub_email, $after_embed ),
			),
		);
	}

	/**
	 * @param array $args Form values to save.
	 *
	 * @return int The new form ID, with none of the fields a new form starts with.
	 */
	private function create_empty_form( $args = array() ) {
		$form_id = (int) $this->factory->form->create( $args );

		foreach ( FrmField::getAll( array( 'fi.form_id' => $form_id ) ) as $field ) {
			FrmField::destroy( $field->id );
		}

		return $form_id;
	}

	/**
	 * @param int    $form_id       Form ID.
	 * @param string $type          Field type.
	 * @param array  $field_options Field options to save.
	 *
	 * @return int The new field ID.
	 */
	private function create_field( $form_id, $type, $field_options = array() ) {
		static $order = 0;
		++$order;

		return (int) $this->factory->field->create(
			array(
				'form_id'       => $form_id,
				'type'          => $type,
				'field_order'   => $order,
				'field_options' => $field_options,
			)
		);
	}

	/**
	 * @param array $fields Field objects.
	 *
	 * @return array<int>
	 */
	private function field_ids( $fields ) {
		return array_map( 'intval', wp_list_pluck( array_values( $fields ), 'id' ) );
	}

	/**
	 * @param int    $form_id    Form ID.
	 * @param string $inc_embed  'include' to get fields from embedded forms.
	 * @param string $inc_repeat 'include' to get fields in repeaters.
	 *
	 * @return array<int>
	 */
	private function get_field_ids( $form_id, $inc_embed, $inc_repeat ) {
		return $this->field_ids( FrmField::get_all_for_form( $form_id, '', $inc_embed, $inc_repeat ) );
	}

	/**
	 * Forget the field lists loaded in this request, as a new request would.
	 *
	 * @return void
	 */
	private function forget_request_cache() {
		$this->set_private_property( 'FrmField', 'request_cache', array() );
	}
}
