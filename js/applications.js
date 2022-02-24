( function() {
	/** globals ajaxurl */

	if ( 'undefined' === typeof ajaxurl ) {
		return;
	}

	const container = document.getElementById( 'frm_applications_container' );
	if ( ! container ) {
		return;
	}

	doJsonFetch( 'get_applications_data' ).then(
		data => renderApplications( data.applications )
	);

	async function doJsonFetch( action ) {
		const response = await fetch( ajaxurl + '?action=frm_' + action );
		const json = await response.json();
		if ( ! json.success ) {
			return Promise.reject( 'JSON result is not successful' );
		}
		return Promise.resolve( json.data );
	}

	function renderApplications( applications ) {
		const templatesNav = getTemplatesNav();

		const templatesGrid = div({ className: 'frm_grid_container frm-application-templates-grid' });
		applications.forEach(
			application => templatesGrid.appendChild( createApplicationCard( application ) )
		);

		const contentWrapper = div({
			className: 'frm-applications-index-content',
			children: [ templatesNav, templatesGrid ]
		});

		container.innerHTML = '';
		container.appendChild( contentWrapper );
	}

	function getTemplatesNav() {
		const nav = div({ className: 'frm-application-templates-nav' });
		// TODO __ i18n.
		const title = document.createElement( 'h3' );
		title.textContent = 'Formidable templates';
		nav.appendChild( title );
		return nav;
	}

	function createApplicationCard( data ) {
		const card = div({
			className: 'frm-application-card',
			children: [ getCardHeader(), document.createElement( 'hr' ), getCardContent() ]
		});

		function getCardHeader() {
			const header = div({
				children: [ document.createTextNode( data.name ), getUseThisTemplateControl( data ) ]
			});
			return header;
		}

		function getCardContent() {
			const image = document.createElement( 'img' );
			image.setAttribute( 'src', data.icon );
			const content = div({
				children: [ image ]
			});
			return content;
		}

		return card;
	}

	function getUseThisTemplateControl( data ) {
		const control = document.createElement( 'a' );
		// TODO __ i18n.
		control.setAttribute( 'href', '#' );
		control.setAttribute( 'role', 'button' );
		control.textContent = 'Use this template';

		onClickPreventDefault(
			control,
			() => {

			}
		);

		return control;
	}

	function onClickPreventDefault( element, callback ) {
		element.addEventListener(
			'click',
			function( event ) {
				event.preventDefault();
				callback( event );
			}
		);
	}

	function div({ className, children } = {}) {
		const output = document.createElement( 'div' );
		if ( className ) {
			output.className = className;
		}
		if ( children ) {
			children.forEach( child => output.appendChild( child ) );
		}
		return output;
	}

	function text( content ) {
		return document.createTextNode( content );
	}
}() );
