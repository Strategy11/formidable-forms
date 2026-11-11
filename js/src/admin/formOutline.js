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
 * Add a form outline to the builder: the Outline tab in the side panel lists the form's pages,
 * sections and fields in order, and jumps to any of them.
 *
 * @since x.x
 * @return {void}
 */
export function initFormOutline() {
	const popover = document.getElementById( 'frm-builder-outline' );
	const toggle = document.getElementById( 'frm-builder-outline-toggle' );
	const tab = toggle?.closest( 'li' );
	const builder = document.getElementById( 'frm-show-fields' );
	if ( ! popover || ! tab || ! builder ) {
		return;
	}

	const search = document.getElementById( 'frm-builder-outline-search-input' );
	const list = popover.querySelector( '.frm-builder-outline-list' );
	const emptyMessage = popover.querySelector( '.frm-builder-outline-empty' );
	const emptyTitle = emptyMessage.querySelector( '.frm-builder-outline-empty-title' );
	const emptyText = emptyMessage.querySelector( '.frm-builder-outline-empty-text' );
	const status = popover.querySelector( '.frm-builder-outline-status' );
	const isOpen = () => Boolean( tab?.classList.contains( 'frm-active' ) );
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

		const makeButton = ( className, text, field, pageIndex ) => {
			const button = tag( 'button', {
				className,
				child: span( { className: 'frm-builder-outline-label', text } )
			} );
			button.type = 'button';
			button.title = text;
			button.dataset.fieldId = field.dataset.fid;
			button.dataset.pageIndex = pageIndex;
			return tag( 'li', { child: button } );
		};

		pages.forEach( page => {
			const pageMatches = page.title.toLocaleLowerCase().includes( query );
			const [ firstField ] = page.fields;
			if ( ! firstField ) {
				return;
			}

			// Sections and fields in form order. With a search, only the ones that match.
			const rows = [];
			page.fields.forEach( field => {
				const { type } = field.dataset;
				if ( type === 'end_divider' ) {
					return;
				}
				const text = getFieldText( field );
				if ( ! text ) {
					return;
				}
				const isSection = type === SECTION_TYPE;
				if ( query !== '' ) {
					const matches = text.toLocaleLowerCase().includes( query ) || field.dataset.fid.includes( query );
					if ( ! matches && ! ( isSection && pageMatches ) ) {
						return;
					}
					if ( ! isSection && fieldResultCount >= MAX_FIELD_RESULTS ) {
						return;
					}
				}
				// A page named after its first section would list that name twice in a row.
				if ( isSection && field === firstField && text === page.title && pages.length > 1 ) {
					return;
				}
				rows.push( makeButton( isSection ? 'frm-builder-outline-section' : 'frm-builder-outline-field', text, field, page.index ) );
				if ( ! isSection && query !== '' ) {
					fieldResultCount++;
				}
			} );

			if ( query !== '' && ! pageMatches && rows.length === 0 ) {
				return;
			}

			// A one-page form has no pages to show, only its sections and fields.
			if ( pages.length > 1 ) {
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
			}
			children.push( ...rows );
		} );

		const resultCount = children.length;
		const hasFields = pages.some( page => page.fields.length > 0 );
		const noResults = query !== '' && resultCount === 0;
		const showEmpty = noResults || ! hasFields;
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
			emptyTitle.textContent = __( 'No fields yet', 'formidable' );
			emptyText.textContent = __( 'Fields you add to this form will show up here, so you can jump to any of them.', 'formidable' );
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

	// Track the page at the top of the canvas, so the outline can mark it.
	const updateCurrentPage = () => {
		if ( ! isOpen() ) {
			return;
		}
		const { top } = scroller.getBoundingClientRect();
		let index = -1;
		getPagePills().forEach( pill => {
			if ( pill.offsetParent && pill.getBoundingClientRect().top < top + 8 ) {
				index = Math.max( index, getPillPage( pill ) );
			}
		} );
		if ( currentPageIndex !== Math.max( index, 0 ) ) {
			currentPageIndex = Math.max( index, 0 );
			render();
		}
	};

	let scrollFrame;
	const queueCurrentPageUpdate = () => {
		window.cancelAnimationFrame( scrollFrame );
		scrollFrame = window.requestAnimationFrame( updateCurrentPage );
	};

	// The tab navigator switches tabs by class, from clicks, keys and code, so watch the class.
	new MutationObserver( updateOutline ).observe( tab, { attributes: true, attributeFilter: [ 'class' ] } );
	scroller.addEventListener( 'scroll', queueCurrentPageUpdate, { passive: true } );
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
