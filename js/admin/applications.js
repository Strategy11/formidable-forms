( function() {
	/** globals ajaxurl, wp, frmDom */

	if ( 'undefined' === typeof ajaxurl || 'undefined' === typeof wp || 'undefined' === typeof frmDom ) {
		return;
	}

	const __ = wp.i18n.__;
	const { tag, div, span, a, svg } = frmDom;
	const { maybeCreateModal, footerButton } = frmDom.modal;
	const { newSearchInput } = frmDom.search;
	const { doJsonFetch } = frmDom.ajax;
	const { onClickPreventDefault } = frmDom.util;

	const container = document.getElementById( 'frm_applications_container' );
	if ( ! container ) {
		return;
	}

	const state = {
		categories: false,
		templates: false,
		filteredCategory: false
	};
	const elements = {
		noTemplateSearchResultsPlaceholder: false,
		templatesGrid: false,
		activeCategoryAnchor: false
	};

	wp.hooks.addFilter( 'frm_application_card', 'formidable', handleCardHook );
	wp.hooks.addFilter( 'frm_application_card_item_count', 'formidable', filterItemCount );

	initialize();

	function initialize() {
		wp.hooks.addAction( 'frm_application_render_templates', 'formidable', getUrlParamsAndMaybeOpenTemplateModal );
		getApplicationDataAndLoadPage();
	}

	function getUrlParamsAndMaybeOpenTemplateModal( _, { data } = {}) {
		const url       = new URL( window.location.href );
		const urlParams = url.searchParams;
		if ( ! urlParams.get( 'triggerViewApplicationModal' ) ) {
			return;
		}

		const templateKey = urlParams.get( 'template' );
		if ( ! templateKey ) {
			return;
		}

		const template = data.templates.find( template => templateKey === template.key );
		if ( template ) {
			openViewApplicationModal( template );
		}
	}

	function getApplicationDataAndLoadPage() {
		doJsonFetch( 'get_applications_data' ).then( handleApplicationsDataResponse );
	}

	function handleApplicationsDataResponse( data ) {
		state.categories = data.categories;
		state.templates = data.templates;

		const contentWrapper = div({ className: 'frm-applications-index-content' });

		container.innerHTML = '';
		container.appendChild( contentWrapper );

		renderFormidableTemplates( contentWrapper, data.templates );

		const hookName = 'frm_application_render_templates';
		const args = { data };
		wp.hooks.doAction( hookName, contentWrapper, args );
	}

	function renderFormidableTemplates( contentWrapper, templates ) {
		elements.templatesGrid = div({
			id: 'frm_application_templates_grid',
			className: 'frm_grid_container frm-application-cards-grid'
		});
		addTemplatesToGrid( templates );
		contentWrapper.appendChild( getTemplatesNav() );
		contentWrapper.appendChild( elements.templatesGrid );
	}

	function addTemplatesToGrid( templates ) {
		templates.forEach(
			application => elements.templatesGrid.appendChild( createApplicationCard( application ) )
		);
		maybeTriggerSearch();
	}

	function maybeTriggerSearch() {
		const searchInput = document.getElementById( 'frm-application-search' );
		if ( searchInput && '' !== searchInput.value ) {
			const eventArgs = { bubbles: true, cancelable: true };
			const event = new Event( 'input', eventArgs );
			searchInput.dispatchEvent( event );
		}
	}

	function getTemplatesNav() {
		return div({
			className: 'frm-application-templates-nav wrap',
			children: [
				tag(
					'h2',
					{
						text: __( 'Formidable Applications', 'formidable' ),
						className: 'frm-h2'
					}
				),
				getCategoryOptions(),
				getTemplateSearch()
			]
		});
	}

	function getCategoryOptions() {
		if ( state.templates.length < 8 ) {
			// Do not show categories filters until there are at least 8 templates.
			return document.createTextNode( '' );
		}

		const categories = [ getAllItemsCategory() ].concat( state.categories );
		const wrapper = div({ id: 'frm_application_category_filter' });

		categories.forEach( addCategoryToWrapper );
		function addCategoryToWrapper( category, index ) {
			if ( 0 !== index ) {
				wrapper.appendChild( document.createTextNode( '|' ) );
			}
			const anchor = a( category );
			if ( 0 === index ) {
				anchor.classList.add( 'frm-active-application-category' );
				elements.activeCategoryAnchor = anchor;
			}
			onClickPreventDefault(
				anchor,
				() => {
					if ( false !== elements.activeCategoryAnchor ) {
						elements.activeCategoryAnchor.classList.remove( 'frm-active-application-category' );
					}

					handleCategorySelect( category );
					anchor.classList.add( 'frm-active-application-category' );
					elements.activeCategoryAnchor = anchor;
				}
			);
			wrapper.appendChild( anchor );
		}

		return wrapper;
	}

	function getAllItemsCategory() {
		/* translators: %d: Number of application templates. */
		return __( 'All Items (%d)', 'formidable' ).replace( '%d', state.templates.length );
	}

	function handleCategorySelect( category ) {
		state.filteredCategory = category;

		Array.from( elements.templatesGrid.children ).forEach(
			child => child.classList.contains( 'frm-application-card' ) && child.remove()
		);

		if ( getAllItemsCategory() === category ) {
			addTemplatesToGrid( state.templates );
			return;
		}

		addTemplatesToGrid(
			state.templates.filter(
				template => -1 !== template.categories.indexOf( category )
			)
		);
	}

	function getTemplateSearch() {
		const id = 'frm-application-search';
		const placeholder = __( 'Search templates', 'formidable' );
		const targetClassName = 'frm-application-template-card';
		const args = { handleSearchResult: handleTemplateSearch };
		const wrappedInput = newSearchInput( id, placeholder, targetClassName, args );
		return wrappedInput;
	}

	function handleTemplateSearch({ foundSomething, notEmptySearchText }) {
		if ( false === elements.noTemplateSearchResultsPlaceholder ) {
			elements.noTemplateSearchResultsPlaceholder = getNoResultsPlaceholder();
			elements.templatesGrid.appendChild( elements.noTemplateSearchResultsPlaceholder );
		}
		elements.noTemplateSearchResultsPlaceholder.classList.toggle( 'frm_hidden', ! notEmptySearchText || foundSomething );
	}

	function getNoResultsPlaceholder() {
		return div( __( 'No application templates match your search query.', 'formidable' ) );
	}

	function handleCardHook( _, args ) {
		return createApplicationCard( args.data );
	}

	function createApplicationCard( data ) {
		const isTemplate = ! data.termId;
		const card = div({
			className: 'frm-application-card',
			children: [
				getCardHeader(),
				div({ className: 'frm-flex' })
			]
		});

		if ( isTemplate ) {
			card.classList.add( 'frm-application-template-card' );
			card.appendChild( tag( 'hr' ) );
			card.appendChild( getCardContent() );

			card.addEventListener(
				'click',
				event => 'a' !== event.target.nodeName.toLowerCase() && card.querySelector( 'a' ).click()
			);
		}

		const hookName = 'frm_application_index_card';
		const args     = { data };
		wp.hooks.doAction( hookName, card, args );

		function getCardHeader() {
			const titleWrapper = span({
				children: [
					svg({ href: '#frm_lock_simple' }),
					tag( 'h4', { className: 'frm-locked-application-template', text: data.name })
				]
			});
			const header = div({
				children: [
					titleWrapper,
					getUseThisTemplateControl( data )
				]
			});

			const counter = getItemCounter();
			if ( false !== counter ) {
				header.appendChild( counter );
			}

			return header;
		}

		function getItemCounter() {
			const hookName = 'frm_application_card_item_count';
			const args = { data };
			return wp.hooks.applyFilters( hookName, false, args );
		}

		function getCardContent() {
			const thumbnailFolderUrl = frmGlobal.url + '/images/application-thumbnails/';
			const filenameToUse = data.hasLiteThumbnail ? data.key + '.png' : 'placeholder.svg';
			const image = tag( 'img' );
			image.setAttribute( 'src', thumbnailFolderUrl + filenameToUse );
			return div({
				className: 'frm-application-card-image-wrapper',
				child: image
			});
		}

		return card;
	}

	function filterItemCount( counter, { data }) {
		const hasForms = 'undefined' !== typeof data.formCount && '0' !== data.formCount;
		const hasViews = 'undefined' !== typeof data.viewCount && '0' !== data.viewCount;
		const hasPages = 'undefined' !== typeof data.pageCount && '0' !== data.pageCount;

		if ( ! hasForms && ! hasViews && ! hasPages ) {
			return counter;
		}

		counter = div({ className: 'frm-application-item-count' });

		if ( hasForms ) {
			addCount( data.formCount, __( 'Forms', 'formidable' ), __( 'Form', 'formidable' ) );
		}

		if ( hasViews ) {
			addCount( data.viewCount, __( 'Views', 'formidable' ), __( 'View', 'formidable' ) );
		}

		if ( hasPages ) {
			addCount( data.pageCount, __( 'Pages', 'formidable' ), __( 'Page', 'formidable' ) );
		}

		function addCount( countValue, pluralDescriptor, singularDescriptor ) {
			if ( counter.children.length ) {
				counter.appendChild( document.createTextNode( ' | ' ) );
			}

			const descriptor = '1' === countValue ? singularDescriptor : pluralDescriptor;
			counter.appendChild(
				span({ text: countValue + ' ' + descriptor })
			);
		}

		return counter;
	}

	function getUseThisTemplateControl( data ) {
		let control = tag( 'a' );
		control.setAttribute( 'href', '#' );
		control.setAttribute( 'role', 'button' );
		control.textContent = getUpgradeNowText();

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

	function getUpgradeNowText() {
		return __( 'Upgrade Now', 'formidable' );
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
		const children = [];

		if ( data.upgradeUrl ) {
			children.push(
				div({
					className: 'frm_warning_style',
					children: [
						span( __( 'Access to this application requires a license upgrade.', 'formidable' ) ),
						a({
							text: getUpgradeNowText(),
							href: data.upgradeUrl
						})
					]
				})
			);
		}

		children.push(
			div({
				className: 'frm-application-modal-details',
				children: [
					div({
						className: 'frm-application-modal-label',
						text: __( 'Description', 'formidable' )
					}),
					div( data.description )
				]
			})
		);

		const output = div({ children });

		const hookName = 'frm_view_application_modal_content';
		const args     = { data };
		wp.hooks.doAction( hookName, output, args );

		return output;
	}

	function getViewApplicationModalFooter( data ) {
		const viewDemoSiteButton = footerButton({
			text: __( 'Learn More', 'formidable' ),
			buttonType: 'secondary'
		});
		viewDemoSiteButton.href = data.link;
		viewDemoSiteButton.target = '_blank';

		let primaryActionButton = footerButton({
			text: getUpgradeNowText(),
			buttonType: 'primary'
		});

		if ( data.upgradeUrl ) {
			primaryActionButton.classList.remove( 'dismiss' );
			primaryActionButton.setAttribute( 'href', data.upgradeUrl );
		}

		const hookName = 'frm_view_application_modal_primary_action_button';
		const args     = { data };
		primaryActionButton = wp.hooks.applyFilters( hookName, primaryActionButton, args );

		return div({
			children: [ viewDemoSiteButton, primaryActionButton ]
		});
	}
}() );
