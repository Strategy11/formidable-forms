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
}
