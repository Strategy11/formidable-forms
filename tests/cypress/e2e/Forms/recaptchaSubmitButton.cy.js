// Coverage for the invisible-reCAPTCHA submit-button behavior: the submit button disables
// while the check runs, and re-enables after a 10s stall without touching other in-flight forms.
// No live Google dependency is needed: grecaptcha is stubbed and the real widget script is
// blocked so the test only exercises Formidable's own JS.
describe( 'Invisible reCAPTCHA submit button state', () => {
	const stubGrecaptcha = win => {
		win.grecaptcha = {
			getResponse: () => '',
			execute: () => {}, // skipcq: JS-0057 -- intentional no-op stub, formidable.js doesn't use execute()'s return value
			reset: () => {} // skipcq: JS-0057 -- intentional no-op stub, nothing reads reset()'s return value
		};
	};

	let previewUrl;

	// wp-admin's own user-profile.js throws an unrelated `reading 'serialize'` error on some
	// re-logins in this environment (pre-existing WP-core quirk, unconnected to this fix) -
	// don't let it fail the teardown login in after().
	Cypress.on( 'uncaught:exception', err => ! err.message.includes( "reading 'serialize'" ) );

	before( () => {
		cy.login();

		cy.log( 'Configure a fake reCAPTCHA key so the invisible captcha field renders' );
		cy.visit( '/wp-admin/admin.php?page=formidable-settings&t=captcha_settings' );
		cy.get( '#frm-recaptcha' ).check( { force: true } );
		cy.get( '#frm_pubkey' ).clear().type( 'test-site-key' );
		cy.get( '#frm_privkey' ).clear().type( 'test-secret-key' );
		cy.get( '#frm_re_type' ).select( 'invisible' );
		cy.get( 'input[type="submit"][value="Update"]' ).click();

		cy.log( 'Create a form with an invisible reCAPTCHA field' );
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.contains( '.frm_nav_bar .button-primary', 'Add New' ).click();
		cy.get( '.frm-list-grid-layout #frm-form-templates-create-form' ).should( 'contain', 'Create a blank form' ).click();
		cy.get( '#frm_submit_side_top', { timeout: 5000 } ).should( 'contain', 'Save' ).click();
		cy.get( '#frm_new_form_name_input' ).type( 'Test Form' );
		cy.get( '#frm-save-form-name-button' ).should( 'contain', 'Save' ).click();
		cy.get( 'li[id="captcha"] a[title="Captcha"]', { timeout: 5000 } ).click( { force: true } );
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(1) > a' )
			.should( 'contain', 'On Blank Page' )
			.invoke( 'attr', 'href' )
			.then( href => {
				previewUrl = href;
			} );
	} );

	beforeEach( () => {
		// The real widget script would auto-render any `.g-recaptcha` element it finds and
		// fight the stub above - block it outright, same as the issue's suggested scope.
		cy.intercept( '**/recaptcha/api.js*', '' );
	} );

	after( () => {
		cy.log( 'Teardown - delete the form and clear the reCAPTCHA key' );
		// Test isolation clears cookies between tests, so the session from before() is gone by now.
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.deleteForm();

		cy.visit( '/wp-admin/admin.php?page=formidable-settings&t=captcha_settings' );
		cy.get( '#frm_pubkey' ).clear();
		cy.get( '#frm_privkey' ).clear();
		cy.get( '#frm_re_type' ).select( '' );
		cy.get( 'input[type="submit"][value="Update"]' ).click();
	} );

	function getSubmitButton() {
		return cy.get( '.frm_forms' ).find( "button[type='submit'], input[type='submit']" ).filter( ':visible' ).first();
	}

	function submitInvisibleRecaptchaForm() {
		getSubmitButton().click();
	}

	it( 'disables the submit button as soon as the invisible reCAPTCHA check begins', () => {
		cy.visit( previewUrl, { onBeforeLoad: stubGrecaptcha } );

		submitInvisibleRecaptchaForm();

		cy.get( 'form.frm-show-form' ).should( 'have.class', 'frm_loading_form' );
		getSubmitButton().should( 'be.disabled' );
	} );

	it( 're-enables the submit button after 10s if the reCAPTCHA check stalls', () => {
		cy.visit( previewUrl, { onBeforeLoad: stubGrecaptcha } );
		cy.get( '.g-recaptcha, .frm-g-recaptcha' ).should( 'exist' );
		cy.clock();

		submitInvisibleRecaptchaForm();
		cy.get( 'form.frm-show-form' ).should( 'have.class', 'frm_loading_form' );

		cy.tick( 10000 );

		cy.get( 'form.frm-show-form' ).should( 'not.have.class', 'frm_loading_form' );
		getSubmitButton().should( 'not.be.disabled' );
	} );

	it( "does not re-enable an unrelated in-flight form's loading state when this form's stall fallback fires", () => {
		cy.visit( previewUrl, { onBeforeLoad: stubGrecaptcha } );
		cy.get( '.g-recaptcha, .frm-g-recaptcha' ).should( 'exist' );
		cy.clock();

		cy.log( 'Simulate a second, unrelated form mid-AJAX-submission on the same page' );
		cy.document().then( doc => {
			const otherForm = doc.createElement( 'form' );
			otherForm.className = 'frm_loading_form frm-show-form';
			otherForm.id = 'frm-other-in-flight-form';
			const otherButton = doc.createElement( 'button' );
			otherButton.type = 'submit';
			otherButton.disabled = true;
			otherForm.append( otherButton );
			doc.body.append( otherForm );
		} );

		submitInvisibleRecaptchaForm();
		cy.get( '.frm_forms form.frm-show-form' ).should( 'have.class', 'frm_loading_form' );

		cy.tick( 10000 );

		cy.get( '.frm_forms form.frm-show-form' ).should( 'not.have.class', 'frm_loading_form' );

		cy.log( "The unrelated form's own loading state must be untouched - it is not the form that stalled" );
		cy.get( '#frm-other-in-flight-form' ).should( 'have.class', 'frm_loading_form' );
		cy.get( '#frm-other-in-flight-form button' ).should( 'be.disabled' );
	} );
} );
