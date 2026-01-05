( function() {
	const { toggleAddonState } = require( './admin/addon-state' );

	function onReady() {
		jQuery( document ).on( 'click', '#frm_upgrade_modal .frm-install-addon', installAddon );
		jQuery( document ).on( 'click', '#frm_upgrade_modal .frm-activate-addon', activateAddon );

		function activateAddon( e ) {
			e.preventDefault();
			toggleAddonState( this, 'frm_activate_addon' );
		}

		function installAddon( e ) {
			e.preventDefault();
			toggleAddonState( this, 'frm_install_addon' );
		}

		initUpgradeModal();
		setupBootstrapDropdowns();

		jQuery( document ).on( 'mouseenter.frm', '.frm_help', function() {
			jQuery( this ).off( 'mouseenter.frm' );

			jQuery( this ).tooltip( 'show' );
		} );
	}

	async function initUpgradeModal() {
		const upgradePopup = await import( './admin/upgrade-popup' );
		upgradePopup.initUpgradeModal();
	}

	function setupBootstrapDropdowns() {
		const frmDom = window.frmDom;

		frmDom.bootstrap.setupBootstrapDropdowns( function() {
			const toggle = document.querySelector( '#frm_testmode_enabled_form_actions .dropdown-toggle' );
			if ( toggle ) {
				toggle.classList.add( 'frm-dropdown-toggle' );
				if ( ! toggle.hasAttribute( 'role' ) ) {
					toggle.setAttribute( 'role', 'button' );
				}
				if ( ! toggle.hasAttribute( 'tabindex' ) ) {
					toggle.setAttribute( 'tabindex', 0 );
				}
			}
		} );

		const element = document.getElementById( 'frm_testmode_enabled_form_actions' );
		if ( element ) {
			element.style.display = 'none';
			frmDom.bootstrap.multiselect.init.bind( element )();

			if ( element.disabled ) {
				element.parentElement.querySelector( '.dropdown-toggle' ).classList.add( 'frm_noallow' );
			}
		}
	}

	if ( document.readyState === 'complete' ) {
		onReady();
	} else {
		document.addEventListener( 'DOMContentLoaded', onReady );
	}

	document.addEventListener(
		'frm_after_start_over',
		function() {
			setupBootstrapDropdowns();
		}
	);

	jQuery( document ).on(
		'frmPageChanged frmFormComplete',
		function() {
			setupBootstrapDropdowns();
		}
	);
}() );
