describe( 'Single Product field live update in the form builder', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.createNewForm();
		cy.viewport( 1280, 720 );
		cy.openForm();
	} );

	const addSingleProductField = () => {
		// No payment gateway is configured in this test environment; bypass the
		// setup-a-gateway-first modal so the Product field can still be added.
		cy.window().then( win => {
			win.frm_admin_js.paymentsSettingsModal = false;
		} );

		cy.get( 'li[id="product"] a[title="Product"]' ).click( { force: true } );

		return cy.get( 'li[data-ftype="product"]' ).invoke( 'attr', 'data-fid' ).then( fieldId => {
			cy.get( `li[data-ftype="product"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use`, { timeout: 10000 } ).click( { force: true } );
			cy.get( 'li[data-ftype="product"] .frm_select_field > span' ).should( 'contain', 'Field Settings' ).click( { force: true } );
			cy.get( `select[name="field_options[data_type_${ fieldId }]"]`, { timeout: 10000 } ).select( 'single' );

			return cy.wrap( fieldId );
		} );
	};

	const editFirstOption = ( fieldId, label, price ) => {
		cy.get( `#frm_field_${ fieldId }_opts .frm_single_option:not(.frm_option_template)` ).first().within( () => {
			cy.get( `.field_${ fieldId }_option:not(.frm_product_price)` ).clear( { force: true } ).type( label, { force: true } ).blur();
			cy.get( '.frm_product_price' ).clear( { force: true } ).type( price, { force: true } ).blur();
		} );
	};

	it( 'updates the preview label live as the product name/price change', () => {
		addSingleProductField().then( fieldId => {
			editFirstOption( fieldId, 'Widget', '1234.5' );

			cy.get( `#field_${ fieldId }_inner_container .frm_single_product_label` )
				.should( 'contain', 'Widget' )
				.and( 'contain', '1,234.50' );
		} );
	} );

	it( 'formats a comma-decimal price correctly instead of NaN (EUR currency)', () => {
		cy.wpCliEval( '<?php $s = new FrmSettings(); $s->currency = "EUR"; update_option( $s->option_name, $s, true );' );
		cy.reload();

		addSingleProductField().then( fieldId => {
			editFirstOption( fieldId, 'Widget', '19,99' );

			cy.get( `#field_${ fieldId }_inner_container .frm_single_product_label` )
				.should( 'contain', '19,99' )
				.and( 'not.contain', 'NaN' )
				.and( 'not.contain', 'undefined' );
		} );

		cy.wpCliEval( '<?php $s = new FrmSettings(); $s->currency = "USD"; update_option( $s->option_name, $s, true );' );
	} );
} );
