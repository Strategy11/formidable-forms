/* eslint-disable sonarjs/no-forced-browser-interaction -- the group click handler only reacts to a click on the row itself, which the field inside covers. */
describe( 'Shift range selection of field groups in the form builder', () => {
	// Seeds the form through PHP: dragging a field into a Section is not something the
	// builder's insert buttons do. wp-env's dev ("cli") and tests ("tests-cli") sites are
	// separate WordPress installs, and which one baseUrl points at depends on how Cypress
	// is launched, so the fields are written to whichever one holds this form.
	const seedFields = formId => {
		const fileName = `frm-shift-range-${ Date.now() }-${ Cypress._.uniqueId() }.php`;
		const code = `<?php
$form = FrmForm::getOne( ${ formId } );
if ( ! $form || 'Test Form' !== $form->name ) {
	return;
}
$types = array(
	'A1'      => 'text',
	'A2'      => 'text',
	'A3'      => 'text',
	'Section' => 'divider',
	'S1'      => 'text',
	'S2'      => 'text',
	'S3'      => 'text',
	'End'     => 'end_divider',
	'B1'      => 'text',
	'B2'      => 'text',
);
$order = 1;
foreach ( $types as $name => $type ) {
	$values               = FrmFieldsHelper::setup_new_vars( $type, ${ formId } );
	$values['name']       = $name;
	$values['field_order'] = $order++;
	FrmField::create( $values );
}`;
		cy.writeFile( fileName, code );
		const pluginName = Cypress.config( 'projectRoot' ).split( '/' ).pop();
		[ 'cli', 'tests-cli' ].forEach( container => {
			cy.exec( `npm --silent run env run ${ container } wp eval-file wp-content/plugins/${ pluginName }/${ fileName }` );
		} );
		cy.exec( `rm ${ fileName }` );
	};

	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.createNewForm();
		cy.viewport( 1280, 720 );
		cy.openForm();

		cy.url().then( url => {
			seedFields( new URL( url ).searchParams.get( 'id' ) );
		} );
		cy.reload();
		cy.get( '#frm-show-fields li.form-field' ).should( 'have.length.at.least', 9 );
	} );

	// openForm() opens the first "Test Form" in the list, so trash this one or the next test reuses it.
	afterEach( () => {
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.deleteForm();
	} );

	// The rows in the order they are seeded above (the Section's end marker has no row of its own).
	const ROWS = [ 'A1', 'A2', 'A3', 'Section', 'S1', 'S2', 'S3', 'B1', 'B2' ];
	const ROW_SELECTOR = '#frm-show-fields li.form-field[data-type="text"], #frm-show-fields li.form-field[data-type="divider"]';

	const getGroup = name => cy.get( ROW_SELECTOR ).eq( ROWS.indexOf( name ) ).closest( 'ul.frm_sorting' );

	// A click on the row itself, not on the field inside it, is what the group handler reads.
	// The hover target is normally set by mouse movement, so set it directly.
	const clickGroup = ( name, shiftKey = false ) => {
		getGroup( name ).then( $group => {
			$group[ 0 ].ownerDocument.querySelectorAll( '.frm-field-group-hover-target' ).forEach( target => target.classList.remove( 'frm-field-group-hover-target' ) );
			$group.addClass( 'frm-field-group-hover-target' );
			cy.wrap( $group ).trigger( 'click', { shiftKey, force: true } );
		} );
	};

	const shiftRange = ( first, second ) => {
		clickGroup( first );
		clickGroup( second, true );
	};

	const expectSelected = names => {
		cy.get( '#frm-show-fields ul.frm-selected-field-group > li.form-field:not([data-type="end_divider"])' ).should( $fields => {
			const rows = Array.from( $fields[ 0 ].ownerDocument.querySelectorAll( ROW_SELECTOR ) );
			const selected = $fields.toArray().map( field => ROWS[ rows.indexOf( field ) ] );
			expect( selected ).to.have.members( names );
		} );
	};

	it( 'selects only the Section rows up to the clicked one when going from a field before the Section', () => {
		shiftRange( 'A1', 'S2' );
		expectSelected( [ 'A1', 'A2', 'A3', 'S1', 'S2' ] );
	} );

	it( 'selects backward from a Section row to a field before the Section', () => {
		shiftRange( 'S2', 'A1' );
		expectSelected( [ 'A1', 'A2', 'A3', 'S1', 'S2' ] );
	} );

	it( 'selects backward from a field after the Section to a Section row', () => {
		shiftRange( 'B1', 'S2' );
		expectSelected( [ 'S2', 'S3', 'B1' ] );
	} );

	it( 'keeps selecting within a single Section the same way', () => {
		shiftRange( 'S1', 'S3' );
		expectSelected( [ 'S1', 'S2', 'S3' ] );
	} );

	it( 'selects the whole Section when the range spans it', () => {
		shiftRange( 'A1', 'B2' );
		expectSelected( [ 'A1', 'A2', 'A3', 'Section', 'S1', 'S2', 'S3', 'B1', 'B2' ] );
	} );
} );
