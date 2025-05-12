( function() {
    const buttons = document.querySelectorAll( '.frm-connect-square-with-oauth' );
    buttons.forEach( function( button ) {
        button.addEventListener( 'click', function() {
            const formData = new FormData();
            formData.append( 'mode', button.dataset.mode );
            frmDom.ajax.doJsonPost( 'square_oauth', formData ).then(
                function( response ) {
                    if ( 'undefined' !== typeof response.redirect_url ) {
                        window.location = response.redirect_url;
                    }
                }
            );
        } );
    } );
}() );
