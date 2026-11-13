/**
 * Check every field carried by a drag before a container accepts the drop.
 *
 * @since x.x
 * @param {HTMLElement} draggable Field, palette button, or row being dragged.
 * @param {HTMLElement} droppable Destination row or container.
 * @return {boolean} Whether all fields are compatible with the container.
 */
export function allowContainerDrop( draggable, droppable ) {
	const inSection = null !== droppable.closest( '.start_divider' );
	const inRepeater = null !== droppable.closest( '.repeat_section' );
	if ( ! inSection && ! inRepeater ) {
		return true;
	}

	const fields = draggable.matches( '.form-field, .frm-new-field' )
		? [ draggable ] : Array.from( draggable.querySelectorAll( '.form-field' ) );

	return fields.every( field => {
		const type = field.dataset.ftype || field.dataset.type || field.id.split( '|' )[ 0 ];
		if ( inSection && ( [ 'break', 'submit', 'form' ].includes( type ) ||
			( type === 'divider' && ! droppable.classList.contains( 'frm-layout-panels' ) ) ) ) {
			return false;
		}
		if ( ! inRepeater ) {
			return true;
		}
		if ( field.classList.contains( 'frm-no-repeater' ) ) {
			return false;
		}

		/**
		 * Let add-ons reject individual fields, including children of dragged rows.
		 *
		 * @since 6.27
		 * @param {boolean}     denyDropInRepeater Whether the field is incompatible.
		 * @param {HTMLElement} field              The field or palette button being dragged.
		 */
		return ! wp.hooks.applyFilters( 'frm_deny_drop_in_repeater', false, field );
	} );
}
