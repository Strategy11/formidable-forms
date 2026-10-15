/**
 * Update a select placeholder without retaining a synthetic blank choice when cleared.
 *
 * @since x.x
 * @param {HTMLSelectElement} field          The preview select.
 * @param {string}            value          The placeholder text.
 * @param {boolean}           hasBlankChoice Whether the field has a real blank choice.
 * @return {void}
 */
export function updateSelectPlaceholder( field, value, hasBlankChoice ) {
	let [ option ] = field.options;
	const isPlaceholder = option?.dataset.placeholder === 'true' ||
		option?.classList.contains( 'frm-select-placeholder' );
	if ( ! value && ! hasBlankChoice ) {
		if ( isPlaceholder ) {
			option.remove();
		}
		return;
	}

	if ( ! option || option.value !== '' || ( ! hasBlankChoice && ! isPlaceholder ) ) {
		option = frmDom.tag( 'option', { value: '' } );
		const hasDefault = Array.from( field.options ).some( choice => choice.defaultSelected );
		field.prepend( option );
		if ( ! hasDefault ) {
			field.selectedIndex = 0;
		}
	}

	option.textContent = value;
	option.classList.toggle( 'frm-select-placeholder', value !== '' );
	if ( value ) {
		option.dataset.placeholder = 'true';
	} else {
		delete option.dataset.placeholder;
	}
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
