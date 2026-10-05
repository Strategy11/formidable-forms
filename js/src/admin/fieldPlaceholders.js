/**
 * Restore placeholder attributes before the builder scans its grid or starts requests.
 *
 * @since x.x
 * @return {void}
 */
export function hydrateFieldPlaceholders() {
	const manifestElement = document.getElementById( 'frm-field-placeholders' );
	const fieldsContainer = document.getElementById( 'frm-show-fields' );
	if ( ! manifestElement || ! fieldsContainer ) {
		return;
	}

	const { fields, definitions } = JSON.parse( manifestElement.textContent );
	fieldsContainer.querySelectorAll( 'li[data-frm-placeholder]' ).forEach( field => {
		const [ fieldId, definitionIndex ] = fields[ field.dataset.frmPlaceholder ];
		const [ className, formId, type ] = definitions[ definitionIndex ];
		field.id = `frm_field_id_${ fieldId }`;
		field.className = className;
		field.dataset.fid = fieldId;
		field.dataset.formid = formId;
		field.dataset.ftype = type;
		delete field.dataset.frmPlaceholder;
	} );
	manifestElement.remove();
}
