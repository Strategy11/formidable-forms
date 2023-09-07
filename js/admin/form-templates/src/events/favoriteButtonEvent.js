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
 * Internal dependencies
 */
import { PREFIX, favoritesCount } from '../shared';
import { isFavoritesCategory, isFavoriteTemplate, isCustomTemplate, isFeaturedTemplate } from '../utils';
import { templatesList, featuredTemplatesList, favoritesCategoryCountEl } from '../elements';

function favoriteButtonEvent( selectedCategory ) {
	// Attach click event listeners to each favorite button
	const favoriteButtons = document.querySelectorAll( `.${PREFIX}-favorite-button` );
	favoriteButtons.forEach( favoriteButton => {
		onClickPreventDefault( favoriteButton, onFavoriteButtonClick );
	});
}

/**
 * Handles the click event on the add to favorite button.
 *
 * @since x.x
 * @param {Event} event The click event object.
 */
export const onFavoriteButtonClick = ( event ) => {
	const favoriteButton = event.currentTarget;

	// Check if the button is "disabled"
	if ( 'true' === favoriteButton.getAttribute( 'data-disabled' ) ) {
		return;
	}

	// Disable the button temporarily to prevent multiple clicks
	favoriteButton.setAttribute( 'data-disabled', 'true' );

	// Get Necessary Template Information
	const templateInfo = getTemplateInfo( favoriteButton );

	// Toggle UI Elements
	updateFavoriteStatus( templateInfo );

	/**
	 * Update Counters and Icons.
	 *
	 * Modify favorite counts and toggle heart icon.
	 */
	updateCountersAndIcons( templateInfo );

	/**
	 * Adjust UI Based on Current Category.
	 *
	 * Hide or show elements based on selected category.
	 */
	if ( this.isFavoritesCategory( this.selectedCategory ) ) {
		this.hide( template );

		if ( 0 === favoritesCount.default ) {
			this.hide( this.templatesList );
		}

		if ( 0 === favoritesCount.custom || 0 === favoritesCount.default ) {
			this.hide( this.customTemplatesTitle );
		}
	}

	/**
	 * Update Server.
	 *
	 * Prepare and send a request to update the favorite status on the server
	 */
	const formData = new FormData();
	formData.append( 'template_id', template.dataset.id );
	formData.append( 'operation', operation );
	formData.append( 'is_custom_template', isCustomTemplate );

	doJsonPost( 'add_or_remove_favorite_template', formData )
		.finally( () => {
			// Re-enable the button after the operation
			favoriteButton.setAttribute( 'data-disabled', 'false' );
		});
};

/**
 * Get necessary template information.
 *
 * @param {HTMLElement} favoriteButton The favorite button element
 * @returns {Object} The template information
 */
const getTemplateInfo = ( favoriteButton ) => {
	// Get Necessary Template Information
	const template = favoriteButton.closest( `.${PREFIX}-item` );

	return {
		template,
		templateId: template.dataset.id,
		isFavorited: isFavoriteTemplate( template ),
		isCustomTemplate: isCustomTemplate( template ),
		isFeaturedTemplate: isFeaturedTemplate( template )
	};
};

/**
 * Update favorite status on UI.
 *
 * @param {Object} templateInfo                     The template information.
 * @param {HTMLElement} templateInfo.template       The template element.
 * @param {boolean} templateInfo.isFavorited        Whether the template is favorited.
 * @param {boolean} templateInfo.isFeaturedTemplate Whether the template is featured.
 */
const updateFavoriteStatus = ({template, isFavorited, isFeaturedTemplate}) => {
	template.classList.toggle( `${PREFIX}-favorite-item`, ! isFavorited );

	// Initialize a reference for the twin featured template in the other list, if applicable
	let twinFeaturedTemplate = null;
	// Check if the template is featured and find its twin version in the respective list
	if ( isFeaturedTemplate ) {
		const templateList = template.closest( `#${PREFIX}-featured-list` ) ? featuredTemplatesList : templatesList;
		twinFeaturedTemplate = templateList?.querySelector( `.${PREFIX}-item[data-id="${templateId}"]` );
		// Toggle favorite status of the twin featured template, if found
		twinFeaturedTemplate?.classList.toggle( `${PREFIX}-favorite-item`, ! isFavorited );
	}
};

