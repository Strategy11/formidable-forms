module.exports = function createUpgradeDialogs( { maybeAddFieldSelection } ) {
	const { __, sprintf } = wp.i18n;
	const { div, img, span } = frmDom;

	function initUpgradeModal() {
		const upgradePopup = require( './upgrade-popup' );
		upgradePopup.initUpgradeModal();
	}

	function addOneClick( element, type, upgradeLabel ) {
		const upgradePopup = require( './upgrade-popup' );
		upgradePopup.addOneClick( element, type, upgradeLabel );
	}

	/**
	 * Opens a basic modal with the given title and content.
	 *
	 * @param {Event} event The event object.
	 * @return {void}
	 */
	function showBasicModal( event ) {
		const button = event.target.dataset?.modalTitle ? event.target : event.target.closest( '[data-modal-title]' );
		if ( ! button ) {
			return;
		}

		const { modalTitle, modalContent } = button.dataset;
		if ( ! modalTitle || ! modalContent ) {
			return;
		}

		event.preventDefault();

		frmDom.modal.maybeCreateModal(
			'frmBasicModal',
			{
				title: modalTitle,
				content: div( {
					className: 'inside',
					child: span( modalContent ),
				} ),
			}
		);
	}

	function getRequiredLicenseFromTrigger( element ) {
		if ( element.dataset.requires ) {
			return element.dataset.requires;
		}
		return 'Pro';
	}

	function populateUpgradeTab( element ) {
		const title = element.dataset.upgrade;

		const tab = element.getAttribute( 'href' ).replace( '#', '' );
		const container = document.querySelector( `.frm_${ tab }` ) || document.querySelector( `.${ tab }` );

		if ( ! container ) {
			return;
		}

		if ( container.querySelector( '.frm-upgrade-message' ) ) {
			// Tab has already been populated.
			return;
		}

		const h2 = container.querySelector( 'h2' );
		h2.style.borderBottom = 'none';

		/* translators: %s: Form Setting section name (ie Form Permissions, Form Scheduling). */
		h2.textContent = sprintf( __( '%s are not installed', 'formidable' ), title );

		container.classList.add( 'frmcenter' );

		const upgradeModal = document.getElementById( 'frm_upgrade_modal' );

		// The message explains the heading, so it reads before the call to action, the same way it does in the modal.
		appendClonedModalElementToContainer( 'frm-upgrade-message' );
		appendClonedModalElementToContainer( 'frm-oneclick' );
		appendClonedModalElementToContainer( 'frm-addon-status' );

		// Borrow the call to action from the Upgrade upgradeModal which should exist on the settings page (it is still used for other upgrades including Actions).
		const upgradeModalLink = upgradeModal.querySelector( '.frm-upgrade-link' );
		if ( upgradeModalLink ) {
			let upgradeButton;
			let upgradeActions = upgradeModalLink.closest( '.frm-upgrade-modal-actions' );
			if ( upgradeActions ) {
				upgradeActions = upgradeActions.cloneNode( true );
				upgradeButton = upgradeActions.querySelector( '.frm-upgrade-link' );
			} else {
				upgradeButton = upgradeModalLink.cloneNode( true );
			}
			const level = upgradeButton.querySelector( '.license-level' );
			if ( level ) {
				level.textContent = getRequiredLicenseFromTrigger( element );
			}
			if ( element.dataset.gradientUpgrade ) {
				upgradeButton.classList.add( 'frm-gradient' );
			}
			container.append( upgradeActions || upgradeButton );

			// Maybe append the secondary "Already purchased?" link from the upgradeModal as well.
			if ( upgradeModalLink.nextElementSibling?.querySelector( '.frm-link-secondary' ) ) {
				container.append( upgradeModalLink.nextElementSibling.cloneNode( true ) );
			}

			appendClonedModalElementToContainer( 'frm-oneclick-button' );
		}

		let upgradeLabel = element.dataset.message;

		if ( upgradeLabel === undefined ) {
			upgradeLabel = element.dataset.upgrade;
		}
		addOneClick( element, 'tab', upgradeLabel );

		if ( element.dataset.upsellImages ) {
			container.append( getUpsellImagesWrapper( element.dataset.upsellImages, title ) );
		} else if ( element.dataset.screenshot ) {
			container.append( getScreenshotWrapper( element.dataset.screenshot ) );
		}

		function appendClonedModalElementToContainer( className ) {
			container.append( upgradeModal.querySelector( `.${ className }` ).cloneNode( true ) );
		}
	}

	/**
	 * Build the previews shown below an upgrade tab's call to action.
	 *
	 * Unlike a screenshot, these images carry their own framing, so they get no
	 * browser chrome around them. The first one takes a row to itself and the rest
	 * share the row below it, scaled to keep their proportions.
	 *
	 * @since 6.35
	 *
	 * @param {string} images Comma separated file names, relative to the images/upsell folder.
	 * @param {string} alt    Name of the feature being previewed.
	 * @return {Element} The wrapper to append to the tab.
	 */
	function getUpsellImagesWrapper( images, alt ) {
		const folderUrl = `${ frmGlobal.url }/images/upsell/`;
		const [ featured, ...rest ] = images.split( ',' ).map(
			image => img( { src: folderUrl + image.trim(), alt } )
		);
		const children = [ featured ];

		if ( rest.length ) {
			children.push( div( { className: 'frm-upsell-images-row', children: rest } ) );
		}

		return div( { className: 'frm-upsell-images', children } );
	}

	function getScreenshotWrapper( screenshot ) {
		const folderUrl = `${ frmGlobal.url }/images/screenshots/`;
		const wrapper = div( {
			className: 'frm-settings-screenshot-wrapper',
			children: [
				getToolbar(),
				div( { child: img( { src: folderUrl + screenshot } ) } )
			]
		} );

		function getToolbar() {
			const children = getColorIcons();
			children.push( img( { src: `${ frmGlobal.url }/images/tab.svg` } ) );
			return div( {
				className: 'frm-settings-screenshot-toolbar',
				children
			} );
		}

		function getColorIcons() {
			return [ '#ED8181', '#EDE06A', '#80BE30' ].map(
				color => {
					const circle = div( { className: 'frm-minmax-icon' } );
					circle.style.backgroundColor = color;
					return circle;
				}
			);
		}

		return wrapper;
	}

	/* Form settings */

	function showInputIcon( parentClass ) {
		if ( parentClass === undefined ) {
			parentClass = '';
		}
		maybeAddFieldSelection( parentClass );
		jQuery( `${ parentClass } .frm_has_shortcodes:not(.frm-with-right-icon) input,${ parentClass } .frm_has_shortcodes:not(.frm-with-right-icon) textarea` ).wrap( '<span class="frm-with-right-icon"></span>' ).before( '<svg class="frmsvg frm-show-box"><use href="#frm_more_horiz_solid_icon"/></svg>' );
	}

	function initModal( id, width ) {
		const upgradePopup = require( './upgrade-popup' );
		return upgradePopup.initModal( id, width );
	}

	return {
		initModal,
		initUpgradeModal,
		populateUpgradeTab,
		showBasicModal,
		showInputIcon,
	};
};
