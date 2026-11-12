import { __, _n, sprintf } from '@wordpress/i18n';
import { renderFieldPreviewNow } from './fieldPreviewQueue';

const PAGE_BREAK_TYPE = 'break';
const SECTION_TYPE = 'divider';
const HTML_FIELD_TYPE = 'html';
const SUBMIT_TYPE = 'submit';
// Layout pieces that sit in the field list but aren't fields people fill in.
const STRUCTURE_TYPES = [ SECTION_TYPE, 'end_divider', PAGE_BREAK_TYPE, SUBMIT_TYPE ];
const MAX_FIELD_RESULTS = 60;
const RENDER_DELAY = 100;
// togglePage() in admin.js animates the first rows for 150 ms.
const PAGE_EXPAND_DELAY = 160;

const getFieldContent = field => field.querySelector( ':scope > .frm_inner_field_container, :scope > .divider_section_only > .frm_inner_field_container' ) || field;

const getHeadingText = field => {
	const heading = getFieldContent( field ).querySelector( '.frm_opt_container h1, .frm_opt_container h2, .frm_opt_container h3, .frm_opt_container h4, .frm_opt_container h5, .frm_opt_container h6, .frm_description h1, .frm_description h2, .frm_description h3, .frm_description h4, .frm_description h5, .frm_description h6' );
	return heading?.textContent.trim() || '';
};

