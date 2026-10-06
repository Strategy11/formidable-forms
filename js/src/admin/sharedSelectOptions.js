const templates = new Map();

/**
 * Populate a builder select from an already delivered option list.
 *
 * @since x.x
 * @param {HTMLSelectElement} select The select whose saved options are already present.
 * @return {void}
 */
export function hydrateBuilderSelect( select ) {
	const key = select.dataset.frmOptions;
	const records = window.frm_admin_js?.selectOptions?.[ key ];
	if ( ! key || ! records ) {
		return;
	}

	if ( ! templates.has( key ) ) {
		const template = frmDom.tag( 'select' );
		const options = records.map( record => {
			const option = frmDom.tag( 'option', { text: record.label || ' ' } );
			Object.entries( record.attributes ).forEach( ( [ name, value ] ) => option.setAttribute( name, value ) );
			option.value = record.value;
			return option;
		} );
		template.append( ...options );
		templates.set( key, template );
	}

	const selected = new Set( Array.from( select.selectedOptions, option => option.value ) );
	const defaults = new Set( Array.from( select.options ).filter( option => option.defaultSelected ).map( option => option.value ) );
	const options = Array.from( templates.get( key ).cloneNode( true ).options );
	const fragment = document.createDocumentFragment();
	fragment.append( ...options );
	options.forEach( option => {
		option.defaultSelected = defaults.has( option.value );
		option.selected = selected.has( option.value );
	} );
	select.replaceChildren( fragment );
	if ( ! select.multiple && ! selected.size ) {
		select.selectedIndex = -1;
	}
	delete select.dataset.frmOptions;
}

/**
 * Populate options before a settings panel's controls and multiselects are shown.
 *
 * @since x.x
 * @param {HTMLElement} container The settings panel being opened.
 * @return {void}
 */
export function hydrateBuilderSelectsIn( container ) {
	container.querySelectorAll( 'select[data-frm-options]' ).forEach( hydrateBuilderSelect );
}
