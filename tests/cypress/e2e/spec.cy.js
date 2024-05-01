const login = name => {
	cy.getCookies().then(cookies => {
        let hasMatch = false;
        cookies.forEach((cookie) => {
            if (cookie.name.substr(0, 20) === 'wordpress_logged_in_') {
                hasMatch = true;
            }
        });
        if ( ! hasMatch ) {
            cy.visit( 'http://localhost:8889/wp-login.php' ).wait( 1000 );

			// User login.
			cy.get( '#user_login' ).type( name );

			// User pass.
			cy.get( '#user_pass' ).type( 'password' );

			// WP submit.
			cy.get( '#wp-submit' ).click();
        }
	});
};

describe( 'Configure WordPress', function() {
    before(function () {
        login( 'admin' );
    });

    it('Can visit admin page', function() {
        cy.visit( 'http://localhost:8889/wp-admin' );
    });

});

