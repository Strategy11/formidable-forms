import { PREFIX } from '../shared';

/**
 * Gets essential DOM elements.
 *
 * @return {Object} DOM elements.
 */
function getDOMElements() {
	// Body Elements
	const bodyContent = document.querySelector( '#post-body-content' );
	const bodyElements = {
		bodyContent,
		bodyContentChildren: Array.from( bodyContent?.children || []),
		createFormButton: document.querySelector( `#${ PREFIX }-create-form` ),
		pageTitle: document.querySelector( `#${ PREFIX }-page-title` ),
		upsellBanner: document.querySelector( `#${ PREFIX }-upsell-banner` )
	};

	// Templates Elements
	const templatesList = document.querySelector( `#${ PREFIX }-list` );
	const templates = {
		templatesList,
		featuredTemplatesList: document.querySelector(
			`#${ PREFIX }-featured-list`
		),
		templateItems: templatesList?.querySelectorAll( `.${ PREFIX }-item` ),
		twinFeaturedTemplateItems: templatesList?.querySelectorAll(
			`.${ PREFIX }-featured-item`
		)
	};

	// Custom Templates Section Element
	const customTemplatesSection = document.querySelector(
		`#${ PREFIX }-custom-list-section`
	);
	const customTemplates = {
		customTemplatesSection,
		customTemplatesTitle: customTemplatesSection?.querySelector(
			`#${ PREFIX }-custom-list-title`
		),
		customTemplatesList: customTemplatesSection?.querySelector(
			`#${ PREFIX }-custom-list`
		),
		customTemplateItems: customTemplatesSection?.querySelectorAll(
			`.${ PREFIX }-item`
		)
	};

	// Sidebar Elements
	const searchInput = document.querySelector( '#template-search-input' );
	const allTemplatesCategory = document.querySelector(
		`.${ PREFIX }-cat-item[data-category="all-templates"]`
	);
	const favoritesCategory = document.querySelector(
		`.${ PREFIX }-cat-item[data-category="favorites"]`
	);
	const sidebar = {
		searchInput,
		allTemplatesCategory,
		favoritesCategory,
		favoritesCategoryCountEl: favoritesCategory?.querySelector(
			`.${ PREFIX }-cat-count`
		)
	};

	return {
		...bodyElements,
		...templates,
		...customTemplates,
		...sidebar
	};
}

export default getDOMElements;
