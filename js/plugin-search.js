/**
 * Handles replacing the bottom row
 * of the card with customized content.
 */

/* global frmPlugSearch, JSON */

var FormidablePSH = {};

function frmPS() {
	FormidablePSH = {
		$pluginFilter: document.getElementById( 'plugin-filter' ),

		/**
		 * Get parent search hint element.
		 * @returns {Element | null}
		 */
		getCard: function () {
			return document.querySelector( '.plugin-card-frm-plugin-search' );
		},

		/**
		 * Replace bottom row of the card to insert logo, text and link to dismiss the card.
		 */
		replaceCardBottom: function () {
			var hint = FormidablePSH.getCard();
			if ( 'object' === typeof hint && null !== hint ) {
				hint.querySelector( '.plugin-card-bottom' ).outerHTML =
					'<div class="plugin-card-bottom frm-plugin-search__bottom">' +
					'<p class="frm-plugin-search__text">' +
					frmPlugSearch.legend +
					'</p>' +
					'</div>';

				// Remove link and parent li from action links and move it to bottom row
				var dismissLink = document.querySelector( '.frm-plugin-search__dismiss' );
				dismissLink.parentNode.parentNode.removeChild( dismissLink.parentNode );
				document.querySelector( '.frm-plugin-search__bottom' ).appendChild( dismissLink );
			}
		},


		/**
		 * Check if plugin card list nodes changed. If there's a Formidable PSH card, replace the title and the bottom row.
		 * @param {array} mutationsList
		 */
		replaceOnNewResults: function ( mutationsList ) {
			mutationsList.forEach( function ( mutation ) {
				if (
					'childList' === mutation.type &&
					1 === document.querySelectorAll( '.plugin-card-frm-plugin-search' ).length
				) {
					FormidablePSH.replaceCardBottom();
				}
			} );
		},

		/**
		 * Start suggesting.
		 */
		init: function () {
			if ( FormidablePSH.$pluginFilter === null ) {
				return;
			}

			// Replace PSH bottom row on page load
			FormidablePSH.replaceCardBottom();

			// Listen for changes in plugin search results
			var resultsObserver = new MutationObserver( FormidablePSH.replaceOnNewResults );
			resultsObserver.observe( FormidablePSH.$pluginFilter, { childList: true } );
		},
	};

	FormidablePSH.init();
}
FormidablePS = frmPS();