/**
 * Modify favorite counts and toggle heart icon
 *
 * @param {Object} templateInfo                           The template information.
 * @param {HTMLElement} templateInfo.template             The template element.
 * @param {boolean} templateInfo.isFavorited              Whether the template is favorited.
 * @param {boolean} templateInfo.isCustomTemplate         Whether the template is custom.
 * @param {HTMLElement} templateInfo.twinFeaturedTemplate The twin featured template element, if applicable.
 */
const updateCountersAndIcons = ({template, isFavorited, isCustomTemplate, twinFeaturedTemplate}) => {
	const HEART_ICON_SELECTOR = `.${PREFIX}-item-favorite-button use`;
	const FILLED_HEART_ICON = '#frm_heart_solid_icon';
	const LINEAR_HEART_ICON = '#frm_heart_icon';

	const operation = isFavorited ? 'remove' : 'add';
	const heartIcon = template.querySelector( HEART_ICON_SELECTOR );
	const twinTemplateHeartIcon = twinFeaturedTemplate?.querySelector( HEART_ICON_SELECTOR );

	if ( 'add' === operation ) {
		// Increment the total favorite count
		++favoritesCount.total;
		// Increment custom or default favorites count based on template type
		isCustomTemplate ? ++favoritesCount.custom : ++favoritesCount.default;
		// Update heart icon to filled (favorited state)
		heartIcon.setAttribute( 'xlink:href', FILLED_HEART_ICON );
		twinTemplateHeartIcon?.setAttribute( 'xlink:href', FILLED_HEART_ICON );
	} else {
		// Decrement the total favorite count
		--favoritesCount.total;
		// Decrement custom or default favorites count based on template type
		isCustomTemplate ? --favoritesCount.custom : --favoritesCount.default;
		// Update heart icon to outline (non-favorited state)
		heartIcon.setAttribute( 'xlink:href', LINEAR_HEART_ICON );
		twinTemplateHeartIcon?.setAttribute( 'xlink:href', LINEAR_HEART_ICON );
	}

	// Update total favorite count displayed in the "Favorites" sidebar category
	favoritesCategoryCountEl.textContent = favoritesCount.total;
};

/**
 * Adjust UI based on the currently selected category.
 *
 * @param {Object} templateInfo                    - The template information.
 * @param {HTMLElement} templateInfo.template      - The template element.
 * @param {boolean} templateInfo.isFavorited       - Whether the template is favorited.
 * @param {boolean} templateInfo.isCustomTemplate  - Whether the template is custom.
 */
const syncFavoritesUI = ({ template, isFavorited, isCustomTemplate }) => {
    if ( isFavoritesCategory( selectedCategory ) ) {
        hide( template );

        if ( 0 === favoritesCount.default ) {
            hide( templatesList );
        }

        if ( 0 === favoritesCount.custom || 0 === favoritesCount.default ) {
            hide( customTemplatesTitle );
        }
    }
};

/**
 * Update favorite status on the server.
 *
 * @param {Object} templateInfo                    - The template information.
 * @param {HTMLElement} templateInfo.template      - The template element.
 * @param {boolean} templateInfo.isFavorited       - Whether the template is favorited.
 * @param {boolean} templateInfo.isCustomTemplate  - Whether the template is custom.
 */
const updateServer = ({ template, isFavorited, isCustomTemplate }) => {
    const operation = isFavorited ? 'remove' : 'add';
    const formData = new FormData();
    formData.append( 'template_id', template.dataset.id );
    formData.append( 'operation', operation );
    formData.append( 'is_custom_template', isCustomTemplate );

    doJsonPost( 'add_or_remove_favorite_template', formData )
        .finally( () => {
            // Re-enable the button after the operation
            template.querySelector( '.favorite-button' ).setAttribute( 'data-disabled', 'false' );
        });
};


export default favoriteButtonEvent;
