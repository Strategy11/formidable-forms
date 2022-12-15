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
			'arr2' => array(
				'x' => 'X',
				'z' => 'Z',
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

	/**
	 * @covers FrmXMLHelper::populate_postmeta
	 */
	public function test_populate_postmeta() {
		$post             = array();
		$meta             = new stdClass();
		$meta->meta_key   = 'frm_dyncontent';
		$meta->meta_value = '[{"box":1,"content":"<div id=\"box_1\">Box 1 Content<\/div>"},{"box":2,"content":"Box 2 Content\nBox 2 Line 2"}]';
		$imported         = array(
			'forms' => array(),
		);

		$this->populate_postmeta( $post, $meta, $imported );

		$this->assertTrue( array_key_exists( 'postmeta', $post ) );
		$this->assertTrue( ! empty( $post['postmeta'] ) );
		$this->assertTrue( array_key_exists( 'frm_dyncontent', $post['postmeta'] ) );
		$this->assertTrue( is_array( $post['postmeta']['frm_dyncontent'] ) );
		$this->assertEquals(
			array(
				array(
					'box'     => 1,
					'content' => '<div id=\"box_1\">Box 1 Content<\/div>',
				),
				array(
					'box'     => 2,
					'content' => 'Box 2 Content\nBox 2 Line 2',
				),
			),
			$post['postmeta']['frm_dyncontent']
		);
	}

	private function populate_postmeta( &$post, $meta, $imported ) {
		$this->run_private_method( array( 'FrmXMLHelper', 'populate_postmeta' ), array( &$post, $meta, $imported ) );
	}
}
