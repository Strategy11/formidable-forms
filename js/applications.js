( function() {
	/** globals ajaxurl, wp, frmDom */

	if ( 'undefined' === typeof ajaxurl || 'undefined' === typeof wp || 'undefined' === typeof frmDom ) {
		return;
	}

	const __ = wp.i18n.__;
	const { div, tag } = frmDom;
	const { maybeCreateModal, footerButton } = frmDom.modal;
	const { doJsonFetch } = frmDom.ajax;

	const container = document.getElementById( 'frm_applications_container' );
	if ( ! container ) {
		return;
	}

	wp.hooks.addFilter( 'frm_application_card', 'formidable', handleCardHook );

	doJsonFetch( 'get_applications_data' ).then(
		data => {
			const contentWrapper = div({ className: 'frm-applications-index-content' });

			container.innerHTML = '';
			container.appendChild( contentWrapper );

			renderFormidableTemplates( contentWrapper, data.templates );

			const hookName = 'frm_application_render_templates';
			const args = { data };
			wp.hooks.doAction( hookName, contentWrapper, args );
		}
	);

	function renderFormidableTemplates( contentWrapper, templates ) {
		const templatesGrid = div({ className: 'frm_grid_container frm-application-templates-grid' });
		templates.forEach(
			application => templatesGrid.appendChild( createApplicationCard( application ) )
		);

		contentWrapper.appendChild( getTemplatesNav() );
		contentWrapper.appendChild( templatesGrid );
	}

	function getTemplatesNav() {
		const nav = div({ className: 'frm-application-templates-nav' });
		nav.appendChild(
			tag(
				'h3',
				{ text: __( 'Formidable templates', 'formidable' ) }
			)
		);
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

		const searchListener = function( event ) {
			const searchValue = event.target.value.toLowerCase();
			const cards = document.querySelectorAll( '.frm-application-card' );
			cards.forEach(
				card => {
					const isHidden = -1 === card.textContent.toLowerCase().indexOf( searchValue );
					card.classList.toggle( 'frm_hidden', isHidden );
				}
			);
		};

		searchInput.addEventListener( 'input', searchListener );
		searchInput.addEventListener( 'search', searchListener );
		searchInput.addEventListener( 'change', searchListener );

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

	function handleCardHook( card, args ) {
		return createApplicationCard( args.data );
	}

	function createApplicationCard( data ) {
		const card = div({
			className: 'frm-application-card',
			children: [
				getCardHeader(),
				div({ className: 'frm-flex' })
			]
		});

		function getCardHeader() {
			const title = tag(
				'span',
				{ text: data.name }
			);
			const header = div({
				children: [
					title,
					getUseThisTemplateControl( data ),
					div({ text: data.description })
				]
			});
			return header;
		}

		const hookName = 'frm_application_index_card';
		const args     = { data };
		wp.hooks.doAction( hookName, card, args );

		return card;
	}

	function getUseThisTemplateControl( data ) {
		let control = tag( 'a' );
		control.setAttribute( 'href', '#' );
		control.setAttribute( 'role', 'button' );
		control.textContent = __( 'Upgrade Now', 'formidable' );

		control.addEventListener(
			'click',
			event => {
				if ( '#' === control.getAttribute( 'href' ) ) {
					event.preventDefault();
					openViewApplicationModal( data );
				}
			}
		);

		const hookName = 'frm_application_card_control';
		const args = { data };
		control = wp.hooks.applyFilters( hookName, control, args );

		return control;
	}

	function openViewApplicationModal( data ) {
		const modal = maybeCreateModal(
			'frm_view_application_modal',
			{
				content: getViewApplicationModalContent( data ),
				footer: getViewApplicationModalFooter( data )
			}
		);
		modal.querySelector( '.frm-modal-title' ).textContent = data.name;
		modal.classList.add( 'frm_common_modal' );
	}

	function getViewApplicationModalContent( data ) {
		const output = div({
			children: [
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

		const hookName = 'frm_view_application_modal_content';
		const args     = { data };
		wp.hooks.doAction( hookName, output, args );

		return output;
	}

	function getViewApplicationModalFooter( data ) {
		const viewDemoSiteButton = footerButton({
			text: __( 'View demo site', 'formidable' ),
			buttonType: 'secondary'
		});
		viewDemoSiteButton.href = data.link;
		viewDemoSiteButton.target = '_blank';

		let primaryActionButton = footerButton({
			text: 'Upgrade Now',
			buttonType: 'primary'
		});

		const hookName = 'frm_view_application_modal_primary_action_button';
		const args     = { data };
		primaryActionButton = wp.hooks.applyFilters( hookName, primaryActionButton, args );

		return div({
			children: [ viewDemoSiteButton, primaryActionButton ]
		});
	}
}() );
