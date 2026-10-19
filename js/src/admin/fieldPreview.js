/**
 * Render the displayed choices without populating an interactive option list.
 *
 * @since x.x
 * @param {HTMLSelectElement} field       The preview dropdown.
 * @param {Array}             choices     Choices from the field settings.
 * @param {string}            placeholder The current placeholder text.
 * @return {void}
 */
export function renderDropdownPreview( field, choices, placeholder = '' ) {
	const available = choices.filter( choice => placeholder === '' || choice.label !== '' );
	let displayed = available.filter( choice => choice.checked );
	if ( ! field.multiple ) {
		displayed = displayed.slice( -1 );
	}
	if ( ! displayed.length ) {
		displayed = [ placeholder !== ''
			? { saved: '', label: placeholder, placeholder: true }
			: choices[ 0 ] || { saved: '', label: '' } ];
	}
	const options = displayed.map( choice => {
		const option = frmDom.tag( 'option', {
			value: choice.saved,
			text: choice.label,
			selected: true
		} );
		if ( choice.placeholder ) {
			option.classList.add( 'frm-select-placeholder' );
			option.dataset.placeholder = 'true';
		}
		if ( choice.price !== undefined ) {
			option.dataset.price = choice.price;
		}
		return option;
	} );
	field.replaceChildren( ...options );
}

/**
 * Refresh an address dropdown from its default and placeholder settings.
 *
 * @since x.x
 * @param {HTMLSelectElement} field    The address preview dropdown.
 * @param {HTMLElement}       settings The field settings panel.
 * @return {void}
 */
export function updateComboSelectPreview( field, settings ) {
	const inputs = Array.from( settings.querySelectorAll( '[data-changeme]' ) )
		.filter( input => input.dataset.changeme === field.id );
	const defaultInput = inputs.find( input => input.dataset.changeatt === 'value' );
	const placeholderInput = inputs.find( input => input.dataset.changeatt === 'placeholder' );
	const value = defaultInput?.value ?? field.value;
	const values = JSON.parse( field.dataset.frmPreviewChoices || '[]' );
	const choices = values.includes( value ) ? [ { saved: value, label: value, checked: true } ] : [];
	renderDropdownPreview( field, choices, placeholderInput?.value || '' );
}

/**
 * Replace an address layout and restore the current, unsaved subfield settings.
 *
 * @since x.x
 * @param {HTMLSelectElement} setting       The address type setting.
 * @param {Function}          updatePreview The shared field preview updater.
 * @return {void}
 */
export function updateAddressPreview( setting, updatePreview ) {
	const settings = setting.closest( '.frm-single-settings' );
	const template = Array.from( settings.querySelectorAll( 'template[data-frm-address-type]' ) )
		.find( item => item.dataset.frmAddressType === setting.value );
	const field = setting.ownerDocument.getElementById( `frm_field_id_${ settings.dataset.fid }` );
	const container = field?.querySelector( '.frm_combo_inputs_container' );
	const replacement = template?.content.getElementById( `frm_combo_inputs_container_${ settings.dataset.fid }` );
	if ( ! container || ! replacement ) {
		return;
	}

	container.replaceWith( replacement.cloneNode( true ) );
	settings.querySelectorAll( '[data-changeme]' ).forEach( input => updatePreview.call( input ) );
	settings.querySelectorAll( '.frm_sub_field_options' ).forEach( options => {
		const name = options.dataset.subFieldName;
		const visible = field.querySelector( `[data-sub-field-name="${ name }"]` ) !== null;
		options.classList.toggle( 'frm_hidden', ! visible );
	} );
}
