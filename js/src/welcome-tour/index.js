/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

domReady( () => {
	injectWelcomeTourChecklist();
} );

function injectWelcomeTourChecklist() {
	const formidableMenuItem = document.getElementById( 'toplevel_page_formidable' );
	if ( ! formidableMenuItem ) {
		return;
	}

	const subMenu = formidableMenuItem.querySelector( 'ul' );
	if ( ! subMenu ) {
		return;
	}

	const bar       = document.createElement( 'span' );
	bar.className   = 'frm-progress-bar';
	bar.style.width = getProgressBarPercent() + '%';

	const background     = document.createElement( 'span' );
	background.className = 'frm-progress-bar-background';
	background.appendChild( bar );

	const span = document.createElement( 'span' );
	span.id    = 'frm_welcome_tour_progress';
	span.appendChild( background );

	const newLi = document.createElement( 'li' );
	newLi.style.backgroundColor = 'rgba(44, 51, 56, 1)';

	const welcomeTourAnchor = document.createElement( 'a' );
	welcomeTourAnchor.style.padding = '5px 12px';
	welcomeTourAnchor.href = '#';
	welcomeTourAnchor.textContent = frmWelcomeTourVars.i18n.CHECKLIST_TEXT;
	welcomeTourAnchor.appendChild( span );

	newLi.appendChild( welcomeTourAnchor );

	if ( subMenu.closest( '.wp-not-current-submenu' ) ) {
		const newUl = document.createElement( 'ul' );
		newUl.appendChild( newLi );
		formidableMenuItem.appendChild( newUl );
	} else {
		subMenu.insertBefore( newLi, subMenu.firstChild );
	}
}

function getProgressBarPercent() {
	return frmWelcomeTourVars.PROGRESS_BAR_PERCENT;
}
