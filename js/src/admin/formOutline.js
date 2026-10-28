import { __, sprintf } from '@wordpress/i18n';

const PAGE_BREAK_TYPE = 'break';
const SECTION_TYPE = 'divider';
const HTML_FIELD_TYPE = 'html';
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

	const label = field.querySelector( '.frm-label-text, .frm_builder_field_name' ) ||
		field.querySelector( '.frm_primary_label' );
	if ( ! label ) {
		return '';
	}

	const labelClone = label.cloneNode( true );
	labelClone.querySelectorAll( '.frm-collapsed-label' ).forEach(
		collapsedLabel => collapsedLabel.remove()
	);
	const labelText = labelClone.textContent.trim();
	if ( labelText && ( field.dataset.type !== HTML_FIELD_TYPE || labelText !== 'HTML' ) ) {
		return labelText;
	}

	return '';
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

	const search = document.getElementById( 'frm-builder-outline-search' );
	const list = outline.querySelector( '.frm-builder-outline-list' );
	const status = outline.querySelector( '.frm-builder-outline-status' );
	let observer;
	let renderTimeout;

	const getPages = () => {
		const pages = [ { fields: [], breakField: null } ];
		const fields = builder.querySelectorAll( 'li[data-fid]' );
		fields.forEach( field => {
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
		const { tag } = frmDom;
		const children = [];
		let fieldResultCount = 0;

		pages.forEach( page => {
			const pageMatches = page.title.toLocaleLowerCase().includes( query );
			const matchingFields = query === ''
				? []
				: page.fields.filter( field => {
					const fieldText = getFieldText( field ).toLocaleLowerCase();
					return fieldText.includes( query ) || field.dataset.fid.includes( query );
				} );

			if ( query !== '' && ! pageMatches && matchingFields.length === 0 ) {
				return;
			}

			const [ firstField ] = page.fields;
			if ( ! firstField ) {
				return;
			}

			const pageButton = tag( 'button', {
				className: 'frm-builder-outline-page',
				text: `${ sprintf( __( 'Page %1$d', 'formidable' ), page.index + 1 ) }: ${ page.title } (${ page.fields.length })`
			} );
			pageButton.type = 'button';
			pageButton.dataset.fieldId = firstField.dataset.fid;
			pageButton.dataset.pageIndex = page.index;
			children.push( tag( 'li', { child: pageButton } ) );

			page.fields.filter( field => {
				if ( field.dataset.type !== SECTION_TYPE ) {
					return false;
				}
				if ( query === '' || pageMatches ) {
					return true;
				}

				const sectionText = getFieldText( field ).toLocaleLowerCase();
				return sectionText.includes( query ) || field.dataset.fid.includes( query );
			} ).forEach( field => {
				const sectionText = getFieldText( field );
				if ( ! sectionText ) {
					return;
				}

				const sectionButton = tag( 'button', {
					className: 'frm-builder-outline-section',
					text: sectionText
				} );
				sectionButton.type = 'button';
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
				const fieldButton = tag( 'button', {
					className: 'frm-builder-outline-field',
					text: getFieldText( field )
				} );
				fieldButton.type = 'button';
				fieldButton.dataset.fieldId = field.dataset.fid;
				fieldButton.dataset.pageIndex = page.index;
				children.push( tag( 'li', { child: fieldButton } ) );
				fieldResultCount++;
			} );
		} );

		list.replaceChildren( ...children );
		if ( query === '' ) {
			status.textContent = sprintf( __( '%d pages', 'formidable' ), pages.length );
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
				if ( ! outline.open ) {
					return;
				}
				render();
				observeBuilder();
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

	outline.addEventListener( 'toggle', () => {
		if ( ! outline.open ) {
			observer?.disconnect();
			return;
		}
		render();
		observeBuilder();
	} );
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
		field.classList.add( 'frm-builder-outline-target' );
		field.click();
		window.requestAnimationFrame( () => {
			field.scrollIntoView( { behavior: 'instant', block: 'start' } );
			const navigation = document.getElementById( 'frm-nav-tabs' );
			const topOffset = navigation?.getBoundingClientRect().bottom || 0;
			const scrollOffset = field.getBoundingClientRect().top - topOffset;
			window.scrollBy( { top: scrollOffset, behavior: 'smooth' } );
		} );
		window.setTimeout( () => field.classList.remove( 'frm-builder-outline-target' ), 1800 );
	} );
}
