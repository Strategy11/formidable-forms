// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

/**
 * Dismiss every inbox banner message so the admin header is predictable.
 *
 * The inbox banner, the sale banner and the Lite upgrade bar share a single header slot, and
 * FrmAppHelper::print_admin_banner() gives the inbox banner priority. Since inbox messages come
 * from the live API, an unrelated announcement hides the upgrade bar for as long as it runs.
 *
 * @param {number} remainingAttempts Guards against looping when a message refuses to dismiss.
 */
Cypress.Commands.add( 'dismissInboxBanners', ( remainingAttempts = 5 ) => {
	if ( remainingAttempts < 1 ) {
		return;
	}

	cy.get( 'body' ).then( $body => {
		const banner = $body.find( '#frm_banner' );

		if ( ! banner.length ) {
			return;
		}

		cy.log( `Dismiss inbox banner: ${ banner.attr( 'data-key' ) }` );
		cy.window().then( win => {
			cy.request( {
				method: 'POST',
				url: '/wp-admin/admin-ajax.php',
				form: true,
				body: {
					action: 'frm_inbox_dismiss',
					key: banner.attr( 'data-key' ),
					nonce: win.frmGlobal.nonce
				}
			} );
		} );

		cy.reload();
		cy.dismissInboxBanners( remainingAttempts - 1 );
	} );
} );

Cypress.Commands.add( 'createNewForm', () => {
	cy.log( 'Create a blank form' );
	cy.contains( '.frm_nav_bar .button-primary', 'Add New' ).click();
	cy.get( '.frm-list-grid-layout #frm-form-templates-create-form' ).should( 'contain', 'Create a blank form' ).click();
	cy.get( '#frm_submit_side_top', { timeout: 5000 } ).should( 'contain', 'Save' ).click();
	cy.get( '#frm-form-templates-modal' ).should( 'exist' );
	cy.get( '.frm-modal-title' ).should( 'contain', 'Name your form' );
	cy.get( '#frm_new_form_name_input' ).type( 'Test Form' );
	cy.get( '#frm-save-form-name-button' ).should( 'contain', 'Save' ).click();
	cy.get( "a[aria-label='Close']", { timeout: 7000 } ).click();
} );

/**
 * Ensure the "Contact Us" template form (frm_key `contact-form`) exists, creating it if needed.
 *
 * Several specs preview this form directly by key without creating it themselves, relying on
 * `Form Templates/FormTemplates.cy.js` having already created it in the same wp-env instance.
 * That only holds when both specs land in the same CI shard, which isn't guaranteed - shards are
 * bin-packed by spec file line count (see tests/bin/split-specs.sh), so adding or resizing any
 * spec file can split them apart. Call this instead of assuming the fixture is already there.
 */
Cypress.Commands.add( 'ensureContactUsFormExists', () => {
	cy.visit( '/wp-admin/admin.php?page=formidable' );
	cy.get( 'body' ).then( $body => {
		if ( $body.find( '#the-list tr:contains("Contact Us")' ).length > 0 ) {
			cy.log( 'Contact Us form already exists' );
			return;
		}

		cy.log( 'Create the Contact Us form from its template' );
		cy.visit( '/wp-admin/admin.php?page=formidable-form-templates' );
		cy.contains( 'li', 'Contact Us', { timeout: 10000 } )
			.first()
			.trigger( 'mouseover', { force: true } )
			.find( '.frm-form-templates-use-template-button' )
			.should( 'contain', 'Use Template' )
			.click( { force: true } );

		cy.get( "svg[aria-label='Close']", { timeout: 7000 } ).click( { force: true } );
	} );
} );

Cypress.Commands.add( 'deleteForm', () => {
	cy.log( 'Delete Form' );
	cy.contains( '#the-list tr', 'Test Form' ).trigger( 'mouseover' ).then( $row => {
		console.log( 'Hovered Row:', $row );
		cy.wrap( $row ).within( () => {
			cy.get( '.row-actions .trash .frm-trash-link' ).should( 'be.visible' ).click( { force: true } );
		} );
		cy.get( 'body' ).then( $body => {
			if ( $body.find( "div[role='dialog']" ).length ) {
				cy.get( "div[role='dialog']" ).should( 'be.visible' ).and( 'contain.text', 'Do you want to move this form to the trash?' );
				cy.xpath( "//a[@id='frm-confirmed-click']" ).should( 'contain.text', 'Confirm' ).click( { force: true } );
			} else {
				cy.log( 'Dialog not found' );
			}
		} );
	} );
} );

Cypress.Commands.add( 'openForm', () => {
	cy.log( 'Click on the created form' );
	cy.contains( '#the-list tr', 'Test Form' ).trigger( 'mouseover' ).then( $row => {
		cy.wrap( $row ).within( () => {
			cy.get( '.column-name .row-title' ).should( 'exist' ).and( 'be.visible' ).then( $elem => {
				console.log( 'Element is:', $elem );
				cy.wrap( $elem ).click( { force: true } );
			} );
		} );
	} );

	cy.get( 'h1 > .frm_bstooltip' ).should( 'contain', 'Test Form' );
	cy.get( '.current_page' ).should( 'contain', 'Build' );
	cy.xpath( "//li[@class='frm-active']//a[@id='frm_insert_fields_tab']" ).should( 'contain', 'Add Fields' );
} );

Cypress.Commands.add( 'getCurrentFormattedDate', () => {
	const currentDate = new Date();
	return currentDate.toISOString().split( 'T' )[ 0 ].replace( /-/g, '/' );
} );

Cypress.Commands.add( 'emptyTrash', () => {
	cy.log( 'Precondition - Clear trash if there are deleted forms in the list' );
	cy.get( '.subsubsub > .trash > a' ).then( $trashLink => {
		if ( $trashLink.text().includes( 'Trash' ) ) {
			cy.wrap( $trashLink ).click();
			cy.get( 'body' ).then( $body => {
				if ( $body.find( '#delete_all' ).length > 0 ) {
					cy.get( '#delete_all' ).should( 'contain', 'Empty Trash' ).click();
				} else {
					cy.log( 'No forms available to delete.' );
				}
			} );
		} else {
			cy.log( 'No forms in the Trash.' );
		}
	} );
} );

// Runs the IBM Equal Access scan alongside the existing cypress-axe checks. Doesn't
// fail the build yet (assertCompliance(false)) since the current admin/preview
// markup hasn't been triaged against this rule set - see formidable-forms#3356.
Cypress.Commands.add( 'checkIbmAccessibility', label => {
	cy.getCompliance( label ).then( report => {
		const violations = report.results.filter( result => result.level !== 'pass' );

		if ( ! violations.length ) {
			return report;
		}

		// Chain the logging tasks and resolve back to `report` at the end, rather than
		// invoking cy commands and then returning `report` synchronously - Cypress
		// treats mixing queued async commands with a sync return in the same callback
		// as an error, which aborted the scan and (since retries are enabled) caused a
		// same-labeled retry to collide with this scan's already-recorded label.
		cy.task(
			'log',
			`${ violations.length } IBM Equal Access violation${ violations.length === 1 ? '' : 's' } detected (${ label })`
		);

		return cy.task( 'table', violations.map( ( { ruleId, level, message } ) => ( { ruleId, level, message } ) ) ).then( () => report );
	} ).assertCompliance( false );
} );
