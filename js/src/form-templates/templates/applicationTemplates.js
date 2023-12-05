/**
 * Internal dependencies
 */
import { addApplicationTemplatesElement, createApplicationTemplates } from '../elements';
import { addApplicationTemplateEvents } from '../events';
import { doJsonFetch, canAccessApplicationDashboard } from '../shared';

/**
 * Adds application templates if the user has dashboard access.
 *
 * @return {void}
 */
export function maybeAddApplicationTemplates() {
	// Exit if the user doesn't have permission to see application dashboard
	if ( ! canAccessApplicationDashboard ) {
		return;
	}

	doJsonFetch( 'get_applications_data&view=templates' ).then( ( data ) => {
		setupApplicationTemplates( data );
	});
}

/**
 * Sets up application templates by creating HTML elements, injecting them into the DOM,
 * and adding event handlers.
 *
 * @private
 * @param {Object} data The data object containing information for application templates.
 * @return {void}
 */
function setupApplicationTemplates( data ) {
	// Create application templates
	createApplicationTemplates( data.templates );

	// Inject templates into the DOM
	addApplicationTemplatesElement();

	// Set up event handling
	addApplicationTemplateEvents();
}
