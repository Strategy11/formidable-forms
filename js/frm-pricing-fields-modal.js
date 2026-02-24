/**
 * Pricing Fields Modal JavaScript
 *
 * @package Formidable
 */

( function( jQuery ) {
	'use strict';

	/**
	 * Show the pricing fields modal.
	 *
	 * @return {void}
	 */
	function showPricingFieldsModal() {
		const $modal = jQuery( '#frm-pricing-fields-modal' );
		if ( $modal.length === 0 ) {
			return;
		}

		// Initialize jQuery UI dialog if not already initialized
		if ( ! $modal.hasClass( 'ui-dialog-content' ) ) {
			$modal.dialog( {
				autoOpen: false,
				modal: true,
				width: 500,
				height: 'auto',
				resizable: false,
				draggable: false,
				position: { my: 'center', at: 'center', of: window },
				create: function() {
					// Move dialog to end of body to ensure proper stacking
					jQuery( '.ui-dialog' ).appendTo( 'body' );
				},
				open: function() {
					// Add overlay with gray background
					jQuery( '.ui-widget-overlay' ).css( {
						'z-index': 100001,
						'opacity': 0.7
					} );
					jQuery( '.ui-dialog' ).css( {
						'z-index': 100002,
						'background-color': 'white'
					} );

					// Prevent body scroll
					document.body.style.overflow = 'hidden';
				},
				close: function() {
					// Restore body scroll
					document.body.style.overflow = '';
				}
			} );
		}

		// Open the modal
		$modal.dialog( 'open' );
	}

	/**
	 * Hide the pricing fields modal.
	 *
	 * @return {void}
	 */
	function hidePricingFieldsModal() {
		const $modal = jQuery( '#frm-pricing-fields-modal' );
		if ( $modal.length ) {
			$modal.dialog( 'close' );
		}
	}

	// Initialize when DOM is ready.
	document.addEventListener( 'DOMContentLoaded', function() {
		// Check if modal HTML exists and show it.
		if ( document.getElementById( 'frm-pricing-fields-modal' ) ) {
			// Show modal immediately
			showPricingFieldsModal();
		}

		// Handle cancel button click.
		document.addEventListener( 'click', function( e ) {
			if ( e.target.classList.contains( 'frm-cancel-modal' ) ) {
				e.preventDefault();
				hidePricingFieldsModal();
			}
		} );

		// Handle escape key.
		document.addEventListener( 'keydown', function( e ) {
			if ( e.key === 'Escape' ) { // Escape key.
				hidePricingFieldsModal();
			}
		} );

		// Handle click outside modal to close.
		document.addEventListener( 'click', function( e ) {
			const $modal = jQuery( '#frm-pricing-fields-modal' );
			if ( $modal.length && ! $modal.is( e.target ) && $modal.has( e.target ).length === 0 ) {
				hidePricingFieldsModal();
			}
		} );
	} );

	} )( jQuery );
