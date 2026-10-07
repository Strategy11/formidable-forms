<?php

/**
 * @covers FrmSpamEntriesHelper
 */
class test_FrmSpamQuery extends FrmUnitTest {

	/**
	 * Applying exclusion twice preserves a single condition for either table alias.
	 */
	public function test_exclusion_is_idempotent() {
		foreach ( array( 'it.', '' ) as $prefix ) {
			$where    = array(
				array(
					'or'               => 1,
					'parent_form_id'   => null,
					'parent_form_id <' => 1,
				),
			);
			$excluded = FrmSpamEntriesHelper::exclude_spam( $where, $prefix );
			$this->assertCount( 2, $excluded );
			$this->assertSame( $excluded, FrmSpamEntriesHelper::exclude_spam( $excluded, $prefix ) );
		}
	}

	/**
	 * Exclusion inside an OR branch cannot protect the other branches from spam.
	 */
	public function test_or_branch_still_gets_outer_exclusion() {
		$exclusion = FrmSpamEntriesHelper::get_exclude_spam_where();
		$where     = array(
			'or'         => 1,
			'it.form_id' => 123,
			$exclusion,
		);
		$this->assertSame( array( $where, $exclusion ), FrmSpamEntriesHelper::exclude_spam( $where ) );
	}
}
