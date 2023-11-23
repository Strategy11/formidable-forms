/**
 * Internal dependencies
 */
import { PREFIX, VIEW_SLUGS, FEATURED_TEMPLATES_KEYS } from '../shared';

/**
 * Checks if the category is "All Templates".
 *
 * @param {string} category The category slug.
 * @return {boolean} True if the category is "All Templates", otherwise false.
 */
export const isAllTemplatesCategory = category => VIEW_SLUGS.ALL_TEMPLATES === category;

/**
 * Checks if the category is "Favorites".
 *
 * @param {string} category The category slug.
 * @return {boolean} True if the category is "Favorites", otherwise false.
 */
export const isFavoritesCategory = category => VIEW_SLUGS.FAVORITES === category;

/**
 * Checks if the category is "Custom".
 *
 * @param {string} category The category slug.
 * @return {boolean} True if the category is "Custom", otherwise false.
 */
export const isCustomCategory = category => VIEW_SLUGS.CUSTOM === category;

/**
 * Checks if a template is a favorite.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is a favorite, otherwise false.
 */
export const isFavoriteTemplate = template =>
	isHTMLElement( template ) ? template.classList.contains( `${PREFIX}-favorite-item` ) : false;

/**
 * Checks if a template is custom.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is custom, otherwise false.
 */
export const isCustomTemplate = template =>
	isHTMLElement( template ) ? template.classList.contains( `${PREFIX}-custom-item` ) : false;

/**
 * Checks if a template is featured.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is featured, otherwise false.
 */
export const isFeaturedTemplate = template =>
	isHTMLElement( template ) ? FEATURED_TEMPLATES_KEYS.includes( Number( template.dataset.id ) ) : false;

/**
 * Checks if a template is locked.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is locked, otherwise false.
 */
export const isLockedTemplate = template =>
	isHTMLElement( template ) ? template.classList.contains( `${PREFIX}-locked-item` ) : false;

/**
 * Validates an email address using a regular expression.
 *
 * @param {string} email The email address to validate.
 * @return {boolean} True if the email address is valid, otherwise false.
 */
export const isValidEmail = email =>
	typeof email === 'string' ? /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i.test( email ) : false;

/**
 * Validates if the given element is an instance of HTMLElement.
 *
 * @private
 * @param {any} element Element to be checked.
 * @return {boolean} True if it's an HTMLElement, otherwise false.
 */
const isHTMLElement = element => element instanceof HTMLElement || console.warn( 'Invalid argument: Element must be an instance of HTMLElement' ) || false;
