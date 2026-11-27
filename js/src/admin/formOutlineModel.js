/**
 * The form outline's data: pages, sections and fields grouped and filtered, with no DOM access.
 * formOutline.js reads the builder into plain field objects and draws what this returns.
 */

export const PAGE_BREAK_TYPE = 'break';
export const SECTION_TYPE = 'divider';
export const END_SECTION_TYPE = 'end_divider';
export const HTML_FIELD_TYPE = 'html';
export const SUBMIT_TYPE = 'submit';
// Layout pieces that sit in the field list but aren't fields people fill in.
export const STRUCTURE_TYPES = [ SECTION_TYPE, END_SECTION_TYPE, PAGE_BREAK_TYPE, SUBMIT_TYPE ];
export const MAX_FIELD_RESULTS = 60;

/**
 * @typedef {Object} OutlineField
 * @property {string}   id          The field ID.
 * @property {string}   type        The field type.
 * @property {string}   heading     The text of a heading in the field's content, if any.
 * @property {string}   text        The heading, or else the field's name.
 * @property {string}   label       What the outline shows for the field.
 * @property {string}   sectionId   The ID of the section the field sits in directly, or ''.
 * @property {string[]} sectionIds  The IDs of every section the field sits in.
 * @property {string}   key         The field key, or '' when it isn't known.
 * @property {boolean}  placeholder True while the field's preview is still loading, so only its type is known.
 */

/**
 * Check a field against a search.
 *
 * The name matches anywhere in it. The ID only matches in full, so "10" doesn't find 100 to 110.
 * The key matches from its start, since keys are random letters and numbers past what people type.
 *
 * @param {OutlineField} field The field.
 * @param {string}       query The search text, trimmed and lowercase.
 * @return {boolean} True when the field matches.
 */
function fieldMatches( field, query ) {
	return field.label.toLocaleLowerCase().includes( query ) ||
		field.id === query ||
		field.key.toLocaleLowerCase().startsWith( query );
}

/**
 * Split the builder's fields into pages at each page break.
 *
 * @param {OutlineField[]} fields The fields, in builder order.
 * @return {Array<{fields: OutlineField[], breakField: OutlineField|null, index: number, heading: string}>} The pages.
 */
export function groupPages( fields ) {
	const pages = [ { fields: [], breakField: null } ];
	fields.forEach( field => {
		if ( field.type === SUBMIT_TYPE ) {
			return;
		}
		if ( field.type === PAGE_BREAK_TYPE ) {
			pages.push( { fields: [], breakField: field } );
			return;
		}
		pages[ pages.length - 1 ].fields.push( field );
	} );

	return pages.map( ( page, index ) => {
		const headingField = page.fields.find( field => field.heading && ! field.placeholder );
		const labelField = headingField || page.fields.find( field => field.type !== HTML_FIELD_TYPE && field.type !== END_SECTION_TYPE && field.text && ! field.placeholder );
		const heading = labelField?.type !== SECTION_TYPE && labelField?.text ? labelField.text : '';
		return { fields: page.fields, breakField: page.breakField, index, heading };
	} );
}

/**
 * Build the outline tree for a search.
 *
 * @param {Array}    pages      The pages from groupPages().
 * @param {string}   query      The search text. Empty for the full outline.
 * @param {Function} pageNumber Returns the "Page N" text for a page index.
 * @return {{items: Array, resultCount: number, fieldResultCount: number, hasFields: boolean}} The rows to draw and the counts.
 */
