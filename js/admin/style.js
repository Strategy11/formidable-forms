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

		Array.from( document.getElementsByClassName( 'frm_style_card' ) ).forEach(
			card => form.parentNode.classList.remove( card.dataset.classname )
		);

		form.parentNode.classList.add( event.target.dataset.classname );
	}
}() );
