<?php

class test_FrmXMLHelper extends FrmUnitTest {

	public function test_remove_defaults() {
		$defaults = array();
		$saved    = array();

		$this->run_private_method(
			array( 'FrmXMLHelper', 'remove_defaults' ),
			array( $defaults, &$saved )
		);
		$this->assertEquals( array(), $saved );

		$defaults = array(
			'x' => 'X',
			'y' => 'Y',
			'z' => 'Z',
			'b' => 'B',
		);

		$saved = array(
			'a'   => 'A',
			'b'   => 'B',
			'c'   => 'C',
			'arr' => array(
				'x' => 'X',
				'y' => 'Y',
			),
		);

		$this->run_private_method(
			array( 'FrmXMLHelper', 'remove_defaults' ),
			array( $defaults, &$saved )
		);
		$this->assertEquals(
			array(
				'a'   => 'A',
				'c'   => 'C',
				'arr' => array(
					'x' => 'X',
					'y' => 'Y',
				),
			),
			$saved
		);

		$defaults = array(
			'a'   => 'A',
			'b'   => 'B',
			'arr' => array(
				'x' => 'X',
			),
		);

		$saved = array(
			'a'   => 'A',
			'b'   => 'B',
			'c'   => 'C',
			'arr' => array(
				'x' => 'X',
				'y' => 'Y',
			),
		);

		$this->run_private_method(
			array( 'FrmXMLHelper', 'remove_defaults' ),
			array( $defaults, &$saved )
		);
		$this->assertEquals(
			array(
				'c'   => 'C',
				'arr' => array(
					'x' => 'X',
					'y' => 'Y',
				),
			),
			$saved
		);
	}
}
