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
 * @property {string}   id         The field ID.
 * @property {string}   type       The field type.
 * @property {string}   heading    The text of a heading in the field's content, if any.
 * @property {string}   text       The heading, or else the field's name.
 * @property {string}   label      What the outline shows for the field.
 * @property {string}   sectionId  The ID of the section the field sits in directly, or ''.
 * @property {string[]} sectionIds The IDs of every section the field sits in.
 */

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
		const headingField = page.fields.find( field => field.heading );
		const labelField = headingField || page.fields.find( field => field.type !== HTML_FIELD_TYPE && field.type !== END_SECTION_TYPE && field.text );
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
			if ( type === END_SECTION_TYPE ) {
				return;
			}
			if ( query !== '' && type !== SECTION_TYPE && fieldResultCount >= MAX_FIELD_RESULTS ) {
				return;
			}
			const isSection = type === SECTION_TYPE;
			const matches = field.label.toLocaleLowerCase().includes( query ) || field.id.includes( query );
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
			if ( query !== '' && ! section.matches && ! pageMatches && section.row.children.length === 0 ) {
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
