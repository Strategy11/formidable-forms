/**
 * Copyright (C) 2010 Formidable Forms
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { PLUGIN_URL, PREFIX, tag, div, span, a, img } from '../shared';

/**
 * Create and return Empty State element.
 *
 * @since x.x
 * @returns {HTMLElement} The Empty State element.
 */
function createEmptyStateElement() {
	const button = a({
		class: 'button button-primary frm-button-primary',
		href: '#'
	});
	button.setAttribute( 'role', 'button' );

	return div({
		id: `${PREFIX}-empty-state`,
		children: [
			img({ src: `${PLUGIN_URL}/images/form-templates/empty-state.svg`, alt: __( 'Empty State', 'formidable' ) }),
			tag( 'h3', {
				class: `${PREFIX}-title`
			}),
			span({
				class: `${PREFIX}-text`
			}),
			button
		]
	});
};

export default createEmptyStateElement;
