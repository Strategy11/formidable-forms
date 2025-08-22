/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { PLUGIN_URL, HIDDEN_CLASS } from 'core/constants';
import { PREFIX as SKELETON_PREFIX } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { PREFIX, applicationsUrl } from '../shared';
import { addElements, getElements } from './elements';

const { tag, div, span, a, img } = window.frmDom;

// Application templates element
let applicationTemplates;

// Base URL for the thumbnail images of applications
const thumbnailBaseURL = `${PLUGIN_URL}/images/applications/thumbnails`;

/**
 * Create and return the application templates HTML element.
 *
 * @param {Object[]} applications Array of application objects.
 * @return {void}
 */
export function createApplicationTemplates( applications ) {
	if ( ! applications || ! applications.length ) {
		return;
	}

	const templateItems = applications.map( template => createTemplateItem( template ) );

	applicationTemplates = div({
		id: `${PREFIX}-applications`,
		className: HIDDEN_CLASS,
		children: [
			tag( 'h2', {
				text: __( 'Application Templates' ),
				className: 'frm-text-sm frm-mb-sm'
			}),
			tag( 'ul', {
				className: `${PREFIX}-list frm-list-grid-layout`,
				children: templateItems
			})
		]
	});
}

/**
 * Create and return an individual item element for a application template.
 *
 * @private
 * @param {Object} template The application object.
 * @return {HTMLElement} Element representing a single application template.
 */
function createTemplateItem( template ) {
	const { name, key, hasLiteThumbnail, isWebp } = template;
	// eslint-disable-next-line no-nested-ternary
	const thumbnailURL = hasLiteThumbnail ?
		( isWebp ? `${thumbnailBaseURL}/${key}.webp` : `${thumbnailBaseURL}/${key}.png` ) :
		`${thumbnailBaseURL}/placeholder.svg`;

	return tag( 'li', {
		className: 'frm-card-item',
		data: {
			href: `${applicationsUrl}&triggerViewApplicationModal=1&template=${key}`,
			'frm-search-text': name.toLowerCase()
		},
		children: [
			div({
				className: `${PREFIX}-item-icon`,
				child: img({ src: thumbnailURL })
			}),
			div({
				className: `${PREFIX}-item-body`,
				children: [
					span({
						text: __( 'Ready Made Solution', 'formidable' ),
						className: 'frm-meta-tag frm-orange-tag frm-text-xs'
					}),
					tag( 'h3', {
						text: name,
						className: 'frm-text-sm frm-font-medium frm-m-0'
					}),
					a({
						text: __( 'See all applications', 'formidable' ),
						className: 'frm-text-xs frm-font-semibold',
						href: applicationsUrl
					})
				]
			})
		]
	});
};

/**
 * Inject application Templates elements into the DOM and the elements object.
 *
 * @return {void}
 */
export function addApplicationTemplatesElement() {
	const elements = getElements();

	if ( elements.applicationTemplates || undefined === applicationTemplates ) {
		return;
	}

	elements.bodyContent.appendChild( applicationTemplates );

	addElements({
		applicationTemplates,
		applicationTemplatesTitle: applicationTemplates.querySelector( 'h2' ),
		applicationTemplatesList: applicationTemplates.querySelector( `.${PREFIX}-list` ),
		applicationTemplateItems: applicationTemplates.querySelectorAll( '.frm-card-item' )
	});
}