const getFieldText = field => {
	const content = getFieldContent( field );
	const label = content.querySelector( '.frm_builder_field_name' ) || content.querySelector( '.frm_primary_label' );
	if ( ! label ) {
		// Offscreen previews retain the name without rendering their controls.
		return field.querySelector( '.frm-deferred-settings-meta input[id^="frm_name_"]' )?.value.trim() || '';
	}

	// Some field types print the name straight into the label, so drop the markers that sit beside it.
	if ( label.classList.contains( 'frm_builder_field_name' ) ) {
		return label.textContent.trim();
	}
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
	const isOpen = () => tab.classList.contains( 'frm-active' );
	const scroller = document.getElementById( 'post-body-content' );
	let currentPageIndex = 0;
	let renderTimeout;
	let searchTimeout;
	let pagesCache;
	let fieldDetails = new WeakMap();
	let pagePills = [];
	let highlightedField;
	let highlightTimeout;
	let jumpTimeout;

	const getFieldDetails = field => {
		if ( ! fieldDetails.has( field ) ) {
			const pendingPreview = field.classList.contains( 'frm_field_preview_pending' );
			const heading = pendingPreview ? '' : getHeadingText( field );
			const text = pendingPreview
				? field.querySelector( '.frm-deferred-settings-meta input[id^="frm_name_"]' )?.value.trim() || ''
				: heading || getFieldText( field );
			fieldDetails.set( field, { heading, text, searchText: text.toLocaleLowerCase() } );
		}
		return fieldDetails.get( field );
	};

	const getPages = () => {
		if ( pagesCache ) {
			return pagesCache;
		}
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

		pagesCache = pages.map( ( page, index ) => {
			const headingField = page.fields.find( field => getFieldDetails( field ).heading );
			const labelField = headingField || page.fields.find( field => field.dataset.type !== HTML_FIELD_TYPE && field.dataset.type !== 'end_divider' && getFieldDetails( field ).text );
			const heading = labelField ? getFieldDetails( labelField ).text : '';
			const title = ( labelField?.dataset.type !== SECTION_TYPE && heading ) || sprintf( __( 'Page %d', 'formidable' ), index + 1 );
			return { fields: page.fields, breakField: page.breakField, index, title };
		} );
		return pagesCache;
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
		let resultCount = 0;

		const makeButton = ( className, text, field, pageIndex, fieldCount ) => {
			const button = tag( 'button', {
				className,
				child: span( { className: 'frm-builder-outline-label', text } )
			} );
			if ( fieldCount !== undefined ) {
				button.append( span( {
					className: 'frm-builder-outline-meta',
					/* translators: %d: Number of fields in the section */
					text: sprintf( _n( '%d field', '%d fields', fieldCount, 'formidable' ), fieldCount )
				} ) );
			}
			button.type = 'button';
			button.title = text;
			button.dataset.fieldId = field.dataset.fid;
			button.dataset.pageIndex = pageIndex;
			return tag( 'li', { child: button } );
		};

		pages.forEach( page => {
			const previousResultCount = resultCount;
			const pageNumber = sprintf( __( 'Page %d', 'formidable' ), page.index + 1 );
			const pageMatches = `${ pageNumber }: ${ page.title }`.toLocaleLowerCase().includes( query );
			const firstField = page.fields[ 0 ] || page.breakField || pages[ 1 ]?.breakField;
			if ( ! firstField ) {
				return;
			}

			// Group by the builder containers, including fields inside layout rows.
			const sections = new Map();
			page.fields.forEach( field => {
				if ( field.dataset.type === SECTION_TYPE ) {
					sections.set( field, [] );
				}
				let section = field.parentElement.closest( 'li[data-type="divider"]' );
				while ( section ) {
					sections.get( section )?.push( field );
					section = section.parentElement.closest( 'li[data-type="divider"]' );
				}
			} );
			const sectionRows = new Map();
			const rows = [];
			page.fields.forEach( field => {
				const { type } = field.dataset;
				if ( type === 'end_divider' ) {
					return;
				}
				if ( query !== '' && type !== SECTION_TYPE && fieldResultCount >= MAX_FIELD_RESULTS ) {
					return;
				}
				const { text, searchText } = getFieldDetails( field );
				if ( ! text ) {
					return;
				}
				const isSection = type === SECTION_TYPE;
				const matches = searchText.includes( query ) || field.dataset.fid.includes( query );
				if ( query !== '' ) {
					if ( ! matches && ! isSection ) {
						return;
					}
				}
				const section = field.parentElement.closest( 'li[data-type="divider"]' );
				const parent = sectionRows.get( section );
				const siblings = parent ? parent.children : rows;
				if ( matches ) {
					resultCount++;
				}
				if ( isSection ) {
					const fieldCount = sections.get( field ).filter( child => ! STRUCTURE_TYPES.includes( child.dataset.type ) ).length;
					const row = makeButton( 'frm-builder-outline-section', text, field, page.index, fieldCount );
					sectionRows.set( field, { row, children: [], matches, siblings } );
					siblings.push( row );
				} else {
					const row = makeButton( 'frm-builder-outline-field', text, field, page.index );
					siblings.push( row );
				}
				if ( ! isSection && query !== '' ) {
					fieldResultCount++;
				}
			} );
			// eslint-disable-next-line unicorn/no-array-reverse -- toReversed() is ES2023 and breaks older browsers. Array.from() already made a copy to reverse.
			Array.from( sectionRows.values() ).reverse().forEach( section => {
				if ( query !== '' && ! section.matches && ! pageMatches && section.children.length === 0 ) {
					section.siblings.splice( section.siblings.indexOf( section.row ), 1 );
					return;
				}
				if ( section.children.length ) {
					section.row.replaceChildren( section.row.firstElementChild, tag( 'ul', { className: 'frm-builder-outline-children', children: section.children } ) );
				}
			} );

			if ( query !== '' && ! pageMatches && rows.length === 0 ) {
				return;
			}

			// A one-page form has no pages to show, only its sections and fields.
			if ( pages.length > 1 ) {
				if ( pageMatches && resultCount === previousResultCount ) {
					resultCount++;
				}
				const pageLabel = page.title === pageNumber ? pageNumber : `${ pageNumber }: ${ page.title }`;
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
				children.push( tag( 'li', { children: [
					pageButton,
					... rows.length ? [ tag( 'ul', { className: 'frm-builder-outline-children', children: rows } ) ] : []
				] } ) );
			} else {
				children.push( ...rows );
			}
		} );

		const hasFields = pages.length > 1 || pages.some( page => page.fields.length > 0 );
		const noResults = query !== '' && resultCount === 0;
		const showEmpty = noResults || ! hasFields;
		const fragment = document.createDocumentFragment();
		if ( ! showEmpty ) {
			fragment.append( ...children );
		}
		const focusedButton = list.contains( document.activeElement ) ? document.activeElement : null;
		const focusedFieldId = focusedButton?.dataset.fieldId;
		const focusedClass = focusedButton?.className;
		list.replaceChildren( fragment );
		if ( focusedFieldId ) {
			list.querySelector( `button.${ focusedClass }[data-field-id="${ focusedFieldId }"]` )?.focus( { preventScroll: true } );
		}
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
			: sprintf( _n( '%d matching result found.', '%d matching results found.', resultCount, 'formidable' ), resultCount );
	};

	const observer = new MutationObserver( () => {
		pagesCache = null;
		fieldDetails = new WeakMap();
		clearTimeout( renderTimeout );
		renderTimeout = setTimeout( () => {
			pagePills = getPagePills();
			render();
			updateCurrentPage();
			// Preview hydration can shift the target after a long jump.
			if ( highlightedField?.isConnected ) {
				const targetBounds = highlightedField.getBoundingClientRect();
				const viewportBounds = scroller.getBoundingClientRect();
				if ( targetBounds.bottom < viewportBounds.top || targetBounds.top > viewportBounds.bottom ) {
					highlightedField.scrollIntoView( { block: 'start' } );
				}
			}
		}, RENDER_DELAY );
	} );

	const expandCollapsedPage = pageContainer => {
		const collapseButton = pageContainer?.querySelector( '.frm-collapse-page' );
		if ( pageContainer?.classList.contains( 'frm-page-collapsed' ) ) {
			collapseButton?.click();
			return Boolean( collapseButton );
		}
		return false;
	};

	const updateOutline = () => {
		clearTimeout( renderTimeout );
		clearTimeout( searchTimeout );
		clearTimeout( jumpTimeout );
		window.cancelAnimationFrame( scrollFrame );
		scrollFrame = null;
		observer.disconnect();
		scroller.removeEventListener( 'scroll', queueCurrentPageUpdate );
		pagesCache = null;
		fieldDetails = new WeakMap();
		pagePills = [];
		if ( ! isOpen() ) {
			return;
		}
		observer.observe( builder, {
			childList: true,
			characterData: true,
			subtree: true,
			attributes: true,
			attributeFilter: [ 'data-type', 'data-fid' ]
		} );
		pagePills = getPagePills();
		scroller.addEventListener( 'scroll', queueCurrentPageUpdate, { passive: true } );
		render();
		updateCurrentPage();
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
		pagePills.forEach( pill => {
			if ( pill.offsetParent && pill.getBoundingClientRect().top < top + 8 ) {
				index = Math.max( index, getPillPage( pill ) );
			}
		} );
		if ( currentPageIndex !== Math.max( index, 0 ) ) {
			currentPageIndex = Math.max( index, 0 );
			list.querySelector( '[aria-current]' )?.removeAttribute( 'aria-current' );
			if ( search.value.trim() === '' ) {
				list.querySelector( `.frm-builder-outline-page[data-page-index="${ currentPageIndex }"]` )?.setAttribute( 'aria-current', 'location' );
			}
		}
	};

	let scrollFrame;
	const queueCurrentPageUpdate = () => {
		if ( scrollFrame ) {
			return;
		}
		scrollFrame = window.requestAnimationFrame( () => {
			scrollFrame = null;
			updateCurrentPage();
		} );
	};

	// The tab navigator switches tabs by class, from clicks, keys and code, so watch the class.
	new MutationObserver( updateOutline ).observe( tab, { attributes: true, attributeFilter: [ 'class' ] } );
	search.addEventListener( 'input', () => {
		clearTimeout( searchTimeout );
		searchTimeout = setTimeout( () => {
			searchTimeout = null;
			render();
		}, RENDER_DELAY );
	} );
	updateOutline();
	// Arrow keys move between the search box and the results, like a menu. Safari's Tab skips buttons by default.
	popover.addEventListener( 'keydown', event => {
		if ( ! [ 'ArrowDown', 'ArrowUp', 'Home', 'End', 'Enter' ].includes( event.key ) ) {
			return;
		}
		if ( searchTimeout ) {
			clearTimeout( searchTimeout );
			searchTimeout = null;
			render();
		}
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
			// Page headers provide context. Prefer a matching section or field.
			const firstResult = list.querySelector( '.frm-builder-outline-section, .frm-builder-outline-field' ) || items[ 0 ];
			firstResult.click();
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
		renderFieldPreviewNow( button.dataset.fieldId );
		const field = builder.querySelector( `li[data-fid="${ button.dataset.fieldId }"]` );
		if ( ! field ) {
			return;
		}

		const page = getPages()[ Number( button.dataset.pageIndex ) ];
		const pageContainer = page?.breakField
			? page.breakField.closest( 'li.frm_field_box' )
			: document.getElementById( 'frm-fake-page' );
		const expandedPage = expandCollapsedPage( pageContainer );

		let ancestor = field.parentElement.closest( '.frm-section-collapsed' );
		while ( ancestor ) {
			ancestor.querySelector( '.frm-collapse-section' )?.click();
			ancestor = ancestor.parentElement.closest( '.frm-section-collapsed' );
		}

		clearTimeout( highlightTimeout );
		highlightedField?.classList.remove( 'frm-builder-outline-target' );
		highlightedField = field;
		// Don't select the field. That would open Field Options, and the outline stays open to jump again.
		field.classList.add( 'frm-builder-outline-target' );
		clearTimeout( jumpTimeout );
		jumpTimeout = window.setTimeout( () => {
			window.requestAnimationFrame( () => {
				field.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			} );
		}, expandedPage ? PAGE_EXPAND_DELAY : 0 );
		highlightTimeout = window.setTimeout( () => {
			field.classList.remove( 'frm-builder-outline-target' );
			highlightedField = null;
		}, 1800 );
	} );
}
