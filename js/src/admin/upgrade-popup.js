import { __ } from '@wordpress/i18n';

const { svg } = frmDom;

function getShowLinkHrefValue( link, showLink ) {
	let customLink = link.getAttribute( 'data-link' );
	if ( customLink === null || typeof customLink === 'undefined' || customLink === '' ) {
		customLink = showLink.getAttribute( 'data-default' );
	}
	return customLink;
}

/**
 * Allow addons to be installed from the upgrade modal.
 *
 * @param {Element}          link
 * @param {string}           context      Either 'modal' or 'tab'.
 * @param {string|undefined} upgradeLabel
 */
export function addOneClick( link, context, upgradeLabel ) {
	let container;
	if ( 'modal' === context ) {
		container = document.getElementById( 'frm_upgrade_modal' );
	} else if ( 'tab' === context ) {
		container = document.getElementById( link.getAttribute( 'href' ).substr( 1 ) );
	} else {
		return;
	}

	const oneclickMessage = container.querySelector( '.frm-oneclick' );
	const upgradeMessage = container.querySelector( '.frm-upgrade-message' );
	const showLink = container.querySelector( '.frm-upgrade-link' );
	const button = container.querySelector( '.frm-oneclick-button' );
	const addonStatus = container.querySelector( '.frm-addon-status' );

	let oneclick = link.getAttribute( 'data-oneclick' );
	let newMessage = link.getAttribute( 'data-message' );
	let showIt = 'block';
	let showMsg = 'block';
	let hideIt = 'none';

	const modalIconWrapper = container.querySelector( '.frm-circled-icon' );
	if ( modalIconWrapper ) {
		modalIconWrapper.classList.remove( 'frm-circled-icon-green' );
		modalIconWrapper.querySelector( 'svg' )?.replaceWith( svg( { href: '#frm_filled_lock_icon' } ) );
	}

	const learnMoreLink = container.querySelector( '.frm-learn-more' );
	if ( learnMoreLink ) {
		learnMoreLink.href = link.dataset.learnMore;
	}

	// If one click upgrade, hide other content.
	if ( oneclickMessage !== null && typeof oneclick !== 'undefined' && oneclick ) {
		if ( newMessage === null ) {
			showMsg = 'none';
		}
		showIt = 'none';
		hideIt = 'block';
		oneclick = JSON.parse( oneclick );

		button.className = button.className.replace( ' frm-install-addon', '' ).replace( ' frm-activate-addon', '' );
		button.className = button.className + ' ' + oneclick.class;
		button.rel = oneclick.url;

		oneclickMessage.textContent = __( 'This plugin is not activated. Would you like to activate it now?', 'formidable' );
		button.textContent = __( 'Activate', 'formidable' );

		const linkIcon = link.querySelector( 'use' );
		if ( linkIcon ) {
			modalIconWrapper?.querySelector( 'svg' ).replaceWith(
				svg( {
					href: linkIcon.getAttribute( 'href' ) || linkIcon.getAttribute( 'xlink:href' ), // Get the icon from xlink:href if it has not been updated to use href
					classList: [ 'frm_svg32' ]
				} )
			);
		}
	}

	if ( ! newMessage ) {
		newMessage = upgradeMessage.getAttribute( 'data-default' );
	}
	if ( undefined !== upgradeLabel ) {
		newMessage = newMessage.replace( '<span class="frm_feature_label"></span>', upgradeLabel );
	}

	upgradeMessage.innerHTML = newMessage;

	if ( link.dataset.upsellImage ) {
		upgradeMessage.append(
			frmDom.img( {
				src: link.dataset.upsellImage,
				alt: link.dataset.upgrade
			} )
		);
	}

	// Either set the link or use the default.
	showLink.href = getShowLinkHrefValue( link, showLink );

	addonStatus.style.display = 'none';

	oneclickMessage.style.display = hideIt;
	button.style.display = hideIt === 'block' ? 'inline-block' : hideIt;
	upgradeMessage.style.display = showMsg;
	showLink.style.display = showIt === 'block' ? 'inline-block' : showIt;

	const showLinkParent = showLink.closest( '.frm-upgrade-modal-actions' );
	if ( showLinkParent ) {
		showLinkParent.style.display = showIt === 'block' ? 'flex' : showIt;
	}
}

export function initModal( id, width ) {
	const $info = jQuery( id );
	if ( ! $info.length ) {
		return false;
	}

	if ( typeof width === 'undefined' ) {
		width = '552px';
	}

	const dialogArgs = {
		dialogClass: 'frm-dialog',
		modal: true,
		autoOpen: false,
		closeOnEscape: true,
		width: width,
		resizable: false,
		draggable: false,
		open: function() {
			jQuery( '.ui-dialog-titlebar' ).addClass( 'frm_hidden' ).removeClass( 'ui-helper-clearfix' );
			jQuery( '#wpwrap' ).addClass( 'frm_overlay' );
			jQuery( '.frm-dialog' ).removeClass( 'ui-widget ui-widget-content ui-corner-all' );
			$info.removeClass( 'ui-dialog-content ui-widget-content' );
			bindClickForDialogClose( $info );
		},
		close: function() {
			jQuery( '#wpwrap' ).removeClass( 'frm_overlay' );
			jQuery( '.spinner' ).css( 'visibility', 'hidden' );

			this.removeAttribute( 'data-option-type' );
			const optionType = document.getElementById( 'bulk-option-type' );
			if ( optionType ) {
				optionType.value = '';
			}
		}
	};

	$info.dialog( dialogArgs );

	return $info;
}

