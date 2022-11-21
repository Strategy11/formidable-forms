( function() {

	const { __ } = wp.i18n;
	const state = {
		showingSampleForm: false
	};

	document.addEventListener( 'click', handleClickEvents );

	function handleClickEvents( event ) {
		const target = event.target;

		if ( target.classList.contains( 'frm_style_card' ) ) {
			handleStyleCardClick( event );
			return;
		}

		if ( 'frm_toggle_sample_form' === target.id ) {
			toggleSampleForm();
			return;
		}

		if ( 'frm_submit_side_top' === target.id ) {
			saveActiveStyle();
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

		const sampleForm = document.getElementById( 'frm_sample_form' ).querySelector( '.frm_forms' );
		sampleForm.classList.remove( activeCard.dataset.classname );
		sampleForm.classList.add( event.target.dataset.classname );

		document.getElementById( 'frm_style_form' ).querySelector( '[name="style_id"]' ).value = event.target.dataset.styleId;
	}

	function toggleSampleForm() {
		state.showingSampleForm = ! state.showingSampleForm;

		document.getElementById( 'frm_active_style_form' ).classList.toggle( 'frm_hidden', state.showingSampleForm );
		document.getElementById( 'frm_sample_form' ).classList.toggle( 'frm_hidden', ! state.showingSampleForm );
		document.getElementById( 'frm_toggle_sample_form' ).textContent = state.showingSampleForm ? __( 'Disable sample form', 'formidable' ) : __( 'View sample form', 'formidable' );
	}

	function saveActiveStyle() {
		document.getElementById( 'frm_style_form' ).submit();
	}
}() );
