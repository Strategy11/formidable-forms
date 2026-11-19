/* global frmAdminBuild */

/**
 * Lite's original page and section collapse code, for Pro versions that do not register their own.
 * The code was moved here from admin.js.
 *
 * @since x.x
 */

// The fake field works differently than real fields.
function maybeCollapsePage() {
	/*jshint validthis:true */
	const field = jQuery( this ).closest( '.frm_field_box[data-ftype=break]' );
	if ( field.length ) {
		toggleCollapsePage( field );
	} else {
		toggleCollapseFakePage();
	}
}

// Find all fields in a page and hide/show them
function toggleCollapsePage( field ) {
	const toCollapse = getAllFieldsForPage( field.get( 0 ).parentNode.closest( 'li.frm_field_box' ).nextElementSibling );
	togglePage( field, toCollapse );
}

function toggleCollapseFakePage() {
	const topLevel = document.getElementById( 'frm-fake-page' );
	const firstField = document.getElementById( 'frm-show-fields' ).firstElementChild;
	const toCollapse = getAllFieldsForPage( firstField );

	if ( firstField.getAttribute( 'data-ftype' ) === 'break' ) {
		// Don't collapse if the first field is a page break.
		return;
	}

	togglePage( jQuery( topLevel ), toCollapse );
}

function getAllFieldsForPage( firstWrapper ) {
	let $fieldsForPage;
	let currentWrapper;

	$fieldsForPage = jQuery();

	if ( null === firstWrapper ) {
		return $fieldsForPage;
	}

	currentWrapper = firstWrapper;

	do {
		if ( null !== currentWrapper.querySelector( '.edit_field_type_break' ) ) {
			break;
		}
		$fieldsForPage = $fieldsForPage.add( jQuery( currentWrapper ) );
		currentWrapper = currentWrapper.nextElementSibling;
	} while ( null !== currentWrapper );

	return $fieldsForPage;
}

function togglePage( field, toCollapse ) {
	let i;
	const fieldCount = toCollapse.length;
	const slide = Math.min( fieldCount, 3 );

	if ( field.hasClass( 'frm-page-collapsed' ) ) {
		field.removeClass( 'frm-page-collapsed' );
		toCollapse.removeClass( 'frm-is-collapsed' );
		for ( i = 0; i < slide; i++ ) {
			if ( i === slide - 1 ) {
				jQuery( toCollapse[ i ] ).slideDown( 150, function() {
					toCollapse.show();
				} );
			} else {
				jQuery( toCollapse[ i ] ).slideDown( 150 );
			}
		}
	} else {
		field.addClass( 'frm-page-collapsed' );
		toCollapse.addClass( 'frm-is-collapsed' );
		for ( i = 0; i < slide; i++ ) {
			if ( i === slide - 1 ) {
				jQuery( toCollapse[ i ] ).slideUp( 150, function() {
					toCollapse.css( 'cssText', 'display:none !important;' );
				} );
			} else {
				jQuery( toCollapse[ i ] ).slideUp( 150 );
			}
		}
	}
}

function maybeCollapseSection() {
	/*jshint validthis:true */
	const parentCont = this.parentNode.parentNode.parentNode.parentNode;

	parentCont.classList.toggle( 'frm-section-collapsed' );
}

function changeSectionStyle( e ) {
	const collapsedSection = e.target.closest( '.frm-section-collapsed' );
	if ( ! collapsedSection ) {
		return;
	}

	if ( e.type === 'show' ) {
		collapsedSection.style.zIndex = 3;
	} else {
		collapsedSection.style.zIndex = 1;
	}
}

function toggleSectionHolder() {
	document.querySelectorAll( '.start_divider' ).forEach(
		function( divider ) {
			toggleOneSectionHolder( jQuery( divider ) );
		}
	);
}

function toggleOneSectionHolder( $section ) {
	if ( ! $section.length ) {
		return;
	}

	const $rows = $section.find( 'ul.frm_sorting' );
	let sectionHasFields = false;
	const { length } = $rows;
	for ( let index = 0; index < length; ++index ) {
		if ( 0 !== frmAdminBuild.getFieldsInRow( jQuery( $rows.get( index ) ) ).length ) {
			sectionHasFields = true;
			break;
		}
	}

	const noSectionFields = $section.parent().children( '.frm_no_section_fields' ).get( 0 );
	noSectionFields.classList.toggle( 'frm_block', ! sectionHasFields );
}

/**
 * @since x.x
 * @return {void}
 */
export function registerPageSectionChrome() {
	const registered = frmAdminBuild.registerBuilderFeature( 'page_section_chrome', {
		maybeCollapsePage,
		toggleCollapsePage,
		toggleCollapseFakePage,
		getAllFieldsForPage,
		togglePage,
		maybeCollapseSection,
		changeSectionStyle,
		toggleSectionHolder,
		toggleOneSectionHolder,
	} );
	if ( ! registered ) {
		return;
	}

	jQuery( document ).on( 'click', '#frm_form_editor_container .frm-collapse-page', maybeCollapsePage );
	jQuery( document ).on( 'click', '#frm_form_editor_container .frm-collapse-section', maybeCollapseSection );
	jQuery( document ).on( 'show.bs.dropdown hide.bs.dropdown', '#frm_form_editor_container', changeSectionStyle );
}
