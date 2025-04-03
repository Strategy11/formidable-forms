( function() {
    const button = document.getElementById( 'frm_connect_square_with_oauth' );
    if ( button ) {
        button.addEventListener( 'click', function() {
            const formData = new FormData();
            frmDom.ajax.doJsonPost( 'square_oauth', formData ).then(
                function( response ) {
                    if ( 'undefined' !== typeof response.redirect_url ) {
                        window.location = response.redirect_url;
                    }
                }
            );
        } );
    }
}() );
