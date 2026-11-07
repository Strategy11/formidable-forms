import { __, _n, sprintf } from '@wordpress/i18n';

const PAGE_BREAK_TYPE = 'break';
const SECTION_TYPE = 'divider';
const HTML_FIELD_TYPE = 'html';
const SUBMIT_TYPE = 'submit';
// Layout pieces that sit in the field list but aren't fields people fill in.
const STRUCTURE_TYPES = [ SECTION_TYPE, 'end_divider', PAGE_BREAK_TYPE, SUBMIT_TYPE ];
const MAX_FIELD_RESULTS = 60;

const getHeadingText = field => {
	const heading = field.querySelector( '.frm_opt_container h1, .frm_opt_container h2, .frm_opt_container h3, .frm_opt_container h4, .frm_opt_container h5, .frm_opt_container h6, .frm_description h1, .frm_description h2, .frm_description h3, .frm_description h4, .frm_description h5, .frm_description h6' );
	return heading?.textContent.trim() || '';
};

const getFieldText = field => {
	const headingText = getHeadingText( field );
	if ( headingText ) {
		return headingText;
	}

	const label = field.querySelector( '.frm_builder_field_name' ) || field.querySelector( '.frm_primary_label' );
	if ( ! label ) {
		return '';
	}

	// Some field types print the name straight into the label, so drop the markers that sit beside it.
	const labelClone = label.cloneNode( true );
	labelClone.querySelectorAll( '.frm_required, .frm-collapsed-label' ).forEach( marker => marker.remove() );
	return labelClone.textContent.trim();
};

/**
 * Add a form outline to the builder: a button floating at the bottom right of the canvas that opens a popover
 * to search and jump to pages, sections and fields.
 *
 * @since x.x
 * @return {void}
 */