function bindClickForDialogClose( $modal ) {
	const closeModal = function() {
		$modal.dialog( 'close' );
	};
	jQuery( '.ui-widget-overlay' ).on( 'click', closeModal );
	$modal.on( 'click', 'a.dismiss', closeModal );
}

export function initUpgradeModal() {
	const $info = initModal( '#frm_upgrade_modal' );
	if ( $info === false ) {
		return;
	}

	document.addEventListener( 'click', handleUpgradeClick );
	frmDom.util.documentOn( 'change', 'select.frm_select_with_upgrade', handleUpgradeClick );

	function handleUpgradeClick( event ) {
		let element, link, content;

		element = event.target;

		if ( ! element.classList ) {
			return;
		}

		const showExpiredModal = element.classList.contains( 'frm_show_expired_modal' ) || null !== element.querySelector( '.frm_show_expired_modal' ) || element.closest( '.frm_show_expired_modal' );

		// If a `select` element is clicked, check if the selected option has a 'data-upgrade' attribute
		if ( event.type === 'change' && element.classList.contains( 'frm_select_with_upgrade' ) ) {
			const selectedOption = element.options[ element.selectedIndex ];
			if ( selectedOption && selectedOption.dataset.upgrade ) {
				element = selectedOption;
			}
		}

		if ( ! element.dataset.upgrade ) {
			let parent = element.closest( '[data-upgrade]' );
			if ( ! parent ) {
				parent = element.closest( '.frm_field_box' );
				if ( ! parent ) {
					return;
				}
				// Fake it if it's missing to avoid error.
				element.dataset.upgrade = '';
			}
			element = parent;
		}

		if ( showExpiredModal ) {
			const hookName = 'frm_show_expired_modal';
			wp.hooks.doAction( hookName, element );
			return;
		}

		const upgradeLabel = element.dataset.upgrade;
		if ( ! upgradeLabel || element.classList.contains( 'frm_show_upgrade_tab' ) ) {
			return;
		}

		event.preventDefault();

		const modal = $info.get( 0 );
		const lockIcon = modal.querySelector( '.frm_lock_icon' );

		if ( lockIcon ) {
			lockIcon.style.display = 'block';
			lockIcon.classList.remove( 'frm_lock_open_icon' );
			lockIcon.querySelector( 'use' ).setAttribute( 'href', '#frm_lock_icon' );
		}

		const upgradeImageId = 'frm_upgrade_modal_image';
		const oldImage = document.getElementById( upgradeImageId );
		if ( oldImage ) {
			oldImage.remove();
		}

		if ( element.dataset.image ) {
			if ( lockIcon ) {
				lockIcon.style.display = 'none';
			}
			lockIcon.parentNode.insertBefore( frmDom.img( { id: upgradeImageId, src: frmGlobal.url + '/images/' + element.dataset.image } ), lockIcon );
		}

		const level = modal.querySelector( '.license-level' );
		if ( level ) {
			level.textContent = getRequiredLicenseFromTrigger( element );
		}

		// If one click upgrade, hide other content
		addOneClick( element, 'modal', upgradeLabel );

		modal.querySelector( '.frm_are_not_installed' ).style.display = element.dataset.image || element.dataset.oneclick ? 'none' : 'inline-block';
		modal.querySelector( '.frm-upgrade-modal-title-prefix' ).style.display = element.dataset.oneclick ? 'inline' : 'none';
		modal.querySelector( '.frm_feature_label' ).textContent = upgradeLabel;
		modal.querySelector( '.frm-upgrade-modal-title-suffix' ).style.display = 'none';
		modal.querySelector( 'h2' ).style.display = 'block';

		$info.dialog( 'open' );

		// set the utm medium
		const button = modal.querySelector( '.button-primary:not(.frm-oneclick-button)' );
		link = button.getAttribute( 'href' ).replace( /(medium=)[a-z_-]+/ig, '$1' + element.getAttribute( 'data-medium' ) );
		content = element.getAttribute( 'data-content' );
		if ( content === null ) {
			content = '';
		}
		link = link.replace( /(content=)[a-z_-]+/ig, '$1' + content );
		button.setAttribute( 'href', link );
	}
}

function getRequiredLicenseFromTrigger( element ) {
	if ( element.dataset.requires ) {
		return element.dataset.requires;
	}
	return 'Pro';
}
