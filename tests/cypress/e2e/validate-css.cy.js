describe( 'Run some CSS validation', () => {
	const arrayBufferToJsonObject = arrayBuffer => {
		const decoder = new TextDecoder( 'utf-8' );
		const jsonString = decoder.decode( arrayBuffer );
		return JSON.parse( jsonString );
	};

	const validateCSS = url => {
		return cy.request( url ).then( response => {
			const css = response.body;
			const formData = new FormData();
			formData.append( 'text', css );
			formData.append( 'profile', 'css3svg' );
			formData.append( 'output', 'json' );

			return cy.request( {
				method: 'POST',
				url: 'https://jigsaw.w3.org/css-validator/validator',
				body: formData,
				headers: {
					'Content-Type': 'multipart/form-data',
				},
			} ).then( validationResponse => {
				expect( validationResponse.status ).to.eq( 200 );

				const jsonObject = arrayBufferToJsonObject( validationResponse.body );

				expect( jsonObject.cssvalidation ).to.be.an( 'object' );
				const validationResults = jsonObject.cssvalidation;
				const validationErrors = validationResults.errors || [];

				const exceptions = [ 'noexistence-at-all' ];
				const errors = [];

				validationErrors.forEach(
					validationError => {
						if ( ! exceptions.includes( validationError.type ) ) {
							errors.push( validationError.message + ' on line ' + validationError.line + ' (' + validationError.type + ')' );
						}
					}
				);

				// Fail the test if there are validation errors
				if ( errors.length ) {
					console.log( 'CSS Validation errors for ' + url, errors );
					throw new Error( 'CSS validation errors found' );
				}
			} );
		} );
	};

	const formidableFolder = Cypress.env( 'FORMIDABLE_FOLDER' ) || 'formidable';

	it( 'Check frm_admin.css for valid CSS', () => {
		validateCSS( '/wp-content/plugins/' + formidableFolder + '/css/frm_admin.css' );
	} );

	it( 'Check generated CSS for valid CSS', () => {
		validateCSS( '/wp-content/plugins/' + formidableFolder + '/css/formidableforms.css' );
	} );
} );
