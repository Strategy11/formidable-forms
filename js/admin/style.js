( function() {
	document.addEventListener( 'click', handleClickEvents );

	function handleClickEvents( event ) {
		if ( event.target.classList.contains( 'frm_style_card' ) ) {
			handleStyleCardClick( event );
		}
	}

	function handleStyleCardClick( event ) {
		const sidebar     = document.getElementById( 'frm_style_sidebar' );
		const previewArea = sidebar.nextElementSibling;
		const form        = previewArea.querySelector( 'form' );

		const activeCard = document.querySelector( '.frm_active_style_card' );
		activeCard.classList.remove( 'frm_active_style_card' );
		form.parentNode.classList.remove( activeCard.dataset.classname );

		form.parentNode.classList.add( event.target.dataset.classname );
		event.target.classList.add( 'frm_active_style_card' );
	}
}() );
