( function() {
	const hookNamespace = 'formidable-square';
	wp.hooks.addAction( 'frm_trans_toggled_gateway', hookNamespace, onGatewayToggle );
	wp.hooks.addAction( 'frm_filled_form_action', hookNamespace, onFilledFormAction );

	const actions = document.getElementById( 'frm_notification_settings' );
	jQuery( actions ).on( 'change', '.frm_trans_type', onToggleSub );

	const { __ } = wp.i18n;

	function updateGatewaySettingsVisibility( settings ) {
		const typeDropdown = settings.querySelector( 'select.frm_trans_type' );
		if ( ! typeDropdown ) {
			return;
		}

		if ( 'recurring' === typeDropdown.value ) {
			const activeGateways = Array.from( settings.querySelectorAll( '[name*="[post_content][gateway]"]:checked' ) ).map( function( el ) {
				return el.value;
			} );

			settings.querySelectorAll( '.frm_trans_sub_opts' ).forEach(
				function( subOpts ) {
					// Check if this setting has a show_* class for any active gateway
					const hasActiveGatewayClass = Array.from( subOpts.classList ).some( function( className ) {
						return activeGateways.some( function( gateway ) {
							return className === `show_${ gateway }`;
						} );
					} );

					if ( hasActiveGatewayClass ) {
						subOpts.classList.remove( 'frm_hidden' );
						if ( subOpts.classList.contains( 'frm_grid_container' ) ) {
							subOpts.style.display = 'grid';
						}
					} else {
						// Check if it has any show_* class for a different gateway
						const hasAnyGatewayClass = Array.from( subOpts.classList ).some( function( className ) {
							return className.startsWith( 'show_' );
						} );

						if ( hasAnyGatewayClass ) {
							// Hide if it has a show_* class but not for any active gateway
							subOpts.classList.add( 'frm_hidden' );
							subOpts.style.display = 'none';
						} else {
							// Show if it has no show_* class (shared setting)
							subOpts.classList.remove( 'frm_hidden' );
							if ( subOpts.classList.contains( 'frm_grid_container' ) ) {
								subOpts.style.display = 'grid';
							}
						}
					}
				}
			);
			return;
		}

		settings.querySelectorAll( '.frm_trans_sub_opts' ).forEach(
			function( subOpts ) {
				subOpts.classList.add( 'frm_hidden' );
				subOpts.style.display = 'none';
			}
		);
	}

	function onGatewayToggle( { gateway, settings, checked } ) {
		if ( 'square' === gateway && checked ) {
			syncRepeat( settings.get( 0 ) );
		}

		syncCurrency( gateway, settings.get( 0 ) );

		updateGatewaySettingsVisibility( settings.get( 0 ) );

		const captureSetting = settings.get( 0 ).querySelector( '[name*="[post_content][capture]"]' );
		if ( captureSetting ) {
			const wrapper = captureSetting.closest( '.frm_gateway_no_recur' );
			if ( wrapper ) {
				if ( 'square' === gateway ) {
					wrapper.style.display = 'none';
				}
			}
		}
	}

	function onFilledFormAction( $container ) {
		const settings = $container.get( 0 ).closest( '.frm_form_action_settings' );
		if ( ! settings || ! settings.classList.contains( 'frm_single_payment_settings' ) ) {
			return;
		}

		const squareGatewayOption = settings.querySelector( '[name*="[post_content][gateway]"][value="square"]' );
		if ( squareGatewayOption?.checked ) {
			syncRepeat( settings );
		}

		updateGatewaySettingsVisibility( settings );
	}

	function onToggleSub() {
		const target = this;
		setTimeout( function() {
			const settings = target.closest( '.frm_form_action_settings' );
			updateGatewaySettingsVisibility( settings );
		}, 0 );
	}

	function syncRepeat( settings ) {
		// Sync recurring payment setting.
		const repeatCadence = settings.querySelector( '[name*="[post_content][repeat_cadence]"]' );
		if ( repeatCadence ) {
			return;
		}

		const intervalCount = settings.querySelector( '[name*="[post_content][interval_count]"]' );
		if ( ! intervalCount ) {
			return;
		}

		const settingWrapper = intervalCount.closest( '.frm_trans_sub_opts' );
		if ( ! settingWrapper ) {
			return;
		}

		const clone = settingWrapper.cloneNode( true );
		const intervalCountSetting = clone.querySelector( '[name*="[post_content][interval_count]"]' );

		const repeatCadenceName = intervalCountSetting.name.replace( 'interval_count', 'repeat_cadence' );

		const newDropdown = document.createElement( 'select' );
		newDropdown.name = repeatCadenceName;
		const repeatCadenceOptions = {
			DAILY: __( 'Daily', 'formidable' ),
			WEEKLY: __( 'Weekly', 'formidable' ),
			EVERY_TWO_WEEKS: __( 'Every Two Weeks', 'formidable' ),
			THIRTY_DAYS: __( 'Every Thirty Days', 'formidable' ),
			SIXTY_DAYS: __( 'Every Sixty Days', 'formidable' ),
			NINETY_DAYS: __( 'Every Ninety Days', 'formidable' ),
			MONTHLY: __( 'Monthly', 'formidable' ),
			EVERY_TWO_MONTHS: __( 'Every Two Months', 'formidable' ),
			QUARTERLY: __( 'Quarterly', 'formidable' ),
			EVERY_FOUR_MONTHS: __( 'Every Four Months', 'formidable' ),
			EVERY_SIX_MONTHS: __( 'Every Six Months', 'formidable' ),
			ANNUAL: __( 'Annual', 'formidable' ),
			EVERY_TWO_YEARS: __( 'Every Two Years', 'formidable' )
		};

		const selectedOption = settings.querySelector( '.frm-repeat-cadence-value' );

		for ( const optionKey in repeatCadenceOptions ) {
			const option = document.createElement( 'option' );
			option.value = optionKey;
			option.textContent = repeatCadenceOptions[ optionKey ];

			if ( selectedOption && selectedOption.value === optionKey ) {
				option.selected = true;
			}

			newDropdown.append( option );
		}

		intervalCountSetting.parentNode.insertBefore( newDropdown, intervalCountSetting.nextSibling );

		intervalCountSetting.remove();
		clone.querySelector( '[name*="[post_content][interval]"]' )?.remove();
		settingWrapper.parentNode.insertBefore( clone, settingWrapper.nextSibling );

		const label = newDropdown.closest( '.frm_trans_sub_opts' )?.querySelector( 'label' );
		if ( label?.textContent.includes( 'Repeat Every' ) ) {
			label.textContent = 'Repeat';
		}

		newDropdown.closest( '.frm_trans_sub_opts' )?.classList.add( 'show_square' );
		newDropdown.closest( '.frm_trans_sub_opts' )?.classList.remove( 'frm_hidden' );

		const stripeLabel = intervalCount.closest( '.frm_trans_sub_opts' )?.querySelector( 'label' );
		if ( stripeLabel?.textContent.includes( 'Repeat Every' ) ) {
			stripeLabel.textContent = 'Repeat';
		}

		intervalCount.closest( '.frm_trans_sub_opts' )?.classList.add( 'show_stripe', 'show_paypal', 'frm_hidden' );
	}

	function syncCurrency( gateway, settings ) {
		// Sync currency setting.
		const currencySetting = settings.querySelector( '[name*="[post_content][currency]"]' );
		if ( ! currencySetting ) {
			return;
		}

		let option = currencySetting.querySelector( 'option.square-currency' );

		if ( option ) {
			if ( 'square' === gateway ) {
				currencySetting.value = option.value;
				currencySetting.disabled = true;
			} else {
				currencySetting.disabled = false;
				option.remove();
				currencySetting.value = 'usd';
			}
			return;
		}

		// Option didn't exist yet, so add it.
		if ( 'square' === gateway ) {
			option = document.createElement( 'option' );
			option.value = 'square';
			option.textContent = 'Use Square Merchant Currency';
			option.classList.add( 'square-currency' );
			currencySetting.append( option );

			currencySetting.value = option.value;
			currencySetting.disabled = true;
		} else {
			currencySetting.disabled = false;
		}
	}
}() );
