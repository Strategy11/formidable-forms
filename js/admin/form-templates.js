( ( wp ) => {

	/**
	 * Globals: ajaxurl, fromDom
	 *
	 * @since x.x
	 */
	const { doJsonPost } = frmDom.ajax;

	/**
	 * Represents the FrmFormTemplates.
	 *
	 * @since x.x
	 *
	 * @class FrmFormTemplates
	 */
	class FrmFormTemplates {

		/**
		 * Class that is added to an element to mark it as hidden.
		 *
		 * @since x.x
		 *
		 * @type {string}
		 */
		static HIDDEN_STATE_CLASS = 'frm-form-templates-hidden';

		/**
		 * All template items class.
		 *
		 * @since x.x
		 *
		 * @type {string}
		 */
		static TEMPLATE_CLASS = 'frm-form-templates-item';

		/**
		 * Initializes the FrmFormTemplates instance.
		 *
		 * @since x.x
		 *
		 * @constructor
		 */
		constructor() {
			/**
			 * Action hook that fires before the FrmFormTemplates class initialization.
			 *
			 * @since x.x
			 *
			 * @hook frmFormTemplates.beforeInitialize
			 */
			wp.hooks.doAction( 'frmFormTemplates.beforeInitialize', this );

			// Call initialize method.
			this.initialize();

			/**
			 * Action hook that fires after the FrmFormTemplates class initialization.
			 *
			 * @since x.x
			 *
			 * @hook frmFormTemplates.afterInitialize
			 */
			wp.hooks.doAction( 'frmFormTemplates.afterInitialize', this );
		}

		/**
		 * Main initialization method.
		 *
		 * @since x.x
		 */
		initialize() {
			// Initialize DOM elements and other properties needed for this class
			this.initProperties();

			// Create categorized templates to facilitate template selection
			this.buildCategorizedTemplates();

			// Attach relevant event listeners for user interactions
			this.addEventListeners();
		}

		/**
		 * Initializes properties for the FrmFormTemplates instance.
		 *
		 * @since x.x
		 */
		initProperties() {
			/**
			 * Create Form button element.
			 *
			 * @since x.x
			 *
			 * @type {HTMLElement}
			 */
			this.createFormButton = document.querySelector( '#frm-form-templates-create-form' );

			/**
			 * Page Title element.
			 *
			 * @since x.x
			 *
			 * @type {HTMLElement}
			 */
			this.pageTitle = document.querySelector( '#frm-form-templates-page-title' );

			/**
			 * Featured Templates List container.
			 *
			 * @since x.x
			 *
			 * @type {HTMLElement}
			 */
			this.featuredTemplatesList = document.querySelector( '#frm-form-templates-featured-list' );

			/**
			 * Upsell Banner container.
			 *
			 * @since x.x
			 *
			 * @type {HTMLElement}
			 */
			this.upsellBanner = document.querySelector( '#frm-form-templates-upsell-banner' );

			/**
			 * Templates List container.
			 *
			 * @since x.x
			 *
			 * @type {HTMLElement}
			 */
			this.templatesList = document.querySelector( '#frm-form-templates-list' );

			/**
			 * All Template Items.
			 *
			 * @since x.x
			 *
			 * @type {NodeList}
			 */
			this.templateItems = this.templatesList?.querySelectorAll( `.${this.constructor.TEMPLATE_CLASS}` );

			/**
			 * All Featured Template Items that are nested within the templatesList.
			 *
			 * @since x.x
			 *
			 * @type {NodeList}
			 */
			this.nestedFeaturedTemplateItems = this.templatesList?.querySelectorAll( '.frm-form-templates-featured-item' );

			/**
			 * Custom Templates List container.
			 *
			 * @since x.x
			 *
			 * @type {HTMLElement}
			 */
			this.customTemplatesList = document.querySelector( '#frm-form-templates-custom-list' );

			/**
			 * All Custom Template Items.
			 *
			 * @since x.x
			 *
			 * @type {NodeList}
			 */
			this.customTemplateItems = this.customTemplatesList?.querySelectorAll( `.${this.constructor.TEMPLATE_CLASS}` );

			/**
			 * Custom Templates List Section element.
			 *
			 * @since x.x
			 *
			 * @type {HTMLElement}
			 */
			this.customTemplatesSection = document.querySelector( '#frm-form-templates-custom-list-section' );

			/**
			 * Custom Templates List Title element.
			 *
			 * @since x.x
			 *
			 * @type {HTMLElement}
			 */
			this.customTemplatesTitle = document.querySelector( '#frm-form-templates-custom-list-title' );

			/**
			 * Body Content element.
			 *
			 * @since x.x
			 *
			 * @type {HTMLElement}
			 */
			this.bodyContent = document.querySelector( '#post-body-content' );

			/**
			 * Body Content Children.
			 *
			 * @since x.x
			 *
			 * @type {HTMLElement}
			 */
			this.bodyContentChildren = Array.from( this.bodyContent?.children );

			/**
			 * Object to hold Categorized Templates.
			 *
			 * Keys will be category names and values will be arrays of corresponding templates.
			 *
			 * @since x.x
			 *
			 * @type {Object}
			 */
			this.categorizedTemplates = {};

			/**
			 * The currently Selected Category. Defaults to 'all-templates'.
			 *
			 * @since x.x
			 *
			 * @type {string}
			 */
			this.selectedCategory = 'all-templates';

			/**
			 * The currently Selected Category element. Defaults to 'All Templates' category element.
			 *
			 * @since x.x
			 *
			 * @type {HTMLElement}
			 */
			this.selectedCategoryEl = document.querySelector( '.frm-form-templates-cat-item[data-category="all-templates"]' );

			/**
			 * Favortes Category element.
			 *
			 * @since x.x
			 *
			 * @type {HTMLElement}
			 */
			this.favoritesCategory = document.querySelector( '.frm-form-templates-cat-item[data-category="favorites"]' );

			/**
			 * Favortes Category Count element.
			 *
			 * @since x.x
			 *
			 * @type {HTMLElement}
			 */
			this.favoritesCategoryCount = this.favoritesCategory?.querySelector( '.frm-form-templates-cat-count' );
		}

		/**
		 * Builds a mapping between categories and corresponding templates.
		 *
		 * @since x.x
		 */
		buildCategorizedTemplates() {
			this.templateItems.forEach( template => {
				const categories = template.getAttribute( 'data-categories' ).split( ',' );

				categories.forEach( category => {
					if ( ! this.categorizedTemplates[category]) {
						this.categorizedTemplates[category] = [];
					}
					this.categorizedTemplates[category].push( template );
				});
			});
		}

		/**
		 * Adds click event listeners.
		 *
		 * @since x.x
		 */
		addEventListeners() {
			// Add click event listener for sidebar categories
			const categoryItems = document.querySelectorAll( '.frm-form-templates-cat-item' );
			categoryItems.forEach( category => {
				category.addEventListener( 'click', this.onCategoryClick );
			});

			// Add click event listener for add to favorite button
			const favoriteButtons = document.querySelectorAll( '.frm-form-templates-item-favorite-button' );
			favoriteButtons.forEach( favoriteButton => {
				favoriteButton.addEventListener( 'click', this.onFavoriteButtonClick );
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
			this.selectedCategoryEl.classList.remove( 'frm-current' );

			// Update the selected category element and add the 'frm-current' class to it
			this.selectedCategoryEl = clickedCategory;
			this.selectedCategoryEl.classList.add( 'frm-current' );

			// Update the main body content based on the clicked category
			this.updateBodyContent();
		}

		/**
		 * Modifies the content of the main body area based on the category selected by the user.
		 *
		 * @since x.x
		 */
		updateBodyContent() {
			const {
				selectedCategory,
				selectedCategoryEl,
				pageTitle,
				templatesList,
				templateItems,
				nestedFeaturedTemplateItems,
				customTemplatesList,
				customTemplateItems,
				customTemplatesSection,
				customTemplatesTitle,
				categorizedTemplates,
				bodyContent
			} = this;

			// Change the page title text
			const categoryText = selectedCategoryEl.querySelector( '.frm-form-templates-cat-text' ).textContent;
			pageTitle.textContent = categoryText;

			// Conditionally reveal or hide templates based on selected category
			if ( this.isAllTemplatesCategory( selectedCategory ) ) {
				this.showBodyContentChildren();
				this.hide( customTemplatesSection );

				this.showElements( templateItems );
				this.hideElements( nestedFeaturedTemplateItems );
			} else {
				this.hideBodyContentChildren();
				this.hideElements( templateItems );
				this.show( pageTitle );

				if ( 'favorites' === selectedCategory ) {
					const favoriteItems = bodyContent.querySelectorAll( '.frm-form-templates-favorite-item' );

					this.hideElements( customTemplateItems );
					this.show( templatesList );
					this.showElements( favoriteItems );

					const hasCustomTemplates = Array.from( favoriteItems ).some( template => template.dataset.custom );
					if ( hasCustomTemplates ) {
						this.showElements([ customTemplatesSection, customTemplatesList ]);

						const isTemplatesListVisisble = !! templatesList.offsetHeight;
						if ( isTemplatesListVisisble ) {
							this.show( customTemplatesTitle );
						}
					}
				} else if ( 'custom' === selectedCategory ) {
					this.showElements([ customTemplatesSection, customTemplatesList, ...customTemplateItems ]);
				} else {
					this.show( templatesList );
					this.showElements( categorizedTemplates[selectedCategory] || []);
				}
			}

			// Fade-in the body content
			this.fadeIn( bodyContent );
		}

		/**
		 * Handles the click event on the add to favorite button.
		 *
		 * @since x.x
		 *
		 * @param {Event} event The click event object.
		 */
		onFavoriteButtonClick = ( event ) => {
			event.preventDefault();

			const favoriteButton = event.currentTarget;

			// Check if the button is "disabled"
			if ( favoriteButton.getAttribute( 'data-disabled' ) === 'true' ) {
				return;
			}

			// "Disable" the button to prevent multiple clicks
			favoriteButton.setAttribute( 'data-disabled', 'true' );

			const template = favoriteButton.closest( `.${this.constructor.TEMPLATE_CLASS}` );
			const templateId = template.dataset.id;
			console.log( templateId );

			// Determine if the item is currently favorited
			const isFavorited = template.classList.contains( 'frm-form-templates-favorite-item' );
			const operation = isFavorited ? 'remove' : 'add';

			// Send request to server
			const formData = new FormData();
			formData.append( 'template_id', templateId );
			formData.append( 'operation', operation );

			doJsonPost( 'add_or_remove_favorite_template', formData )
				.finally( () => {
					// "Re-enable" the button
					favoriteButton.setAttribute( 'data-disabled', 'false' );

					// Toggle favorite status in UI
					template.classList.toggle( 'frm-form-templates-favorite-item', ! isFavorited );

					const favoriteItems = this.bodyContent.querySelectorAll( '.frm-form-templates-favorite-item' );
					console.log( favoriteItems );
					const favoriteItemsCount = favoriteItems.length;
					console.log( favoriteItemsCount );
					this.favoritesCategoryCount.textContent = favoriteItemsCount;

					if ( 'favorites' === this.selectedCategory ) {
						this.hide( template );

						const hasCustomTemplates = Array.from( favoriteItems ).some( template => template.dataset.custom );
						if ( ! hasCustomTemplates ) {
							this.hide( this.customTemplatesTitle );
						}
					}
				});
		}

		/**
		 * Check if the category is "All Templates".
		 *
		 * @since x.x
		 *
		 * @param {string} category
		 * @returns {boolean}
		 */
		isAllTemplatesCategory( category ) {
			return 'all-templates' === category;
		}

		/**
		 * Show all child elements of the Body Content element.
		 *
		 * @since x.x
		 */
		showBodyContentChildren() {
			this.bodyContentChildren?.forEach( ( child ) => {
				this.show( child );
			});
		}

		/**
		 * Hide all child elements of the Body Content element.
		 *
		 * @since x.x
		 */
		hideBodyContentChildren() {
			const {
				bodyContentChildren,
				customTemplatesTitle,
				customTemplatesList
			} = this;

			[ ...bodyContentChildren, customTemplatesTitle, customTemplatesList ]?.forEach( ( child ) => {
				this.hide( child );
			});
		}

		/**
		 * Reveals specified elements by removing the hidden state class.
		 *
		 * @since x.x
		 *
		 * @param {Array<Element>} elements - List of elements to be displayed.
		 */
		showElements( elements ) {
			elements?.forEach( element => this.show( element ) );
		}

		/**
		 * Conceals specified elements by adding the hidden state class.
		 *
		 * @since x.x
		 *
		 * @param {Array<Element>} elements - List of elements to be hidden.
		 */
		hideElements( elements ) {
			elements?.forEach( element => this.hide( element ) );
		}

		/**
		 * Adds the class to show the element.
		 *
		 * @since x.x
		 *
		 * @param {Element} element - The element to show.
		 */
		show( element ) {
			element?.classList.remove( this.constructor.HIDDEN_STATE_CLASS );
		}

		/**
		 * Adds the class to hide the element.
		 *
		 * @since x.x
		 *
		 * @param {Element} element - The element to hide.
		 */
		hide( element ) {
			element?.classList.add( this.constructor.HIDDEN_STATE_CLASS );
		}

		/**
		 * Adds the fade-in animation class to the element.
		 *
		 * @since x.x
		 *
		 * @param {HTMLElement} element The element to which the fade-in class is added.
		 * @param {string} [fadingClass='frm-form-templates-flex'] CSS class to apply during fading
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

	// Initializing the class
	document.addEventListener( 'DOMContentLoaded', () => {
		new FrmFormTemplates();
	});
})( window.wp );
