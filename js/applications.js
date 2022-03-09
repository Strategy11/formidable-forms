( function() {
	/** globals ajaxurl, wp, frmDom */

	if ( 'undefined' === typeof ajaxurl || 'undefined' === typeof wp || 'undefined' === typeof frmDom ) {
		return;
	}

	const __ = wp.i18n.__;
	const { div, tag } = frmDom;

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
		const title = document.createElement( 'h3' );
		title.textContent = __( 'Formidable templates', 'formidable' );
		nav.appendChild( title );
		nav.appendChild( getTemplateSearch() );
		return nav;
	}

	function getTemplateSearch() {
		const label = tag(
			'label',
			{
				className: 'screen-reader-text',
				text: __( 'Search applications', 'formidable' )
			}
		);
		label.setAttribute( 'for', 'frm-application-search' );

		const searchInput = tag(
			'input',
			{
				id: 'frm-application-search',
				className: 'frm-search-input'
			}
		);
		searchInput.setAttribute( 'type', 'search' );

		const listener = function( event ) {
			const search = event.target.value.toLowerCase();
			const cards = document.querySelectorAll( '.frm-application-card' );
			cards.forEach(
				card => {
					const isHidden = -1 === card.textContent.toLowerCase().indexOf( search );
					card.classList.toggle( 'frm_hidden', isHidden );
				}
			);
		};

		searchInput.addEventListener( 'input', listener );
		searchInput.addEventListener( 'search', listener );
		searchInput.addEventListener( 'change', listener );

		const search = tag(
			'p',
			{
				className: 'frm-search',
				children: [
					label,
					tag(
						'span',
						{
							className: 'frmfont frm_search_icon'
						}
					),
					searchInput
				]
			}
		);

		return search;
	}

	function createApplicationCard( data ) {
		const card = div({
			className: 'frm-application-card',
			children: [
				getCardHeader(),
				div({ className: 'frm-flex' }),
				document.createElement( 'hr' ),
				getCardContent()
			]
		});

		function getCardHeader() {
			const title = document.createElement( 'span' );
			title.textContent = data.name;
			const header = div({
				children: [
					title,
					getUseThisTemplateControl( data ),
					div({ text: data.description })
				]
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
		control.setAttribute( 'href', '#' );
		control.setAttribute( 'role', 'button' );
		control.textContent = __( 'Use this template', 'formidable' );

		onClickPreventDefault(
			control,
			() => openViewApplicationModal( data )
		);

		return control;
	}

	function openViewApplicationModal( data ) {
		const modalId = 'frm_view_application_modal';
		const modal = frmDom.modal.maybeCreateModal(
			modalId,
			{
				title: data.name,
				content: getViewApplicationModalContent( data )
			}
		);
		modal.classList.add( 'frm_common_modal' );
	}

	function getViewApplicationModalContent( data ) {
		const img = document.createElement( 'img' );
		img.src = data.icon;
		return div({
			children: [
				div({
					className: 'frm-application-image-wrapper',
					child: img
				}),
				div({
					className: 'frm-application-modal-details',
					children: [
						div({
							className: 'frm-application-modal-label',
							text: __( 'Description', 'formidable' )
						}),
						div({
							text: data.description
						})
					]
				})
			]
		});
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
}() );
