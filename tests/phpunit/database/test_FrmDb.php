<?php

/**
 * @group database
 */
class test_FrmDb extends FrmUnitTest {

	/**
	 * @covers FrmDb::esc_order
	 */
	public function test_esc_order() {
		$orders = array(
			'it.created_at ASC'          => ' ORDER BY it.created_at asc',
			'(select+sleep(3)) #'        => ' ORDER BY select+sleep3 asc',
			'count(*) DESC'              => ' ORDER BY count(*) desc',
			'field_order DESC'           => ' ORDER BY field_order desc',
			' ORDER BY field_order DESC' => ' ORDER BY field_order desc',
			'meta_value'                 => ' ORDER BY meta_value ',
			'meta_1754+0 asc'            => ' ORDER BY meta_1754+0 asc',
		);

		foreach ( $orders as $start => $expected ) {
			$actual = FrmDb::esc_order( $start );
			$this->assertSame( $expected, $actual );
		}
	}
}
