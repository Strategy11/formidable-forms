import { __, _n, sprintf } from '@wordpress/i18n';
import { renderFieldPreviewNow } from './fieldPreviewQueue';
import { buildOutline, getCurrentPageIndex, groupPages, MAX_FIELD_RESULTS, throttle } from './formOutlineModel';

const RENDER_DELAY = 100;
// togglePage() in admin.js animates the first rows for 150 ms.
const PAGE_EXPAND_DELAY = 160;
// Previews above a jump target can still be drawing after the scroll ends, which moves the target.
const JUMP_SETTLE_TIME = 8000;
const HIGHLIGHT_TIME = 1800;
const JUMP_SETTLE_FRAMES = 4;
const JUMP_TOLERANCE = 2;
// Ajax placeholders carry no name until their batch arrives, so the outline leaves them out and redraws as they arrive.
const LOADING_FIELD_SELECTOR = 'li.frm_field_loading:not(.frm_field_load_failed)';

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
	const loadingMessage = popover.querySelector( '.frm-builder-outline-loading' );
	const isOpen = () => tab.classList.contains( 'frm-active' );
	const scroller = document.getElementById( 'post-body-content' );
	let currentPageIndex = 0;
	let searchTimeout;
	let pagesCache;
	let fieldDetails = new WeakMap();
	let pagePills = [];
	let highlightedField;
	let highlightTimeout;
	let jumpTimeout;
	let jumpFrame;
	let stopJumpSettle = () => {};

	const getFieldDetails = field => {
		if ( ! fieldDetails.has( field ) ) {
			const pendingPreview = field.classList.contains( 'frm_field_preview_pending' );
			const heading = pendingPreview ? '' : getHeadingText( field );
			const text = pendingPreview
				? field.querySelector( '.frm-deferred-settings-meta input[id^="frm_name_"]' )?.value.trim() || ''
				: heading || getFieldText( field );
			const label = text || sprintf(
				/* translators: %s: Field ID. */
				__( 'Untitled field (%s)', 'formidable' ),
				field.dataset.fid
			);
			fieldDetails.set( field, { heading, text, label } );
		}
		return fieldDetails.get( field );
	};

	const describeField = field => {
		const { heading, text, label } = getFieldDetails( field );
		const sectionIds = [];
		let section = field.parentElement.closest( 'li[data-type="divider"]' );
		while ( section ) {
			sectionIds.push( section.dataset.fid );
			section = section.parentElement.closest( 'li[data-type="divider"]' );
		}
		// A placeholder has no data-type until its batch arrives, but data-ftype still tells a page break apart.
		const type = field.dataset.type || field.dataset.ftype;
		const placeholder = field.classList.contains( 'frm_field_loading' );
		// Deferred previews keep the key in their metadata, loaded settings in the Field Options input.
		const key = document.getElementById( `field_options_field_key_${ field.dataset.fid }` )?.value || '';
		return { id: field.dataset.fid, type, heading, text, label, key, sectionId: sectionIds[ 0 ] || '', sectionIds, placeholder, element: field };
	};

	const getPages = () => {
		if ( ! pagesCache ) {
			pagesCache = groupPages( Array.from( builder.querySelectorAll( 'li[data-fid]' ), describeField ) );
		}
		return pagesCache;
	};

	const getPageNumber = index => sprintf( __( 'Page %d', 'formidable' ), index + 1 );

	const render = () => {
		if ( ! isOpen() ) {
			return;
		}

		const loading = Boolean( builder.querySelector( LOADING_FIELD_SELECTOR ) );
		loadingMessage.classList.toggle( 'frm_hidden', ! loading );

		const query = search.value.trim();
		const pages = getPages();
		const { items, resultCount, fieldResultCount, hasFields } = buildOutline( pages, query, getPageNumber );
		const { tag, span } = frmDom;

		const drawItem = item => {
			const button = tag( 'button', {
				className: `frm-builder-outline-${ item.kind }`,
				child: span( { className: 'frm-builder-outline-label', text: item.label } )
			} );
			if ( item.fieldCount !== undefined ) {
				button.append( span( {
					className: 'frm-builder-outline-meta',
					/* translators: %d: Number of fields on the page or in the section */
					text: sprintf( _n( '%d field', '%d fields', item.fieldCount, 'formidable' ), item.fieldCount )
				} ) );
			}
			button.type = 'button';
			button.title = item.label;
			button.dataset.fieldId = item.id;
			button.dataset.pageIndex = item.pageIndex;
			if ( item.kind === 'page' && item.pageIndex === currentPageIndex && query === '' ) {
				button.setAttribute( 'aria-current', 'location' );
			}
			const children = [ button ];
			if ( item.children?.length ) {
				children.push( tag( 'ul', { className: 'frm-builder-outline-children', children: item.children.map( drawItem ) } ) );
			}
			return tag( 'li', { children } );
		};

		const noResults = query !== '' && resultCount === 0;
		const showEmpty = noResults || ( ! hasFields && ! loading );
		const fragment = document.createDocumentFragment();
		if ( ! showEmpty ) {
			fragment.append( ...items.map( drawItem ) );
		}
		const focusedButton = list.contains( document.activeElement ) ? document.activeElement : null;
		const focusedFieldId = focusedButton?.dataset.fieldId;
		const focusedClass = focusedButton?.className;
		list.replaceChildren( fragment );
		if ( focusedFieldId ) {
			list.querySelector( `button.${ focusedClass }[data-field-id="${ focusedFieldId }"]` )?.focus( { preventScroll: true } );
		}
		emptyMessage.classList.toggle( 'frm_hidden', ! showEmpty );
		search.parentElement.classList.toggle( 'frm_hidden', ! hasFields && ! loading );
		if ( noResults ) {
			/* translators: %s: The search text */
			emptyTitle.textContent = sprintf( __( 'No results for “%s”', 'formidable' ), search.value.trim() );
			emptyText.textContent = loading
				? __( 'Some fields are still loading.', 'formidable' )
				: __( 'Try a page, section or field name, or a field ID or key.', 'formidable' );
		} else if ( showEmpty ) {
			emptyTitle.textContent = __( 'No fields yet', 'formidable' );
			emptyText.textContent = __( 'Fields you add to this form will show up here, so you can jump to any of them.', 'formidable' );
		}
		if ( query === '' && loading ) {
			status.textContent = __( 'Loading fields…', 'formidable' );
			return;
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

	const queueRender = throttle( () => {
		pagePills = getPagePills();
		render();
		updateCurrentPage();
	}, RENDER_DELAY );

	const observer = new MutationObserver( () => {
		pagesCache = null;
		fieldDetails = new WeakMap();
		queueRender();
	} );

	/**
	 * Keep a jump target at the top of the canvas while the previews above it finish drawing.
	 *
	 * Drawing a preview changes its height, so a smooth scroll that started before it can end
	 * a little past the target. Whenever the scroll comes to rest, put the target back in place.
	 * Stop as soon as the person scrolls, so this never fights them.
	 *
	 * @param {HTMLElement} field  The field the outline jumped to.
	 * @param {Function}    onRest Runs once, when the target first rests in place or settling stops.
	 * @return {void}
	 */
	const settleJump = ( field, onRest ) => {
		stopJumpSettle();
		const deadline = performance.now() + JUMP_SETTLE_TIME;
		const marginTop = parseFloat( window.getComputedStyle( field ).scrollMarginTop ) || 0;
		let lastScrollTop = -1;
		let stableFrames = 0;
		let rested = false;
		const rest = () => {
			if ( ! rested ) {
				rested = true;
				onRest();
			}
		};
		const stop = () => {
			window.cancelAnimationFrame( jumpFrame );
			jumpFrame = null;
			[ 'wheel', 'touchstart', 'pointerdown', 'keydown' ].forEach( type => scroller.removeEventListener( type, stop ) );
			stopJumpSettle = () => {};
			rest();
		};
		const step = () => {
			if ( ! field.isConnected || performance.now() > deadline ) {
				stop();
				return;
			}
			if ( scroller.scrollTop !== lastScrollTop ) {
				lastScrollTop = scroller.scrollTop;
				stableFrames = 0;
			} else if ( ++stableFrames >= JUMP_SETTLE_FRAMES ) {
				stableFrames = 0;
				const offset = field.getBoundingClientRect().top - scroller.getBoundingClientRect().top - marginTop;
				const atBottom = offset > 0 && scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 1;
				if ( Math.abs( offset ) > JUMP_TOLERANCE && ! atBottom ) {
					field.scrollIntoView( { block: 'start' } );
				} else {
					rest();
				}
			}
			jumpFrame = window.requestAnimationFrame( step );
		};
		[ 'wheel', 'touchstart', 'pointerdown', 'keydown' ].forEach( type => scroller.addEventListener( type, stop, { passive: true } ) );
		stopJumpSettle = stop;
		jumpFrame = window.requestAnimationFrame( step );
	};

	const expandCollapsedPage = pageContainer => {
		const collapseButton = pageContainer?.querySelector( '.frm-collapse-page' );
		if ( pageContainer?.classList.contains( 'frm-page-collapsed' ) ) {
			collapseButton?.click();
			return Boolean( collapseButton );
		}
		return false;
	};

	const updateOutline = () => {
		clearTimeout( searchTimeout );
		searchTimeout = null;
		if ( jumpTimeout ) {
			// The jump never started, so neither did the timer that clears its highlight.
			clearTimeout( jumpTimeout );
			jumpTimeout = null;
			highlightedField?.classList.remove( 'frm-builder-outline-target' );
			highlightedField = null;
		}
		stopJumpSettle();
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
		const pills = pagePills.filter( pill => pill.offsetParent ).map( pill => ( { index: getPillPage( pill ), top: pill.getBoundingClientRect().top } ) );
		const index = getCurrentPageIndex( pills, {
			top: scroller.getBoundingClientRect().top,
			height: scroller.clientHeight,
			scrollTop: scroller.scrollTop,
			scrollHeight: scroller.scrollHeight
		} );
		if ( currentPageIndex !== index ) {
			currentPageIndex = index;
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
			? page.breakField.element.closest( 'li.frm_field_box' )
			: document.getElementById( 'frm-fake-page' );
		const expandedPage = expandCollapsedPage( pageContainer );

		let ancestor = field.parentElement.closest( '.frm-section-collapsed' );
		while ( ancestor ) {
			ancestor.querySelector( '.frm-collapse-section' )?.click();
			ancestor = ancestor.parentElement.closest( '.frm-section-collapsed' );
		}

		// Stop the last jump first. Stopping it starts its highlight timer, which is cleared next.
		stopJumpSettle();
		clearTimeout( highlightTimeout );
		highlightedField?.classList.remove( 'frm-builder-outline-target' );
		highlightedField = field;
		// Don't select the field. That would open Field Options, and the outline stays open to jump again.
		field.classList.add( 'frm-builder-outline-target' );
		clearTimeout( jumpTimeout );
		jumpTimeout = window.setTimeout( () => {
			jumpTimeout = null;
			window.requestAnimationFrame( () => {
				const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
				field.scrollIntoView( { behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' } );
				// A long smooth scroll can take most of the highlight time, so start it on arrival.
				// The highlight's scroll margin is part of the position, so keep settling until it ends.
				settleJump( field, () => {
					highlightTimeout = window.setTimeout( () => {
						stopJumpSettle();
						field.classList.remove( 'frm-builder-outline-target' );
						highlightedField = null;
					}, HIGHLIGHT_TIME );
				} );
			} );
		}, expandedPage ? PAGE_EXPAND_DELAY : 0 );
	} );
}
