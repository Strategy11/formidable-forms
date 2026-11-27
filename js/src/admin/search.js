module.exports = function createSearch( checkContextualShortcode ) {
	function searchContent() {
		/*jshint validthis:true */
		let i;
		let regEx = false;
		let searchText = this.value.toLowerCase();
		const toSearch = this.getAttribute( 'data-tosearch' );
		const items = document.getElementsByClassName( toSearch );

		if ( this.tagName === 'SELECT' ) {
			searchText = selectedOptions( this );
			searchText = searchText.join( '|' ).toLowerCase();
			regEx = true;
		}

		const hideRepeaters = document.querySelector( 'select[name="format"]' )?.value === 'xml';
		for ( i = 0; i < items.length; i++ ) {
			const innerText = items[ i ].textContent.toLowerCase();

			const itemCanBeShown = ! ( hideRepeaters && items[ i ].classList.contains( 'frm-is-repeater' ) );
			if ( searchText === '' ) {
				if ( itemCanBeShown && checkContextualShortcode( items[ i ] ) ) {
					items[ i ].classList.remove( 'frm_hidden' );
				}
				items[ i ].classList.remove( 'frm-search-result' );
			} else if ( ( regEx && new RegExp( searchText ).test( innerText ) ) || innerText.includes( searchText ) || textMatchesPlural( innerText, searchText ) ) {
				if ( itemCanBeShown && checkContextualShortcode( items[ i ] ) ) {
					items[ i ].classList.remove( 'frm_hidden' );
				}
				items[ i ].classList.add( 'frm-search-result' );
			} else {
				items[ i ].classList.add( 'frm_hidden' );
				items[ i ].classList.remove( 'frm-search-result' );
			}
		}

		// Updates the visibility of category headings based on search results.
		updateCatHeadingVisibility();

		jQuery( this ).trigger( 'frmAfterSearch' );
	}

	/**
	 * Allow a search for "signatures" to still match "signature" for example when searching fields.
	 *
	 * @since 6.15
	 *
	 * @param {string} text       The text in the element we are checking for a match.
	 * @param {string} searchText The text value that is being searched.
	 * @return {boolean} True if the text matches when pluralized.
	 */
	function textMatchesPlural( text, searchText ) {
		if ( searchText === 's' ) {
			// Don't match everything when just "s" is searched.
			return false;
		}

		if ( text[ text.length - 1 ] === 's' ) {
			// Do not match something with double s if the text already ends in s.
			return false;
		}

		return `${ text }s`.includes( searchText );
	}

	/**
	 * Updates the visibility of category headings based on search results.
	 * If all associated fields are hidden (indicating no search matches),
	 * the heading is hidden.
	 *
	 * @since 6.4.1
	 */
	function updateCatHeadingVisibility() {
		const insertFieldsElement = document.querySelector( '#frm-insert-fields' );
		if ( ! insertFieldsElement ) {
			return;
		}

		const headingElements = insertFieldsElement.querySelectorAll( ':scope > .frm-with-line' );
		headingElements.forEach( heading => {
			const fieldsListElement = heading.nextElementSibling;
			if ( ! fieldsListElement ) {
				return;
			}
			const listItemElements = fieldsListElement.querySelectorAll( ':scope > li.frmbutton' );
			const allHidden = Array.from( listItemElements ).every( li => li.classList.contains( 'frm_hidden' ) );

			// Add or remove class based on `allHidden` condition
			heading.classList.toggle( 'frm_hidden', allHidden );
		} );
	}

	/* Helpers */

	function selectedOptions( select ) {
		let opt;
		const result = [];
		const options = select?.options;

		for ( let i = 0, iLen = options?.length; i < iLen; i++ ) {
			opt = options[ i ];

			if ( opt.selected ) {
				result.push( opt.value );
			}
		}
		return result;
	}

	return searchContent;
};
