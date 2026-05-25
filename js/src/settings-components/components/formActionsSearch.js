/**
 * Form actions search behavior for the Actions & Notifications settings page.
 *
 * @since x.x
 */

const ACTIONS_LIST_WRAPPER_ID = 'frm_email_addon_menu';
const FILTER_CONTENT_ID = 'frm-actions-filter-content';

/**
 * Initializes search behavior for the form actions settings page.
 */
export const initFormActionsSearch = () => {
	const actionsListWrapper = document.getElementById( ACTIONS_LIST_WRAPPER_ID );
	if ( ! actionsListWrapper ) {
		return;
	}

	const searchInput = actionsListWrapper.querySelector( '.frm-auto-search' );
	if ( ! searchInput ) {
		return;
	}

	const filterContent = document.getElementById( FILTER_CONTENT_ID );
	const onSearch = () => handleSearchInput( searchInput, actionsListWrapper, filterContent );

	searchInput.addEventListener( 'input', onSearch );
	searchInput.addEventListener( 'search', onSearch );
};

/**
 * Handles search input for form actions.
 * Switches to "All" tab and defers group heading visibility update.
 *
 * @since x.x
 *
 * @param {HTMLInputElement} searchInput        The search input element.
 * @param {HTMLElement}      actionsListWrapper The actions wrapper container.
 * @param {HTMLElement|null} filterContent      The filter content container.
 */
const handleSearchInput = ( searchInput, actionsListWrapper, filterContent ) => {
	if ( searchInput.value.trim() ) {
		switchToAllTab( actionsListWrapper );
	}

	if ( filterContent ) {
		// Run after searchContent in admin.js finishes toggling item visibility.
		requestAnimationFrame( () => updateGroupHeadingVisibility( filterContent ) );
	}
};

/**
 * Switches the active filter tab to "All" so search spans every category.
 *
 * @param {HTMLElement} actionsListWrapper The actions wrapper container.
 */
const switchToAllTab = actionsListWrapper => {
	const allTab = actionsListWrapper.querySelector( 'li[data-filter="all"]' );
	if ( ! allTab.classList.contains( 'frm-active' ) ) {
		allTab.click();
	}
};

/**
 * Toggles group headings visibility when all their actions are hidden by search.
 *
 * @param {HTMLElement} filterContent The filter content container.
 */
const updateGroupHeadingVisibility = filterContent => {
	filterContent.querySelectorAll( '[data-group]' ).forEach( group => {
		const heading = group.querySelector( '.frm-group-heading' );
		if ( ! heading ) {
			return;
		}

		const actions = group.querySelectorAll( '.frm-action' );
		const allHidden = actions.length > 0 && Array.from( actions ).every(
			action => action.classList.contains( 'frm_hidden' )
		);
		heading.classList.toggle( 'frm-force-hidden', allHidden );
	} );
};
