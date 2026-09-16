<?php

/**
 * Lite has rendered the payments list table since 6.27, so this class is what the payments
 * submodule's own FrmTransListHelper was deprecated in favour of. Each test here pins a
 * behavior the submodule now depends on Lite for.
 *
 * @group stripe
 */
class test_FrmTransLiteListHelper extends FrmUnitTest {

	/**
	 * Whether this class registered the paypal gateway filter itself, and therefore owns
	 * taking it back off again.
	 *
	 * @var bool
	 */
	private $added_gateway_filter = false;

	public function tearDown(): void {
		unset( $_REQUEST['trans_type'] );

		// Take off only what this class put on. Removing the filter unconditionally would
		// strip the gateway registration Lite hooked up, for every later test in the process.
		if ( $this->added_gateway_filter ) {
			remove_filter( 'frm_payment_gateways', 'FrmPayPalLiteAppController::add_gateway' );
			$this->added_gateway_filter = false;
		}

		parent::tearDown();
	}

	/**
	 * @param string $table Either payments or subscriptions.
	 *
	 * @return FrmTransLiteListHelper
	 */
	private function make_helper( $table = 'payments' ) {
		$_REQUEST['trans_type'] = $table;
		return new FrmTransLiteListHelper( array( 'screen' => 'toplevel_page_formidable-payments' ) );
	}

	/**
	 * @param FrmTransLiteListHelper $helper
	 * @param stdClass               $item  A payment or subscription row.
	 * @param array                  $atts  The row arguments, including gateways.
	 *
	 * @return string
	 */
	private function get_paysys_column( $helper, $item, $atts ) {
		return $this->run_private_method( array( $helper, 'get_paysys_column' ), array( $item, $atts ) );
	}

	/**
	 * @param string $paysys
	 *
	 * @return stdClass
	 */
	private function payment_row( $paysys ) {
		$item         = new stdClass();
		$item->paysys = $paysys;

		return $item;
	}

	/**
	 * The Processor column is worth sorting by once more than one gateway is in use, and
	 * the payments submodule made it sortable while Lite did not.
	 *
	 * @covers FrmTransLiteListHelper::get_sortable_columns
	 */
	public function test_get_sortable_columns_includes_paysys() {
		$sortable = $this->make_helper()->get_sortable_columns();

		$this->assertArrayHasKey( 'paysys', $sortable, 'The Processor column should be sortable.' );
		$this->assertSame( 'paysys', $sortable['paysys'], 'Sorting by Processor should order by the paysys column.' );
	}

	/**
	 * @covers FrmTransLiteListHelper::get_sortable_columns
	 */
	public function test_get_sortable_columns_keeps_the_columns_it_already_offered() {
		$sortable = $this->make_helper()->get_sortable_columns();

		$expected = array(
			'item_id',
			'amount',
			'created_at',
			'receipt_id',
			'sub_id',
			'begin_date',
			'expire_date',
			'status',
			'next_bill_date',
		);

		foreach ( $expected as $column ) {
			$this->assertArrayHasKey( $column, $sortable, 'The ' . $column . ' column should still be sortable.' );
		}
	}

	/**
	 * Sorting drops the requested orderby straight into an ORDER BY clause, so a sortable
	 * column that is not really in the schema is a SQL error rather than a cosmetic problem.
	 *
	 * @covers FrmTransLiteListHelper::get_sortable_columns
	 */
	public function test_every_sortable_column_exists_in_the_payment_schema() {
		$columns = array_merge(
			array( 'id' ),
			array_keys( ( new FrmTransLitePayment() )->get_defaults() ),
			array_keys( ( new FrmTransLiteSubscription() )->get_defaults() )
		);

		foreach ( array_keys( $this->make_helper()->get_sortable_columns() ) as $sortable ) {
			$this->assertContains( $sortable, $columns, 'The sortable column ' . $sortable . ' should be a real database column.' );
		}
	}

	/**
	 * This lookup is why the submodule no longer needs its own "paypal" special case: a
	 * registered gateway supplies the label for any processor, not just PayPal.
	 *
	 * @covers FrmTransLiteListHelper::get_paysys_column
	 */
	public function test_get_paysys_column_prefers_the_registered_gateway_label() {
		$helper = $this->make_helper();
		$atts   = array(
			'gateways' => array(
				'paypal' => array( 'label' => 'PayPal' ),
				'stripe' => array( 'label' => 'Stripe' ),
			),
		);

		$this->assertSame( 'PayPal', $this->get_paysys_column( $helper, $this->payment_row( 'paypal' ), $atts ), 'A paypal payment should be labelled PayPal.' );
		$this->assertSame( 'Stripe', $this->get_paysys_column( $helper, $this->payment_row( 'stripe' ), $atts ), 'A stripe payment should be labelled Stripe.' );
	}

