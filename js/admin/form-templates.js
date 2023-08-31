( ( wp ) => {

	/**
	 * Globals: frmFormTemplatesVars, frmDom
	 *
	 * @since x.x
	 */
	let { favoritesCount, FEATURED_TEMPLATES_KEYS } =  frmFormTemplatesVars;
	const { doJsonPost } = frmDom.ajax;
	const { onClickPreventDefault } = frmDom.util;

	/**
	 * Represents the FrmFormTemplates class.
	 *
	 * @since x.x
	 * @class FrmFormTemplates
	 */
	class FrmFormTemplates {

		/**
		 * Class for category items.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static CATEGORY_CLASS = 'frm-form-templates-cat-item';

		/**
		 * Class for templates featured list.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static FEATURED_TEMPLATES_LIST_CLASS = 'frm-form-templates-featured-list';

		/**
		 * Class for templates list.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static TEMPLATES_LIST_CLASS = 'frm-form-templates-list';

		/**
		 * Class for template items.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static TEMPLATE_CLASS = 'frm-form-templates-item';

		/**
		 * Class for featured template items.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static FEATURED_TEMPLATE_CLASS = 'frm-form-templates-featured-item';

		/**
		 * Class for favorite template items.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static FAVORITE_TEMPLATE_CLASS = 'frm-form-templates-favorite-item';

		/**
		 * Class for favorite buttons.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static FAVORITE_BUTTON_CLASS = 'frm-form-templates-item-favorite-button';

		/**
		 * Class added to an element to mark it as hidden.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static HIDDEN_CLASS = 'frm-form-templates-hidden';

		/**
		 * Class added to an element to mark it as the current item.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static CURRENT_CLASS = 'frm-current';

		/**
		 * Class constant for the favorite heart icon.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static FAVORITE_HEART_ICON = '#frm_heart_solid_icon';

		/**
		 * Class constant for the default heart icon.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static DEFAULT_HEART_ICON = '#frm_heart_icon';

		/**
		 * Initializes the FrmFormTemplates instance.
		 *
		 * @since x.x
		 * @constructor
		 */
		constructor() {
			/**
			 * Action hook triggered before class initialization.
			 *
			 * @since x.x
			 * @hook frmFormTemplates.beforeInitialize
			 */
			wp.hooks.doAction( 'frmFormTemplates.beforeInitialize', this );

			// Initialize the class properties and methods
			this.initialize();

			/**
			 * Action hook triggered after class initialization.
			 *
			 * @since x.x
			 * @hook frmFormTemplates.afterInitialize
			 */
			wp.hooks.doAction( 'frmFormTemplates.afterInitialize', this );
		}

		/**
		 * Main method for initializing the FrmFormTemplates instance.
		 *
		 * @since x.x
		 */
		initialize() {
			/// Initialize DOM elements and other properties
			this.initProperties();

			// Create a categorized list of templates
			this.buildCategorizedTemplates();

			// Attach event listeners for user interactions
			this.addEventListeners();
		}

		/**
		 * Initializes the properties for the FrmFormTemplates instance.
		 *
		 * @since x.x
		 */
		initProperties() {
			/**
			 * Create Form button element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.createFormButton = document.querySelector( '#frm-form-templates-create-form' );

			/**
			 * Page Title element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.pageTitle = document.querySelector( '#frm-form-templates-page-title' );

			/**
			 * Featured Templates List container.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.featuredTemplatesList = document.querySelector( `#${this.constructor.FEATURED_TEMPLATES_LIST_CLASS}` );

			/**
			 * Upsell Banner container.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.upsellBanner = document.querySelector( '#frm-form-templates-upsell-banner' );

			/**
			 * Templates List container.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.templatesList = document.querySelector( `#${this.constructor.TEMPLATES_LIST_CLASS}` );

			/**
			 * Template Items.
			 *
			 * @since x.x
			 * @type {NodeList}
			 */
			this.templateItems = this.templatesList?.querySelectorAll( `.${this.constructor.TEMPLATE_CLASS}` );

			/**
			 * Nested Featured Template Items within the templatesList.
			 *
			 * @since x.x
			 * @type {NodeList}
			 */
			this.nestedFeaturedTemplateItems = this.templatesList?.querySelectorAll( `.${this.constructor.FEATURED_TEMPLATE_CLASS}` );

			/**
			 * Custom Templates List Section element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.customTemplatesSection = document.querySelector( '#frm-form-templates-custom-list-section' );

			/**
			 * Custom Templates List Title element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.customTemplatesTitle = this.customTemplatesSection?.querySelector( '#frm-form-templates-custom-list-title' );

			/**
			 * Custom Templates List container.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.customTemplatesList = this.customTemplatesSection?.querySelector( '#frm-form-templates-custom-list' );

			/**
			 * Custom Template Items.
			 *
			 * @since x.x
			 * @type {NodeList}
			 */
			this.customTemplateItems = this.customTemplatesList?.querySelectorAll( `.${this.constructor.TEMPLATE_CLASS}` );

			/**
			 * Object to hold Categorized Templates.
			 * Keys will be category names and values will be arrays of corresponding templates.
			 *
			 * @since x.x
			 * @type {Object}
			 */
			this.categorizedTemplates = {};

			/**
			 * The currently Selected Category. Defaults to 'all-templates'.
			 *
			 * @since x.x
			 * @type {string}
			 */
			this.selectedCategory = 'all-templates';

			/**
			 * The currently Selected Category element. Defaults to 'All Templates' category element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.selectedCategoryEl = document.querySelector( `.${this.constructor.CATEGORY_CLASS}[data-category="all-templates"]` );

			/**
			 * Favortes Category element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.favoritesCategory = document.querySelector( `.${this.constructor.CATEGORY_CLASS}[data-category="favorites"]` );

			/**
			 * Favortes Category Count element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.favoritesCategoryCountEl = this.favoritesCategory?.querySelector( '.frm-form-templates-cat-count' );

			/**
			 * Body Content element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.bodyContent = document.querySelector( '#post-body-content' );

			/**
			 * Body Content Children.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.bodyContentChildren = Array.from( this.bodyContent?.children );
		}

		/**
		 * Builds a categorized list of templates.
		 *
		 * @since x.x
		 */
		buildCategorizedTemplates() {
			this.templateItems.forEach( template => {
				// Extract and split the categories from data attribute
				const categories = template.getAttribute( 'data-categories' ).split( ',' );

				categories.forEach( category => {
					// Initialize the category array if not already done
					if ( ! this.categorizedTemplates[category]) {
						this.categorizedTemplates[category] = [];
					}

					// Add the template to the appropriate category
					this.categorizedTemplates[category].push( template );
				});
			});
		}

		/**
		 * Attaches event listeners for handling user interactions.
		 *
		 * @since x.x
		 */
		addEventListeners() {
			// Attach click event listeners to each sidebar category
			const categoryItems = document.querySelectorAll( `.${this.constructor.CATEGORY_CLASS}` );
			categoryItems.forEach( category => {
				category.addEventListener( 'click', this.onCategoryClick );
			});

			// Attach click event listeners to each favorite button
			const favoriteButtons = document.querySelectorAll( `.${this.constructor.FAVORITE_BUTTON_CLASS}` );
			favoriteButtons.forEach( favoriteButton => {
				onClickPreventDefault( favoriteButton, this.onFavoriteButtonClick );
			});
		}

		/**
		 * Handles the click event on a category item.
		 *
		 * @since x.x
		 *
		 * @param {Event} event The click event object.
		 */
		onCategoryClick = ( event ) => {
			const clickedCategory = event.currentTarget;
			const newSelectedCategory = clickedCategory.getAttribute( 'data-category' );

			// If the selected category hasn't changed, return early
			if ( this.selectedCategory === newSelectedCategory ) {
				return;
			}

			/**
			 * Filter hook to modify the selected category.
			 *
			 * @since x.x
			 *
			 * @hook frmFormTemplates.selectedCategory
			 * @param {string} selectedCategory The selected category.
			 * @param {FormTemplates} instance The FormTemplates class instance.
			 */
			this.selectedCategory = wp.hooks.applyFilters( 'frmFormTemplates.selectedCategory', newSelectedCategory, this );

			// Remove the 'frm-current' class from the previously selected category element
			this.selectedCategoryEl.classList.remove( this.constructor.CURRENT_CLASS );

			// Update the selected category element and add the 'frm-current' class to it
			this.selectedCategoryEl = clickedCategory;
			this.selectedCategoryEl.classList.add( this.constructor.CURRENT_CLASS );

			// Updates the main body content based on the selected category
			this.updateBodyContent();
		}

		/**
		 * Updates the main body content based on the selected category.
		 *
		 * @since x.x
		 */
		updateBodyContent() {
			// Update the displayed category title.
			this.updatePageTitle();

			// Display templates based on selected category.
			this.isAllTemplatesCategory( this.selectedCategory ) ? this.displayAllTemplates() : this.displayCategoryTemplates();

			// Fade-in body content for a smooth visual transition.
			this.fadeIn( this.bodyContent );
		}

		/**
		 * Updates the page title based on the selected category.
		 *
		 * @since x.x
		 */
		updatePageTitle() {
			const categoryText = this.selectedCategoryEl.querySelector( '.frm-form-templates-cat-text' ).textContent;
			this.pageTitle.textContent = categoryText;
		}

		/**
		 * Displays all templates when 'All Templates' is the selected category.
		 *
		 * @since x.x
		 */
		displayAllTemplates() {
			this.showElements([ ...this.bodyContentChildren, ...this.templateItems ]);
			this.hideElements([ this.customTemplatesSection, ...this.nestedFeaturedTemplateItems ]);
		}

		/**
		 * Displays templates based on the currently selected category.
		 *
		 * @since x.x
		 */
		displayCategoryTemplates() {
			// Hide existing elements and templates to prepare for the new display
			this.hideElements([ ...this.bodyContentChildren, ...this.templateItems ]);

			// Show the page title
			this.show( this.pageTitle );

			switch ( this.selectedCategory ) {
				case 'favorites':
					// Display favorite templates
					this.displayFavorites();
					break;
				case 'custom':
					// Display custom templates
					this.displayCustomTemplates();
					break;
				default:
					// Display templates according to their categories
					this.displayCategorizedTemplates();
					break;
			}
		}

		/**
		 * Displays favorite templates.
		 *
		 * @since x.x
		 */
		displayFavorites() {
			const elementsToShow = [];

			const favoriteItems = this.bodyContent.querySelectorAll( `.${this.constructor.FAVORITE_TEMPLATE_CLASS}` );
			elementsToShow.push( ...favoriteItems );

			if ( favoritesCount.default > 0 ) {
				elementsToShow.push( this.templatesList );
			}

			// Logic for custom favorites
			if ( 0 !== favoritesCount.custom ) {
				const nonFavCustomTemplates = Array.from( this.customTemplateItems ).filter( template => ! this.isFavoriteTemplate( template ) );
				this.hideElements( nonFavCustomTemplates );

				elementsToShow.push( this.customTemplatesSection );
				elementsToShow.push( this.customTemplatesList );

				favoritesCount.default === 0 ? this.hide( this.customTemplatesTitle ) : elementsToShow.push( this.customTemplatesTitle );
			}

			this.showElements( elementsToShow );
		}

		/**
		 * Displays custom templates.
		 *
		 * @since x.x
		 */
		displayCustomTemplates() {
			this.showElements([ this.customTemplatesSection, this.customTemplatesList, ...this.customTemplateItems ]);
		}

		/**
		 * Displays templates of a specific category.
		 *
		 * @since x.x
		 */
		displayCategorizedTemplates() {
			this.showElements([ this.templatesList, ...this.categorizedTemplates[ this.selectedCategory ] ]);
		}

		/**
		 * Handles the click event on the add to favorite button.f
		 *
		 * @since x.x
		 * @param {Event} event The click event object.
		 */
		onFavoriteButtonClick = ( event ) => {
			/**
			 * Initial Checks and Setup.
			 *
			 * Validate button state and gather template info.
			 */
			const favoriteButton = event.currentTarget;

			// Check if the button is "disabled"
			if ( 'true' === favoriteButton.getAttribute( 'data-disabled' ) ) {
				return;
			}

			// Disable the button temporarily to prevent multiple clicks
			favoriteButton.setAttribute( 'data-disabled', 'true' );

			// Get Necessary Template Information
			const template = favoriteButton.closest( `.${this.constructor.TEMPLATE_CLASS}` );
			const templateId = template.dataset.id;
			const isFavorited = this.isFavoriteTemplate( template );
			const isCustomTemplate = this.isCustomTemplate( template );
			const isFeaturedTemplate = this.isFeaturedTemplate( template );

			/**
			 * Toggle UI Elements.
			*
			* Update favorite status on UI.
			*/
			template.classList.toggle( this.constructor.FAVORITE_TEMPLATE_CLASS, ! isFavorited );

			// Initialize a reference for the twin featured template in the other list, if applicable
			let twinFeaturedTemplate = null;
			// Check if the template is featured and find its twin version in the respective list
			if ( isFeaturedTemplate ) {
				const templateList = template.closest( `#${this.constructor.TEMPLATES_LIST_CLASS}` ) ? this.featuredTemplatesList : this.templatesList;
				twinFeaturedTemplate = templateList?.querySelector( `.${this.constructor.TEMPLATE_CLASS}[data-id="${templateId}"]` );
				// Toggle favorite status of the twin featured template, if found
				twinFeaturedTemplate?.classList.toggle( this.constructor.FAVORITE_TEMPLATE_CLASS, ! isFavorited );
			}

			/**
			 * Update Counters and Icons.
			 *
			 * Modify favorite counts and toggle heart icon.
			 */
			const operation = isFavorited ? 'remove' : 'add';
			const heartSVGIcon = template.querySelector( `.${this.constructor.FAVORITE_BUTTON_CLASS} use` );
			const twinTemplateHeartSVGIcon = twinFeaturedTemplate?.querySelector( `.${this.constructor.FAVORITE_BUTTON_CLASS} use` );

			if ( 'add' === operation ) {
				// Increment the total favorite count
				++favoritesCount.total;
				// Increment custom or default favorites count based on template type
				isCustomTemplate ? ++favoritesCount.custom : ++favoritesCount.default;
				// Update heart icon to filled (favorited state)
				heartSVGIcon.setAttribute( 'xlink:href', this.constructor.FAVORITE_HEART_ICON );
				twinTemplateHeartSVGIcon?.setAttribute( 'xlink:href', this.constructor.FAVORITE_HEART_ICON );
			} else {
				// Decrement the total favorite count
				--favoritesCount.total;
				// Decrement custom or default favorites count based on template type
				isCustomTemplate ? --favoritesCount.custom : --favoritesCount.default;
				// Update heart icon to outline (non-favorited state)
				heartSVGIcon.setAttribute( 'xlink:href', this.constructor.DEFAULT_HEART_ICON );
				twinTemplateHeartSVGIcon?.setAttribute( 'xlink:href', this.constructor.DEFAULT_HEART_ICON );
			}

			// Update total favorite count displayed in the "Favorites" sidebar category
			this.favoritesCategoryCountEl.textContent = favoritesCount.total;

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
		}

		/**
		 * Checks if the category is "All Templates".
		 *
		 * @since x.x
		 * @param {string} category The category slug.
		 * @returns {boolean} True if the category is "All Templates", otherwise false.
		 */
		isAllTemplatesCategory( category ) {
			return 'all-templates' === category;
		}

		/**
		 * Checks if the category is "Favorites".
		 *
		 * @since x.x
		 * @param {string} category The category slug.
		 * @returns {boolean} True if the category is "Favorites", otherwise false.
		 */
		isFavoritesCategory( category ) {
			return 'favorites' === category;
		}

		/**
		 * Checks if a template is custom.
		 *
		 * @since x.x
		 * @param {HTMLElement} template The template element.
		 * @returns {boolean} True if the template is custom, otherwise false.
		 */
		isCustomTemplate( template ) {
			return template?.classList.contains( 'frm-form-templates-custom-item' );
		}

		/**
		 * Checks if a template is a favorite.
		 *
		 * @since x.x
		 * @param {HTMLElement} template The template element.
		 * @returns {boolean} True if the template is a favorite, otherwise false.
		 */
		isFavoriteTemplate( template ) {
			return template?.classList.contains( this.constructor.FAVORITE_TEMPLATE_CLASS );
		}

		/**
		 * Checks if a template is featured.
		 *
		 * @since x.x
		 * @param {HTMLElement} template The template element.
		 * @returns {boolean} True if the template is featured, otherwise false.
		 */
		isFeaturedTemplate( template ) {
			return FEATURED_TEMPLATES_KEYS.includes( Number( template.dataset.id ) );
		}

		/**
		 * Shows all child elements of the Body Content element.
		 *
		 * @since x.x
		 */
		showBodyContentElements() {
			this.showElements([ ...this.bodyContentChildren, ...this.templateItems ]);
		}

		/**
		 * Hides all child elements of the Body Content element.
		 *
		 * @since x.x
		 */
		hideBodyContentElements() {
			const {
				bodyContentChildren,
				templateItems,
				customTemplateItems,
				customTemplatesTitle,
				customTemplatesList
			} = this;

			this.hideElements([ ...bodyContentChildren, ...templateItems, ...customTemplateItems, customTemplatesTitle, customTemplatesList ]);
		}

		/**
		 * Shows specified elements by removing the hidden class.
		 *
		 * @since x.x
		 * @param {Array<Element>} elements An array of elements to show.
		 */
		showElements( elements ) {
			elements?.forEach( element => this.show( element ) );
		}

		/**
		 * Hides specified elements by adding the hidden class.
		 *
		 * @since x.x
		 *
		 * @param {Array<Element>} elements An array of elements to hide.
		 */
		hideElements( elements ) {
			elements?.forEach( element => this.hide( element ) );
		}

		/**
		 * Removes the hidden class to show the element.
		 *
		 * @since x.x
		 * @param {Element} element The element to show.
		 */
		show( element ) {
			element?.classList.remove( this.constructor.HIDDEN_CLASS );
		}

		/**
		 * Adds the hidden class to hide the element.
		 *
		 * @since x.x
		 * @param {Element} element The element to hide.
		 */
		hide( element ) {
			element?.classList.add( this.constructor.HIDDEN_CLASS );
		}

		/**
		 * Applies a fade-in animation to an element.
		 *
		 * @since x.x
		 * @param {HTMLElement} element The element to apply the fade-in to.
		 * @param {string} [fadingClass='frm-form-templates-flex'] The CSS class to apply during the fading.
		 */
		fadeIn( element, fadingClass = 'frm-form-templates-flex' ) {
			if ( ! element ) {
				return;
			}

			element.classList.add( 'frm-form-templates-fadein', fadingClass );

			element.addEventListener( 'animationend', () => {
				element.classList.remove( 'frm-form-templates-fadein', fadingClass );
			}, { once: true });
		}
	}

	// Initialize the FrmFormTemplates class
	document.addEventListener( 'DOMContentLoaded', () => {
		new FrmFormTemplates();
	});
})( window.wp );