export function initFormOutline() {
	const popover = document.getElementById( 'frm-builder-outline' );
	const toggle = document.getElementById( 'frm-builder-outline-toggle' );
	const builder = document.getElementById( 'frm-show-fields' );
	if ( ! popover || ! toggle || ! builder ) {
		return;
	}

	const search = document.getElementById( 'frm-builder-outline-search-input' );
	const list = popover.querySelector( '.frm-builder-outline-list' );
	const emptyMessage = popover.querySelector( '.frm-builder-outline-empty' );
	const emptyTitle = emptyMessage.querySelector( '.frm-builder-outline-empty-title' );
	const emptyText = emptyMessage.querySelector( '.frm-builder-outline-empty-text' );
	const status = popover.querySelector( '.frm-builder-outline-status' );
	const isOpen = () => ! popover.hidden;
	const scroller = document.getElementById( 'post-body-content' );
	let currentPageIndex = 0;
	let observer;
	let renderTimeout;

	const getPages = () => {
		const pages = [ { fields: [], breakField: null } ];
		const fields = builder.querySelectorAll( 'li[data-fid]' );
		fields.forEach( field => {
			if ( field.dataset.type === SUBMIT_TYPE ) {
				return;
			}
			if ( field.dataset.type === PAGE_BREAK_TYPE ) {
				pages.push( { fields: [], breakField: field } );
				return;
			}
			pages[ pages.length - 1 ].fields.push( field );
		} );

		return pages.map( ( page, index ) => {
			const heading = page.fields
				.map( getHeadingText )
				.find( text => text !== '' ) || page.fields
				.filter( field => field.dataset.type !== HTML_FIELD_TYPE )
				.map( getFieldText )
				.find( text => text !== '' );
			const title = heading || sprintf( __( 'Page %d', 'formidable' ), index + 1 );
			return { fields: page.fields, breakField: page.breakField, index, title };
		} );
	};

	const render = () => {
		if ( ! isOpen() ) {
			return;
		}

		const query = search.value.trim().toLocaleLowerCase();
		const pages = getPages();
		const { tag, span } = frmDom;
		const children = [];
		let fieldResultCount = 0;

		pages.forEach( page => {
			const pageMatches = page.title.toLocaleLowerCase().includes( query );
			const matchingFields = query === ''
				? []
				: page.fields.filter( field => {
					// Sections already get their own row.
					if ( field.dataset.type === SECTION_TYPE ) {
						return false;
					}
					const fieldText = getFieldText( field ).toLocaleLowerCase();
					return fieldText.includes( query ) || field.dataset.fid.includes( query );
				} );

			const matchingSections = page.fields.filter( field => {
				if ( field.dataset.type !== SECTION_TYPE ) {
					return false;
				}
				if ( query === '' || pageMatches ) {
					return true;
				}

				const sectionText = getFieldText( field ).toLocaleLowerCase();
				return sectionText.includes( query ) || field.dataset.fid.includes( query );
			} );

			if ( query !== '' && ! pageMatches && matchingFields.length === 0 && matchingSections.length === 0 ) {
				return;
			}

			const [ firstField ] = page.fields;
			if ( ! firstField ) {
				return;
			}

			const pageLabel = `${ sprintf( __( 'Page %1$d', 'formidable' ), page.index + 1 ) }: ${ page.title }`;
			const fieldCount = page.fields.filter( field => ! STRUCTURE_TYPES.includes( field.dataset.type ) ).length;
			const pageButton = tag( 'button', {
				className: 'frm-builder-outline-page',
				children: [
					span( { className: 'frm-builder-outline-label', text: pageLabel } ),
					/* translators: %d: Number of fields on the page */
					span( { className: 'frm-builder-outline-meta', text: sprintf( _n( '%d field', '%d fields', fieldCount, 'formidable' ), fieldCount ) } )
				]
			} );
			pageButton.type = 'button';
			pageButton.title = pageLabel;
			if ( page.index === currentPageIndex && query === '' ) {
				pageButton.setAttribute( 'aria-current', 'location' );
			}
			pageButton.dataset.fieldId = firstField.dataset.fid;
			pageButton.dataset.pageIndex = page.index;
			children.push( tag( 'li', { child: pageButton } ) );

			matchingSections.forEach( field => {
				const sectionText = getFieldText( field );
				// A page named after its first section would list that name twice in a row.
				if ( ! sectionText || ( query === '' && field === firstField && sectionText === page.title ) ) {
					return;
				}

				const sectionButton = tag( 'button', {
					className: 'frm-builder-outline-section',
					child: span( { className: 'frm-builder-outline-label', text: sectionText } )
				} );
				sectionButton.type = 'button';
				sectionButton.title = sectionText;
				sectionButton.dataset.fieldId = field.dataset.fid;
				sectionButton.dataset.pageIndex = page.index;
				children.push( tag( 'li', { child: sectionButton } ) );
			} );

			if ( query === '' || fieldResultCount >= MAX_FIELD_RESULTS ) {
				return;
			}

			matchingFields.forEach( field => {
				if ( fieldResultCount >= MAX_FIELD_RESULTS ) {
					return;
				}
				const fieldText = getFieldText( field );
				const fieldButton = tag( 'button', {
					className: 'frm-builder-outline-field',
					child: span( { className: 'frm-builder-outline-label', text: fieldText } )
				} );
				fieldButton.type = 'button';
				fieldButton.title = fieldText;
				fieldButton.dataset.fieldId = field.dataset.fid;
				fieldButton.dataset.pageIndex = page.index;
				children.push( tag( 'li', { child: fieldButton } ) );
				fieldResultCount++;
			} );
		} );

		const resultCount = children.length;
		const hasFields = pages.some( page => page.fields.length > 0 );
		// A lone page with no sections gives nothing to jump to, so explain how to add some instead.
		const hasOutline = pages.length > 1 || resultCount > 1;
		const noResults = query !== '' && resultCount === 0;
		const showEmpty = noResults || ( query === '' && ! hasOutline );
		const fragment = document.createDocumentFragment();
		if ( ! showEmpty ) {
			fragment.append( ...children );
		}
		list.replaceChildren( fragment );
		emptyMessage.classList.toggle( 'frm_hidden', ! showEmpty );
		search.parentElement.classList.toggle( 'frm_hidden', ! hasFields );
		if ( noResults ) {
			/* translators: %s: The search text */
			emptyTitle.textContent = sprintf( __( 'No results for “%s”', 'formidable' ), search.value.trim() );
			emptyText.textContent = __( 'Try a page, section or field name.', 'formidable' );
		} else if ( showEmpty ) {
			emptyTitle.textContent = hasFields ? __( 'No pages or sections yet', 'formidable' ) : __( 'No fields yet', 'formidable' );
			emptyText.textContent = hasFields
				? __( 'Add a Page Break or Section field to split this form into parts. You can still search above to jump to any field.', 'formidable' )
				: __( 'Fields you add to this form will show up here.', 'formidable' );
		}
		if ( query === '' ) {
			status.textContent = sprintf( _n( '%d page', '%d pages', pages.length, 'formidable' ), pages.length );
			return;
		}
		if ( resultCount === 0 ) {
			status.textContent = __( 'No pages or fields found.', 'formidable' );
			return;
		}
		status.textContent = fieldResultCount === MAX_FIELD_RESULTS
			? sprintf( __( 'Showing up to %d matching fields.', 'formidable' ), MAX_FIELD_RESULTS )
			: sprintf( __( '%d matching fields found.', 'formidable' ), fieldResultCount );
	};

	const observeBuilder = () => {
		observer?.disconnect();
		observer = new MutationObserver( () => {
			clearTimeout( renderTimeout );
			renderTimeout = setTimeout( () => {
				// New pages add new sortable lists that need to be observed while the outline is open.
				observeBuilder();
				render();
			}, 100 );
		} );
		builder.querySelectorAll( 'ul.frm_sorting' ).forEach( sortableList => {
			observer.observe( sortableList, { childList: true } );
		} );
	};

	const expandCollapsedPage = pageContainer => {
		const collapseButton = pageContainer?.querySelector( '.frm-collapse-page' );
		if ( pageContainer?.classList.contains( 'frm-page-collapsed' ) ) {
			collapseButton?.click();
		}
	};

	const updateOutline = () => {
		clearTimeout( renderTimeout );
		observer?.disconnect();
		if ( ! isOpen() ) {
			return;
		}
		observeBuilder();
		render();
	};

	// Page 1's pill lives in the fake page above the fields, the others in the page break fields.
	const getPagePills = () => Array.from( document.querySelectorAll( '#frm-fake-page .frm-collapse-page, #frm-show-fields li[data-type="break"] .frm-collapse-page' ) );
	const getPillPage = pill => Number( pill.querySelector( '.frm-page-num' )?.textContent ) - 1;

	const placePopover = () => {
		const rect = toggle.getBoundingClientRect();
		// Line up with the button's right edge, like a menu opened from it.
		popover.style.right = `${ window.innerWidth - rect.right }px`;
		// Open upward, so the list grows away from the button.
		popover.style.bottom = `${ window.innerHeight - rect.top + 8 }px`;
	};

	// Track the page at the top of the canvas, so the outline can mark it.
	const updateDock = () => {
		const pills = getPagePills();
		const { top } = scroller.getBoundingClientRect();
		let index = -1;
		pills.forEach( pill => {
			if ( pill.offsetParent && pill.getBoundingClientRect().top < top + 8 ) {
				index = Math.max( index, getPillPage( pill ) );
			}
		} );
		const pages = getPages();
		const pageChanged = currentPageIndex !== Math.max( index, 0 );
		currentPageIndex = Math.max( index, 0 );
		const page = pages[ currentPageIndex ];
		const hasPages = pages.length > 1 && Boolean( page );
		// The outline only earns its place once the form is split into pages.
		toggle.parentElement.hidden = ! hasPages;
		if ( ! hasPages ) {
			if ( isOpen() ) {
				setOpen( false );
			}
			return;
		}
		if ( isOpen() ) {
			placePopover();
			if ( pageChanged ) {
				render();
			}
		}
	};

	let dockFrame;
	const queueDockUpdate = () => {
		window.cancelAnimationFrame( dockFrame );
		dockFrame = window.requestAnimationFrame( updateDock );
	};

	const setOpen = open => {
		popover.hidden = ! open;
		toggle.setAttribute( 'aria-expanded', String( open ) );
		toggle.classList.toggle( 'frm-active', open );
		updateDock();
		updateOutline();
		if ( open ) {
			search.focus();
		}
	};

	toggle.addEventListener( 'click', () => setOpen( popover.hidden ) );
	document.addEventListener( 'keydown', event => {
		if ( event.key === 'Escape' && ! popover.hidden ) {
			setOpen( false );
			toggle.focus();
		}
	} );
	// Not a modal: the builder stays usable, and clicking anywhere else closes the outline.
	document.addEventListener( 'pointerdown', event => {
		if ( ! popover.hidden && ! popover.contains( event.target ) && ! toggle.contains( event.target ) ) {
			setOpen( false );
		}
	} );
	scroller.addEventListener( 'scroll', queueDockUpdate, { passive: true } );
	window.addEventListener( 'resize', queueDockUpdate );
	// Page breaks load and move with the builder, so recheck the dock when they do.
	new MutationObserver( queueDockUpdate ).observe( builder, { childList: true, subtree: true } );
	updateDock();
	search.addEventListener( 'input', render );
	// Arrow keys move between the search box and the results, like a menu. Safari's Tab skips buttons by default.
	popover.addEventListener( 'keydown', event => {
		const items = Array.from( list.querySelectorAll( 'button[data-field-id]' ) );
		if ( items.length === 0 ) {
			return;
		}
		const index = items.indexOf( document.activeElement );
		let next;
		if ( event.key === 'ArrowDown' ) {
			next = items[ Math.min( index + 1, items.length - 1 ) ];
		} else if ( event.key === 'ArrowUp' ) {
			next = index <= 0 ? search : items[ index - 1 ];
		} else if ( event.key === 'Home' && index >= 0 ) {
			[ next ] = items;
		} else if ( event.key === 'End' && index >= 0 ) {
			next = items[ items.length - 1 ];
		} else if ( event.key === 'Enter' && document.activeElement === search ) {
			// Jump to the top result.
			items[ 0 ].click();
			event.preventDefault();
			return;
		}
		if ( next ) {
			event.preventDefault();
			next.focus();
			next.scrollIntoView( { block: 'nearest' } );
		}
	} );
	list.addEventListener( 'click', event => {
		const button = event.target.closest( 'button[data-field-id]' );
		if ( ! button ) {
			return;
		}
		const field = builder.querySelector( `li[data-fid="${ button.dataset.fieldId }"]` );
		if ( ! field ) {
			return;
		}

		const page = getPages()[ Number( button.dataset.pageIndex ) ];
		if ( page?.breakField ) {
			expandCollapsedPage( page.breakField.closest( 'li.frm_field_box' ) );
		} else {
			expandCollapsedPage( document.getElementById( 'frm-fake-page' ) );
		}

		builder.querySelectorAll( '.frm-builder-outline-target' ).forEach( item => item.classList.remove( 'frm-builder-outline-target' ) );
		// Don't select the field. That would open Field Options, and the outline stays open to jump again.
		field.classList.add( 'frm-builder-outline-target' );
		window.requestAnimationFrame( () => {
			field.scrollIntoView( { behavior: 'smooth', block: 'start' } );
		} );
		window.setTimeout( () => field.classList.remove( 'frm-builder-outline-target' ), 1800 );
	} );
}
