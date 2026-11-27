describe( 'Product total with priced and non-priced dropdowns', () => {
	const formKey = 'frm-product-select-change';

	// Same approach as singleProductFieldLiveUpdate.cy.js: write a PHP file into the
	// mounted plugin folder and run it in both wp-env sites, since baseUrl can point at either.
	const runPhp = php => {
		const fileName = `frm-product-total-${ Date.now() }-${ Cypress._.uniqueId() }.php`;
		cy.writeFile( fileName, `<?php ${ php }` );
		const pluginName = Cypress.config( 'projectRoot' ).split( '/' ).pop();
		[ 'cli', 'tests-cli' ].forEach( container => {
			cy.exec( `npm --silent run env run ${ container } wp eval-file wp-content/plugins/${ pluginName }/${ fileName }`, { timeout: 120000 } );
		} );
		cy.exec( `rm ${ fileName }` );
	};

	const deleteForm = `$frm_id = FrmForm::get_id_by_key( '${ formKey }' ); if ( $frm_id ) { FrmForm::destroy( $frm_id ); }`;

	const addField = ( type, values ) => `$frm_vals = FrmFieldsHelper::setup_new_vars( '${ type }', $frm_form_id ); FrmField::create( array_merge( $frm_vals, ${ values } ) );`;

	before( () => {
		runPhp(
			[
				// Make sure the tables exist, since this can be the first spec to run on a fresh site.
				'FrmAppController::install();',
				deleteForm,
				`$frm_form_id = FrmForm::create( array( 'name' => 'Product dropdown total', 'description' => '', 'form_key' => '${ formKey }', 'status' => 'published' ) );`,
				addField( 'product', `array( 'name' => 'Size', 'field_key' => 'frm-priced-select', 'field_order' => 1, 'options' => array( array( 'label' => 'Small', 'value' => 'Small', 'price' => '10' ), array( 'label' => 'Large', 'value' => 'Large', 'price' => '15' ) ), 'field_options' => array( 'data_type' => 'select' ) )` ),
				addField( 'select', `array( 'name' => 'Color', 'field_key' => 'frm-plain-select', 'field_order' => 2, 'options' => array( 'Red', 'Blue' ) )` ),
				addField( 'total', `array( 'name' => 'Total', 'field_key' => 'frm-total', 'field_order' => 3 )` )
			].join( ' ' )
		);
	} );

	after( () => {
		runPhp( deleteForm );
	} );

	beforeEach( () => {
		cy.login();
		cy.visit( `/wp-admin/admin-ajax.php?action=frm_forms_preview&form=${ formKey }` );
	} );

	it( 'recalculates the total for a priced dropdown only', () => {
		cy.get( '#field_frm-priced-select' ).select( 'Large' );
		cy.get( '[data-frmtotal]' ).should( $total => {
			expect( parseFloat( $total.val() ) ).to.equal( 15 );
		} );

		// A value the calculation would overwrite, so any recalculation shows.
		cy.get( '[data-frmtotal]' ).invoke( 'val', '999' );
		cy.get( '#field_frm-plain-select' ).select( 'Blue' );
		cy.get( '[data-frmtotal]' ).should( 'have.value', '999' );

		cy.get( '#field_frm-priced-select' ).select( 'Small' );
		cy.get( '[data-frmtotal]' ).should( $total => {
			expect( parseFloat( $total.val() ) ).to.equal( 10 );
		} );
	} );
} );
