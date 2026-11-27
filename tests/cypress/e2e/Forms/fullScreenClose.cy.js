describe( 'Full-screen navigation during sales', () => {
	it( 'uses the header exit when a sale banner also offers Close', () => {
		cy.document().then( doc => {
			doc.body.innerHTML = `
				<a class="dismiss" href="#sale" aria-label="Close">Dismiss sale</a>
				<div class="frm-full-close"><a href="#/forms" aria-label="Close">Exit builder</a></div>
			`;
		} );

		cy.closeFullScreen();
		cy.location( 'hash' ).should( 'eq', '#/forms' );
	} );
} );
