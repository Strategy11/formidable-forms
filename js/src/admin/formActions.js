function checkActiveAction( type ) {
	const actionTriggers = document.querySelectorAll( `.frm_${ type }_action` );

	if ( isAtLimitForActionType( type ) ) {
		const addAlreadyUsedClass = getLimitForActionType( type ) > 0;
		markActionTriggersInactive( actionTriggers, addAlreadyUsedClass );
		return;
	}

	markActionTriggersActive( actionTriggers );
}

function markActionTriggersActive( triggers ) {
	triggers.forEach(
		trigger => {
			if ( trigger.querySelector( '.frm_show_upgrade' ) ) {
				// Prevent disabled action becoming active.
				return;
			}

			trigger.classList.remove( 'frm_inactive_action', 'frm_already_used' );
			trigger.classList.add( 'frm_active_action' );
		}
	);
}

function markActionTriggersInactive( triggers, addAlreadyUsedClass ) {
	triggers.forEach(
		trigger => {
			trigger.classList.remove( 'frm_active_action' );
			trigger.classList.add( 'frm_inactive_action' );
			if ( addAlreadyUsedClass ) {
				trigger.classList.add( 'frm_already_used' );
			}
		}
	);
}

function isAtLimitForActionType( type ) {
	let atLimit = getNumberOfActionsForType( type ) >= getLimitForActionType( type );

	const hookName = 'frm_action_at_limit';
	const hookArgs = { type };
	atLimit = wp.hooks.applyFilters( hookName, atLimit, hookArgs );

	return atLimit;
}

function getLimitForActionType( type ) {
	return parseInt( jQuery( `.frm_${ type }_action` ).data( 'limit' ), 10 );
}

/**
 * @param {string} type
 *
 * @return {number} The number of actions for the specified type.
 */
function getNumberOfActionsForType( type ) {
	if ( [ 'paypal', 'stripe', 'square' ].includes( type ) ) {
		type = 'payment';
	}
	return jQuery( `.frm_single_${ type }_settings` ).length;
}

module.exports = { checkActiveAction, isAtLimitForActionType };