	/**
	 * @covers FrmTransLiteListHelper::get_paysys_column
	 */
	public function test_get_paysys_column_falls_back_to_the_stored_value_for_an_unregistered_gateway() {
		$atts = array( 'gateways' => array( 'stripe' => array( 'label' => 'Stripe' ) ) );

		$this->assertSame(
			'authnet_aim',
			$this->get_paysys_column( $this->make_helper(), $this->payment_row( 'authnet_aim' ), $atts ),
			'An unregistered gateway should show its stored value.'
		);
	}

	/**
	 * @covers FrmTransLiteListHelper::get_paysys_column
	 */
	public function test_get_paysys_column_falls_back_when_no_gateways_are_registered() {
		$this->assertSame(
			'manual',
			$this->get_paysys_column( $this->make_helper(), $this->payment_row( 'manual' ), array( 'gateways' => array() ) ),
			'With no gateways registered the stored value should be shown.'
		);
	}

	/**
	 * The load-bearing fact behind removing the submodule's hardcoded "paypal" to "PayPal"
	 * mapping. Old PayPal payments are labelled correctly only because this gateway is
	 * still registered with this label, so dropping it would silently turn every historic
	 * PayPal row into "paypal".
	 *
	 * @covers FrmPayPalLiteAppController::add_gateway
	 */
	public function test_the_paypal_gateway_supplies_the_paypal_label() {
		$gateways = FrmPayPalLiteAppController::add_gateway( array() );

		$this->assertArrayHasKey( 'paypal', $gateways, 'A paypal gateway should be registered.' );
		$this->assertSame( 'PayPal', $gateways['paypal']['label'], 'The paypal gateway should be labelled PayPal.' );
	}

	/**
	 * The paysys column reads the gateways out of the row arguments, and display_rows() is
	 * the only thing that puts them there.
	 *
	 * @covers FrmTransLiteListHelper::display_rows
	 */
	public function test_the_registered_gateways_reach_the_paysys_column() {
		if ( false === has_filter( 'frm_payment_gateways', 'FrmPayPalLiteAppController::add_gateway' ) ) {
			add_filter( 'frm_payment_gateways', 'FrmPayPalLiteAppController::add_gateway' );
			$this->added_gateway_filter = true;
		}

		$gateways = FrmTransLiteAppHelper::get_gateways();

		$this->assertArrayHasKey( 'paypal', $gateways, 'The gateways passed to each row should include paypal.' );
		$this->assertSame(
			'PayPal',
			$this->get_paysys_column( $this->make_helper(), $this->payment_row( 'paypal' ), compact( 'gateways' ) ),
			'A paypal row should render as PayPal from the registered gateways.'
		);
	}

	/**
	 * @covers FrmTransLiteListHelper::get_table_query
	 */
	public function test_get_table_query() {
		global $wpdb;

		$list_helper = $this->make_helper();
		$query       = $this->run_private_method( array( $list_helper, 'get_table_query' ), array() );
		$this->assertStringContainsString( 'FROM `' . $wpdb->prefix . 'frm_payments` p', $query );

		$form_id      = $this->factory->form->create();
		$_GET['form'] = $form_id;
		$query        = $this->run_private_method( array( $list_helper, 'get_table_query' ), array() );

		unset( $_GET['form'] );

		$this->assertStringContainsString( 'FROM `' . $wpdb->prefix . 'frm_payments` p', $query );
		$this->assertStringContainsString( 'JOIN `' . $wpdb->prefix . 'frm_items` i ON p.item_id = i.id', $query );
		$this->assertStringContainsString( 'i.form_id = ' . $form_id, $query );
	}

	/**
	 * @covers FrmTransLiteListHelper::get_form_ids
	 */
	public function test_get_form_ids() {
		$form               = $this->factory->form->create_and_get();
		$entry              = $this->factory->entry->create_and_get( $this->factory->field->generate_entry_array( $form ) );
		$list_helper        = $this->make_helper();
		$list_helper->items = array(
			(object) array( 'item_id' => $entry->id ),
		);

		$form_ids = $this->run_private_method( array( $list_helper, 'get_form_ids' ), array() );

		$this->assertArrayHasKey( $entry->id, $form_ids );
		$this->assertEquals( $form->id, $form_ids[ $entry->id ]->form_id );
	}
}
