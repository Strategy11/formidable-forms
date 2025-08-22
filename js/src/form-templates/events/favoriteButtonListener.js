/**
 * External dependencies
 */
import { onClickPreventDefault, addToRequestQueue, hide } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { PREFIX, getState, setSingleState } from '../shared';
import { showFavoritesEmptyState } from '../ui';
import { isFavoriteTemplate, isCustomTemplate, isFeaturedTemplate, isFavoritesCategory } from '../utils';

const FAVORITE_BUTTON_CLASS = `.${ PREFIX }-item-favorite-button`;
const HEART_ICON_SELECTOR = `${ FAVORITE_BUTTON_CLASS } use`;
const FILLED_HEART_ICON = '#frm_heart_solid_icon';
const LINEAR_HEART_ICON = '#frm_heart_icon';
const OPERATION = {
	ADD: 'add',
	REMOVE: 'remove'
};

/**
 * Manages event handling for favorite buttons.
 *
 * @return {void}
 */
function addFavoriteButtonEvents() {
	const favoriteButtons = document.querySelectorAll( FAVORITE_BUTTON_CLASS );

	// Attach click event listeners to each favorite button
	favoriteButtons.forEach( favoriteButton =>
		onClickPreventDefault( favoriteButton, onFavoriteButtonClick )
	);
}

/**
 * Handles the click event on the add to favorite button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onFavoriteButtonClick = event => {
	const favoriteButton = event.currentTarget;
	const { templatesList, featuredTemplatesList, favoritesCategoryCountEl, customTemplatesTitle } = getElements();

	/**
	 * Get necessary template information
	 */
	const template = favoriteButton.closest( '.frm-card-item' );
	const templateId = template.dataset.id;
	const isFavorited = isFavoriteTemplate( template );
	const isTemplateCustom = isCustomTemplate( template );
	const isTemplateFeatured = isFeaturedTemplate( template );

	/**
	 * Toggle the favorite status in the UI.
	 * If template is featured, toggle its twin version in the respective list.
	 */
	let twinFeaturedTemplate = null;

	template.classList.toggle( `${ PREFIX }-favorite-item`, ! isFavorited );
	if ( isTemplateFeatured ) {
		const templateList = template.closest( `#${ PREFIX }-list` )
			? featuredTemplatesList
			: templatesList;

		if ( templateList ) {
			twinFeaturedTemplate = templateList.querySelector(
				`.frm-card-item[data-id="${ templateId }"]`
			);
			// Toggle twin template's favorite status
			twinFeaturedTemplate.classList.toggle(
				`${ PREFIX }-favorite-item`,
				! isFavorited
			);
		}
	}

	/**
	 * Update favorite counts and icons based on the new state
	 */
	const { selectedCategory, favoritesCount } = getState();
	const currentOperation = isFavorited ? OPERATION.REMOVE : OPERATION.ADD;
	const heartIcon = template.querySelector( HEART_ICON_SELECTOR );
	const twinTemplateHeartIcon =
		twinFeaturedTemplate?.querySelector( HEART_ICON_SELECTOR );

	if ( OPERATION.ADD === currentOperation ) {
		// Increment favorite counts
		++favoritesCount.total;
		isTemplateCustom ? ++favoritesCount.custom : ++favoritesCount.default; // eslint-disable-line no-unused-expressions
		// Set heart icon to filled
		heartIcon.setAttribute( 'xlink:href', FILLED_HEART_ICON );
		twinTemplateHeartIcon?.setAttribute( 'xlink:href', FILLED_HEART_ICON );
	} else {
		// Decrement favorite counts
		--favoritesCount.total;
		isTemplateCustom ? --favoritesCount.custom : --favoritesCount.default; // eslint-disable-line no-unused-expressions
		// Set heart icon to outline
		heartIcon.setAttribute( 'xlink:href', LINEAR_HEART_ICON );
		twinTemplateHeartIcon?.setAttribute( 'xlink:href', LINEAR_HEART_ICON );
	}

	// Update UI and state to reflect new favorite counts
	favoritesCategoryCountEl.textContent = favoritesCount.total;
	setSingleState( 'favoritesCount', favoritesCount );

	/**
	 * Hide UI elements if 'Favorites' is active and counts are zero.
	 */
	if ( isFavoritesCategory( selectedCategory ) ) {
		if ( 0 === favoritesCount.total ) {
			showFavoritesEmptyState();
		}

		hide( template );

		if ( 0 === favoritesCount.default ) {
			hide( templatesList );
		}

		if ( 0 === favoritesCount.custom || 0 === favoritesCount.default ) {
			hide( customTemplatesTitle );
		}
	}

	// Update server-side data for favorite templates
	addToRequestQueue( () => updateFavoriteTemplate( templateId, currentOperation, isTemplateCustom ) );
};

/**
 * Update server-side data for favorite templates.
 *
 * @param {string}  id        The template ID.
 * @param {string}  operation The operation to perform ('add' or 'remove').
 * @param {boolean} isCustom  Flag indicating whether the template is custom.
 * @return {Promise<any>} The result of the server-side update.
 */
function updateFavoriteTemplate( id, operation, isCustom ) {
	const formData = new FormData();
	const { doJsonPost } = frmDom.ajax;

	formData.append( 'template_id', id );
	formData.append( 'operation', operation );
	formData.append( 'is_custom_template', isCustom );

	return doJsonPost( 'add_or_remove_favorite_template', formData );
}

export default addFavoriteButtonEvents;
