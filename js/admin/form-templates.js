( ( wp ) => {

	/**
	 * Globals: frmFormTemplatesVars, frmDom, wp
	 *
	 * @since x.x
	 */
	// WordPress globals
	const { __ } = wp.i18n;
	// Internal globals
	let { favoritesCount, FEATURED_TEMPLATES_KEYS } = frmFormTemplatesVars;
	const { url: pluginURL } = frmGlobal;
	const { tag, div, span, a, img, search } = frmDom;
	const { doJsonPost } = frmDom.ajax;
	const { onClickPreventDefault } = frmDom.util;

	/**
	 * FrmFormTemplates class.
	 * Manages behaviors for the "Form Templates" page.
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
		 * Id for empty state.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static EMPTY_STATE_ID = 'frm-form-templates-empty-state';

		/**
		 * Class added to an element to mark it as hidden.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static HIDDEN_CLASS = 'frm_hidden';

		/**
		 * Class added to an element to mark it as the current item.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static CURRENT_CLASS = 'frm-current';

		/**
		 * Class constant for the favorite heart icon with background.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static FILLED_HEART_ICON = '#frm_heart_solid_icon';

		/**
		 * Class constant for the default linear heart icon.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static LINEAR_HEART_ICON = '#frm_heart_icon';

		/**
		 * All Templates category slug.
		 *
		 * @since x.x
		 * @type {string}
		 */
		static ALL_TEMPLATES_SLUG = 'all-templates';

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
			// Initialize DOM elements and other properties
			this.initProperties();

			// Prepare the initial UI components
			this.prepareUI();

			// Set up the initial state, including any required DOM manipulations
			this.setupInitialView();

			// Create a categorized list of templates
			this.buildCategorizedTemplates();

			// Attach event listeners for user interactions
			this.addEventListeners();
		}

		/**
		 * Prepares the UI by creating and appending necessary elements.
		 *
		 * @since x.x
		 */
		prepareUI() {
			// Create and append Empty State to the bodyContent element
			this.createEmptyState();
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
			 * Twin Featured Template Items within the templatesList.
			 *
			 * @since x.x
			 * @type {NodeList}
			 */
			this.twinFeaturedTemplateItems = this.templatesList?.querySelectorAll( `.${this.constructor.FEATURED_TEMPLATE_CLASS}` );

			/**
			 * Search Input element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.searchInput = document.querySelector( '#template-search-input' );

			/**
			 * Indicates whether the search input element has text or not.
			 *
			 * @since x.x
			 * @type {Boolean}
			 */
			this.notEmptySearchText = false;

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
			 * The currently Selected Category. Defaults to ALL_TEMPLATES_SLUG.
			 *
			 * @since x.x
			 * @type {string}
			 */
			this.selectedCategory = this.constructor.ALL_TEMPLATES_SLUG;

			/**
			 * The currently Selected Category element. Defaults to 'All Templates' category element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.selectedCategoryEl = document.querySelector( `.${this.constructor.CATEGORY_CLASS}[data-category="all-templates"]` );

			/**
			 * All Templates Category element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.allTemplatesCategory = this.selectedCategoryEl;

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

			/**
			 * Empty State element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.emptyState = document.querySelector( `#${this.constructor.EMPTY_STATE_ID}` );

			/**
			 * Empty State Title element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.emptyStateTitle = this.emptyState.querySelector( `.${this.constructor.EMPTY_STATE_ID}-title` );

			/**
			 * Empty State Text element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.emptyStateText = this.emptyState.querySelector( `.${this.constructor.EMPTY_STATE_ID}-text` );

			/**
			 * Empty State Button element.
			 *
			 * @since x.x
			 * @type {HTMLElement}
			 */
			this.emptyStateButton = this.emptyState.querySelector( `.${this.constructor.EMPTY_STATE_ID}-button` );
		}

		/**
		 * Sets up the initial state of the UI, including any DOM manipulations
		 * required for the correct presentation of elements.
		 *
		 * @note This should run after initProperties and before any template rendering.
		 *
		 * @since x.x
		 */
		setupInitialView() {
			// Clear the Search Input value
			this.searchInput.value = '';

			// Hide the twin featured template items
			this.hideElements( this.twinFeaturedTemplateItems );

			// Show the main body content and smoothly display the updated UI elements
			this.show( this.bodyContent );
			this.fadeIn( this.bodyContent );
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

			/**
			 * Attach input, search, and change event listeners to the Search Input
			 *
			 * @see frmDom.search method
			 *
			 * @param {HTMLElement} input The search input element.
			 * @param {string} targetClassName The CSS class that all target items has it for search.
			 */
			search.init( this.searchInput, this.constructor.TEMPLATE_CLASS, { handleSearchResult: this.handleSearchDisplay });
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

			// Clears the search input
			if ( this.notEmptySearchText ) {
				this.clearSearchInput( this.searchInput );
			}
		}

		/**
		 * Updates the main body content based on the selected category.
		 *
		 * @since x.x
		 */
		updateBodyContent() {
			// Update the displayed category title
			this.updatePageTitle();

			// Display templates based on selected category
			this.isAllTemplatesCategory( this.selectedCategory ) ? this.displayAllTemplates() : this.displayCategoryTemplates();

			// Smoothly display the updated UI elements
			this.fadeIn( this.bodyContent );
		}

		/**
		 * Updates the page title based on the selected category or a provided title.
		 *
		 * @since x.x
		 * @param {string} [title] Optional title to set. If not provided, the title will be set based on the selected category.
		 */
		updatePageTitle( title ) {
			const newTitle = title || this.selectedCategoryEl.querySelector( '.frm-form-templates-cat-text' ).textContent;
			this.pageTitle.textContent = newTitle;
		}

		/**
		 * Displays all templates when 'All Templates' is the selected category.
		 *
		 * @since x.x
		 */
		displayAllTemplates() {
			this.showElements([ ...this.bodyContentChildren, ...this.templateItems ]);
			this.hideElements([ this.customTemplatesSection, ...this.twinFeaturedTemplateItems ]);
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
			this.showElements([ this.templatesList, ...this.categorizedTemplates[this.selectedCategory] ]);
		}

		/**
		 * Handles the click event on the add to favorite button.
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
				heartSVGIcon.setAttribute( 'xlink:href', this.constructor.FILLED_HEART_ICON );
				twinTemplateHeartSVGIcon?.setAttribute( 'xlink:href', this.constructor.FILLED_HEART_ICON );
			} else {
				// Decrement the total favorite count
				--favoritesCount.total;
				// Decrement custom or default favorites count based on template type
				isCustomTemplate ? --favoritesCount.custom : --favoritesCount.default;
				// Update heart icon to outline (non-favorited state)
				heartSVGIcon.setAttribute( 'xlink:href', this.constructor.LINEAR_HEART_ICON );
				twinTemplateHeartSVGIcon?.setAttribute( 'xlink:href', this.constructor.LINEAR_HEART_ICON );
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
		 * Updates UI based on search results and input value.
		 *
		 * @since x.x
		 *
		 * @param {Object} args The arguments object.
		 * @param {boolean} args.foundSomething True if at least one item is found in the search.
		 * @param {boolean} args.notEmptySearchText True if the search input has a value, otherwise false.
		 */
		handleSearchDisplay = ({ foundSomething, notEmptySearchText }) => {
			// Update class property to manage the state of search input across the class.
			this.notEmptySearchText = notEmptySearchText;

			// If the search input and the selected category are empty, revert to default 'All Templates'
			if ( ! this.notEmptySearchText && ! this.selectedCategory ) {
				// Dispatch the input event manually
				this.allTemplatesCategory.dispatchEvent( new Event( 'click', { 'bubbles': true }) );
				return;
			}

			// If no templates are found, show the empty state
			if ( ! foundSomething ) {
				this.displaySearchEmptyState();
				return;
			}

			// Hide the empty state if showing
			this.hide( this.emptyState );

			// If a category is currently selected, transition to displaying search results
			if ( this.selectedCategory ) {
				// Transition the UI to show the search results
				this.displaySearchResults();

				/**
				 * Clear the selectedCategory to signify we've transitioned to displaying search results.
				 * This acts as a flag to determine that we are in a search result state,
				 * thereby preventing re-running of displaySearchResults when the search text changes.
				 */
				this.selectedCategory = '';
			}
		}

		/**
		 * Updates the UI to display the search results.
		 *
		 * @since x.x
		 */
		displaySearchResults = () => {
			// Remove highlighting from the currently selected category
			this.selectedCategoryEl.classList.remove( this.constructor.CURRENT_CLASS );

			// Hide non-relevant elements in the body content
			this.hideElements( this.bodyContentChildren );

			// Update the page title and display relevant elements
			this.updatePageTitle( __( 'Search Result', 'formidable' ) );
			this.showElements([ this.pageTitle, this.templatesList, ...this.templateItems ]);

			// Smoothly display the updated UI elements
			this.fadeIn( this.bodyContent );
		}

		/**
		 * Displays the empty state UI.
		 * Used when no search results are found.
		 *
		 * @since x.x
		 */
		displaySearchEmptyState = () => {
			if ( 'search' === this.emptyState.dataset?.search ) {
				return;
			}

			// Set the Empty State elements text content
			this.emptyStateTitle.textContent = __( 'No results found', 'formidable' );
			this.emptyStateText.textContent = __( 'Sorry, we didn\'t find any templates that match your criteria.', 'formidable' );
			this.emptyStateButton.textContent = __( 'Start from scratch', 'formidable' );

			// Add id to the Empty State Button
			this.emptyStateButton.setAttribute( 'id', 'frm-search-empty-state-button' );

			this.emptyState.setAttribute( 'data-state', 'search' );

			// Show the empty state UI element
			this.show( this.emptyState );
		}

		/**
		 * Clears the search input and triggers the input event manually.
		 *
		 * @since x.x
		 * @param {HTMLInputElement} searchInput The input element to be cleared.
		 */
		clearSearchInput( searchInput ) {
			// Clear the value
			searchInput.value = '';

			// Dispatch the input event manually
			searchInput.dispatchEvent( new Event( 'input', { 'bubbles': true }) );
		}

		/**
		 * Create and append Empty State element to the Body Content element.
		 *
		 * @since x.x
		 */
		createEmptyState() {
			// Setup the button element
			const button = a({
				class: 'button button-primary frm-button-primary',
				href: '#'
			});
			button.setAttribute( 'role', 'button' );

			// Create the Empty State element
			const emptyState = div({
				id: this.constructor.EMPTY_STATE_ID,
				children: [
					img({ src: `${pluginURL}/images/form-templates/empty-state.svg`, alt: __( 'Empty State', 'formidable' ) }),
					tag( 'h3', {
						class: 'frm-form-templates-title'
					}),
					span({
						class: 'frm-form-templates-text'
					}),
					button
				]
			});

			// Append the Empty State to the Body Content element
			this.bodyContent.appendChild( emptyState );
		}

		/**
		 * Checks if the category is "All Templates".
		 *
		 * @since x.x
		 * @param {string} category The category slug.
		 * @returns {boolean} True if the category is "All Templates", otherwise false.
		 */
		isAllTemplatesCategory( category ) {
			return this.constructor.ALL_TEMPLATES_SLUG === category;
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
		 * Checks if an element is visible.
		 *
		 * @since x.x
		 * @param {HTMLElement} element The HTML element to check for visibility.
		 * @returns {boolean} Returns true if the element is visible, otherwise false.
		 */
		isVisible( element ) {
			return element?.classList.contains( this.constructor.HIDDEN_CLASS );
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
