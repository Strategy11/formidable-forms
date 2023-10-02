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
 * Checks if a template is a favorite.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is a favorite, otherwise false.
 */
export const isFavoriteTemplate = template =>
	template?.classList.contains( `${ PREFIX }-favorite-item` );

/**
 * Checks if a template is custom.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is custom, otherwise false.
 */
export const isCustomTemplate = template =>
	template?.classList.contains( `${ PREFIX }-custom-item` );

/**
 * Checks if a template is featured.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is featured, otherwise false.
 */
export const isFeaturedTemplate = template =>
	FEATURED_TEMPLATES_KEYS.includes( Number( template.dataset.id ) );

/**
 * Checks if a template is locked.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is locked, otherwise false.
 */
export const isLockedTemplate = template =>
	template?.classList.contains( `${ PREFIX }-locked-item` );

/**
 * Validates an email address using a regular expression.
 *
 * @param {string} email The email address to validate.
 * @return {boolean} True if the email address is valid, otherwise false.
 */
export const isValidEmail = email => {
	const regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;
	return regex.test( email );
};
