/**
 * Section cards for the form builder: a working collapse control, a field count and a remembered collapsed state.
 *
 * @since x.x
 */
import { __, _n, sprintf } from '@wordpress/i18n';

const FIELD_SELECTOR = 'li.form-field[data-fid]';
const SECTION_SELECTOR = 'li.edit_field_type_divider';
const CONTAINER_CLASS = 'edit_field_type_divider';
const END_MARKER_CLASS = 'edit_field_type_end_divider';
const UNCOUNTED_TYPES = new Set( [ 'end_divider', 'submit', 'break' ] );

/**
 * Check whether a field preview counts toward its section's field count.
 * Sections and other containers (tabs, accordions) and their end markers are structure, not fields.
 *
 * @since x.x
 * @param {HTMLElement} field The field preview list item.
 * @return {boolean} True when the field should be counted.
 */
export function isCountedField( field ) {
	if ( field.classList.contains( CONTAINER_CLASS ) || field.classList.contains( END_MARKER_CLASS ) ) {
		return false;
	}
	return ! UNCOUNTED_TYPES.has( field.dataset.type );
}

/**
 * Count the fields inside a section, including fields in nested sections, but not the nested sections themselves.
 *
 * @since x.x
 * @param {Iterable<HTMLElement>} fields Field previews inside the section.
 * @return {number} The number of fields.
 */
export function countSectionFields( fields ) {
	return [ ...fields ].filter( isCountedField ).length;
}

/**
 * Read the IDs of collapsed sections. Blocked browser storage must not interrupt the builder.
 *
 * @since x.x
 * @param {string} key The browser storage key.
 * @return {string[]} Collapsed section IDs.
 */
function readCollapsed( key ) {
	let value;
	try {
		value = JSON.parse( localStorage.getItem( key ) || '[]' );
	} catch {
		return [];
	}
	return Array.isArray( value ) ? value.map( String ) : [];
}

/**
 * Save the IDs of collapsed sections without changing the form.
 *
 * @since x.x
 * @param {string}   key   The browser storage key.
 * @param {string[]} value Collapsed section IDs.
 * @return {void}
 */
function writeCollapsed( key, value ) {
	try {
		localStorage.setItem( key, JSON.stringify( value ) );
	} catch {
		// Collapsing still works for this page view when storage is disabled or full.
	}
}

/**
 * Turn the builder's sections into cards with an accessible collapse button and a field count.
 * Only the builder preview changes. Saved form settings and the published form are untouched.
 *
 * @since x.x
 * @param {HTMLElement} canvas The builder field list.
 * @return {Object|undefined} Helpers for other builder modules: setCollapsed( section, shouldCollapse ) and refresh().
 */
export function initBuilderSections( canvas ) {
	const formId = document.getElementById( 'form_id' )?.value;
	if ( ! canvas || ! formId || canvas.classList.contains( 'frm-builder-sections' ) ) {
		return;
	}
	canvas.classList.add( 'frm-builder-sections' );

	const { tag, span, svg } = frmDom;
	const storageKey = `frm-builder-sections-${ formId }`;
	const collapsed = new Set( readCollapsed( storageKey ) );
	const prepared = new WeakSet();
	let frame;

	function getName( section ) {
		const id = section.dataset.fid;
		return document.getElementById( `frm_name_${ id }` )?.value ||
			section.querySelector( '.frm_builder_field_name' )?.textContent.trim() ||
			__( 'Untitled section', 'formidable' );
	}

	function setCollapsed( section, shouldCollapse ) {
		section.classList.toggle( 'frm-section-collapsed', shouldCollapse );
		const children = section.querySelector( ':scope > ul.start_divider' );
		const control = section.querySelector( '.js-frm-builder-collapse' );
		if ( children && control ) {
			children.hidden = shouldCollapse;
			control.setAttribute( 'aria-expanded', String( ! shouldCollapse ) );
			const label = shouldCollapse ? sprintf(
				/* translators: %s: Section name. */
				__( 'Expand %s in builder', 'formidable' ),
				getName( section )
			) : sprintf(
				/* translators: %s: Section name. */
				__( 'Collapse %s in builder', 'formidable' ),
				getName( section )
			);
			control.setAttribute( 'aria-label', label );
		}
		if ( shouldCollapse ) {
			collapsed.add( section.dataset.fid );
		} else {
			collapsed.delete( section.dataset.fid );
		}
		writeCollapsed( storageKey, [ ...collapsed ] );
	}

	function prepareSection( section ) {
		const children = section.querySelector( ':scope > ul.start_divider' );
		const label = section.querySelector( ':scope > .divider_section_only .frm_primary_label' );
		if ( ! children || ! label ) {
			return;
		}
		if ( ! prepared.has( section ) ) {
			prepared.add( section );
			section.classList.add( 'frm-builder-section' );
			const oldControl = section.querySelector( ':scope > .divider_section_only .frm-collapse-section' );
			if ( oldControl ) {
				// A real button replaces the link, so the control works inside tabs and announces its state.
				const control = tag( 'button', {
					className: 'frm-collapse-section js-frm-builder-collapse',
					child: svg( { href: '#frm_arrowdown6_icon' } ),
				} );
				control.type = 'button';
				children.id = `frm-builder-section-${ section.dataset.fid }`;
				control.setAttribute( 'aria-controls', children.id );
				oldControl.remove();
				label.before( control );
			}
			label.append( span( { className: 'frm-builder-section-count' } ) );
			setCollapsed( section, collapsed.has( section.dataset.fid ) );
		}

		// Other builder code may toggle the class directly, so keep the list and storage in sync with it.
		const isCollapsed = section.classList.contains( 'frm-section-collapsed' );
		if ( children.hidden !== isCollapsed || collapsed.has( section.dataset.fid ) !== isCollapsed ) {
			setCollapsed( section, isCollapsed );
		}

		const count = countSectionFields( children.querySelectorAll( FIELD_SELECTOR ) );
		const text = sprintf(
			/* translators: %d: Number of fields in this section. */
			_n( '%d field', '%d fields', count, 'formidable' ),
			count
		);
		const countElement = label.querySelector( '.frm-builder-section-count' );
		if ( countElement && countElement.textContent !== text ) {
			countElement.textContent = text;
		}
	}

	const observer = new MutationObserver( scheduleRefresh );

	function refresh() {
		frame = null;
		observer.disconnect();
		canvas.querySelectorAll( SECTION_SELECTOR ).forEach( prepareSection );
		observer.observe( canvas, { childList: true, subtree: true, attributes: true, attributeFilter: [ 'class' ] } );
	}

	function scheduleRefresh() {
		if ( ! frame ) {
			frame = requestAnimationFrame( refresh );
		}
	}

	// Capture the click so the builder's field selection does not also run.
	canvas.addEventListener( 'click', event => {
		const control = event.target.closest( '.js-frm-builder-collapse' );
		if ( ! control ) {
			return;
		}
		event.preventDefault();
		event.stopPropagation();
		const section = control.closest( SECTION_SELECTOR );
		setCollapsed( section, ! section.classList.contains( 'frm-section-collapsed' ) );
	}, true );

	refresh();

	return { setCollapsed, refresh };
}
