import { __, _n, sprintf } from '@wordpress/i18n';

const PAGE_BREAK_TYPE = 'break';
const SECTION_TYPE = 'divider';
const HTML_FIELD_TYPE = 'html';
const SUBMIT_TYPE = 'submit';
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
 * Add searchable page and field navigation to the form builder.
 *
 * @since x.x
 * @return {void}
 */
export function initFormOutline() {
	const outline = document.getElementById( 'frm-builder-outline' );
	const builder = document.getElementById( 'frm-show-fields' );
	if ( ! outline || ! builder ) {
		return;
	}

	const search = document.getElementById( 'frm-builder-outline-search-input' );
	const list = outline.querySelector( '.frm-builder-outline-list' );
	const emptyMessage = outline.querySelector( '.frm-builder-outline-empty' );
	const emptyTitle = emptyMessage.querySelector( '.frm-builder-outline-empty-title' );
	const emptyText = emptyMessage.querySelector( '.frm-builder-outline-empty-text' );
	const status = outline.querySelector( '.frm-builder-outline-status' );
	// The tab navigator marks the panel's slide active, so watch that instead of the tab click.
	const slide = outline.parentElement;
	const isOutlineActive = () => slide.classList.contains( 'frm-active' );
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
			const pageButton = tag( 'button', {
				className: 'frm-builder-outline-page',
				children: [
					span( { className: 'frm-builder-outline-label', text: pageLabel } ),
					span( { className: 'frm-builder-outline-count', text: String( page.fields.length ) } )
				]
			} );
			pageButton.type = 'button';
			pageButton.title = pageLabel;
			pageButton.setAttribute(
				'aria-label',
				sprintf(
					/* translators: %1$s: Page name, %2$d: Number of fields on the page */
					_n( '%1$s, %2$d field', '%1$s, %2$d fields', page.fields.length, 'formidable' ),
					pageLabel,
					page.fields.length
				)
			);
			pageButton.dataset.fieldId = firstField.dataset.fid;
			pageButton.dataset.pageIndex = page.index;
			children.push( tag( 'li', { child: pageButton } ) );

			matchingSections.forEach( field => {
				const sectionText = getFieldText( field );
				if ( ! sectionText ) {
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

		const hasFields = pages.some( page => page.fields.length > 0 );
		// A lone page with no sections gives nothing to jump to, so explain how to add some instead.
		const hasOutline = pages.length > 1 || children.length > 1;
		const noResults = query !== '' && children.length === 0;
		const showEmpty = noResults || ( query === '' && ! hasOutline );
		const items = showEmpty ? [] : children;
		list.replaceChildren( ...items );
		emptyMessage.classList.toggle( 'frm_hidden', ! showEmpty );
		search.parentElement.classList.toggle( 'frm_hidden', ! hasFields );
		if ( noResults ) {
			emptyTitle.textContent = __( 'No results', 'formidable' );
			emptyText.textContent = __( 'No pages, sections or fields match your search.', 'formidable' );
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
		if ( children.length === 0 ) {
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
				// New pages add new sortable lists, so observe again even when the outline is hidden.
				observeBuilder();
				if ( isOutlineActive() ) {
					render();
				}
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

	new MutationObserver( () => {
		if ( isOutlineActive() ) {
			render();
		}
	} ).observe( slide, { attributeFilter: [ 'class' ] } );
	observeBuilder();
	search.addEventListener( 'input', render );
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
		// Don't select the field. That would switch to the Field Options tab and hide the outline.
		field.classList.add( 'frm-builder-outline-target' );
		window.requestAnimationFrame( () => {
			field.scrollIntoView( { behavior: 'smooth', block: 'start' } );
		} );
		window.setTimeout( () => field.classList.remove( 'frm-builder-outline-target' ), 1800 );
	} );
}