export function buildOutline( pages, query, pageNumber ) {
	query = query.trim().toLocaleLowerCase();
	const items = [];
	let fieldResultCount = 0;
	let resultCount = 0;

	pages.forEach( page => {
		const previousResultCount = resultCount;
		const number = pageNumber( page.index );
		const title = page.heading || number;
		const pageMatches = `${ number }: ${ title }`.toLocaleLowerCase().includes( query );
		const firstField = page.fields[ 0 ] || page.breakField || pages[ 1 ]?.breakField;
		if ( ! firstField ) {
			return;
		}

		const sectionRows = new Map();
		const rows = [];
		page.fields.forEach( field => {
			const { type } = field;
			// A loading field has no name to show or search yet. The outline redraws when it arrives.
			if ( type === END_SECTION_TYPE || field.placeholder ) {
				return;
			}
			if ( query !== '' && type !== SECTION_TYPE && fieldResultCount >= MAX_FIELD_RESULTS ) {
				return;
			}
			const isSection = type === SECTION_TYPE;
			const matches = fieldMatches( field, query );
			if ( query !== '' && ! matches && ! isSection ) {
				return;
			}
			const parent = sectionRows.get( field.sectionId );
			const siblings = parent ? parent.row.children : rows;
			if ( matches ) {
				resultCount++;
			}
			if ( isSection ) {
				const fieldCount = page.fields.filter( child => child.sectionIds.includes( field.id ) && ! STRUCTURE_TYPES.includes( child.type ) ).length;
				const row = { kind: 'section', id: field.id, label: field.label, pageIndex: page.index, fieldCount, children: [] };
				sectionRows.set( field.id, { row, matches, siblings } );
				siblings.push( row );
			} else {
				siblings.push( { kind: 'field', id: field.id, label: field.label, pageIndex: page.index } );
				if ( query !== '' ) {
					fieldResultCount++;
				}
			}
		} );
		// eslint-disable-next-line unicorn/no-array-reverse -- toReversed() is ES2023 and breaks older browsers. Array.from() already made a copy to reverse.
		Array.from( sectionRows.values() ).reverse().forEach( section => {
			// A page title match shows the page, not every section on it.
			if ( query !== '' && ! section.matches && section.row.children.length === 0 ) {
				section.siblings.splice( section.siblings.indexOf( section.row ), 1 );
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
			items.push( {
				kind: 'page',
				id: firstField.id,
				label: title === number ? number : `${ number }: ${ title }`,
				pageIndex: page.index,
				fieldCount: page.fields.filter( field => ! STRUCTURE_TYPES.includes( field.type ) ).length,
				children: rows
			} );
		} else {
			items.push( ...rows );
		}
	} );

	const hasFields = pages.length > 1 || pages.some( page => page.fields.length > 0 );
	return { items, resultCount, fieldResultCount, hasFields };
}

/**
 * Run a callback at most once per wait, while calls keep coming.
 *
 * A debounce would wait for the calls to stop, and the builder inserts field batches for
 * many seconds on a long form, so the outline would stay stale until the last one.
 *
 * @param {Function} callback The callback.
 * @param {number}   wait     Milliseconds between runs.
 * @return {Function} Queues a run. Calls while one is queued are folded into it.
 */
export function throttle( callback, wait ) {
	let timeout = null;
	return () => {
		if ( timeout !== null ) {
			return;
		}
		timeout = setTimeout( () => {
			timeout = null;
			callback();
		}, wait );
	};
}

// How far below the top of the canvas a page's pill can be and still count as reached.
const CURRENT_PAGE_OFFSET = 8;

/**
 * Find the page the canvas is showing: the last one whose pill has reached the top.
 *
 * Pages near the end of a form can be too short to scroll their pill up to the top, so over the
 * last screen of scrolling the line a pill has to cross moves down, reaching the bottom of the
 * canvas at the end. Each of those pages becomes current in turn, and the last one at the bottom.
 *
 * @param {Array<{index: number, top: number}>} pills The visible page pills and their tops on screen.
 * @param {Object}                              view  The canvas: its top on screen, height, scrollTop and scrollHeight.
 * @return {number} The index of the current page.
 */
export function getCurrentPageIndex( pills, view ) {
	const maxScroll = view.scrollHeight - view.height;
	let progress = 0;
	if ( maxScroll > 0 ) {
		const lastScreen = Math.min( maxScroll, view.height );
		const remaining = maxScroll - view.scrollTop;
		progress = Math.min( Math.max( ( lastScreen - remaining ) / lastScreen, 0 ), 1 );
	}
	const line = view.top + CURRENT_PAGE_OFFSET + ( progress * ( view.height - CURRENT_PAGE_OFFSET ) );
	let index = 0;
	pills.forEach( pill => {
		if ( pill.top < line ) {
			index = Math.max( index, pill.index );
		}
	} );
	return index;
}
