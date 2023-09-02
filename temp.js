jQuery( document ).on( 'input search change', '.frm-auto-search:not(#frm-form-templates-page #template-search-input)', searchContent );

	function triggerNewFormModal( event ) {
		var $modal,
			dismiss = document.getElementById( 'frm-form-templates-page' ).querySelector( 'a.dismiss' );

		if ( typeof event !== 'undefined' ) {
			event.preventDefault();
		}

		dismiss.setAttribute( 'tabindex', -1 );

		$modal = initModal( '#frm-form-templates-page', '600px' );
		offsetModalY( $modal, '50px' );
		$modal.attr( 'frm-page', 'create' );
		$modal.find( '#template-search-input' ).val( '' ).trigger( 'change' );
		$modal.dialog( 'open' );

		dismiss.removeAttribute( 'tabindex' );
		bindClickForDialogClose( $modal );

		addApplicationsToNewFormModal( $modal.get( 0 ) );
	}

	function offsetModalY( $modal, amount ) {
		const position = {
			my: 'top',
			at: 'top+' + amount,
			of: window
		};
		$modal.dialog( 'option', 'position', position );
	}

	function addApplicationsToNewFormModal( modal ) {
		if ( modal.querySelector( '.frm-ready-made-solution' ) ) {
			// Avoid adding duplicates if the modal is opened multiple times.
			return;
		}

		if ( ! frmGlobal.canAccessApplicationDashboard ) {
			// User does not have privileges to see Ready Made Solutions.
			return;
		}

		doJsonFetch( 'get_applications_data&view=templates' ).then( addTemplatesOnFetchSuccess );

		const categoryList = modal.querySelector( 'ul.frm-categories-list' );

		function addTemplatesOnFetchSuccess( data ) {
			data.templates.forEach( addTemplateToCategoryList );
			initSearch( 'template-search-input', 'frm-searchable-template frm-ready-made-solution' );
		}

		function addTemplateToCategoryList( template ) {
			categoryList.appendChild( getReadyMadeSolution( template ) );
		}

		function getReadyMadeSolution( template ) {
			const image = tag( 'img' );
			const thumbnailFolderUrl = frmGlobal.url + '/images/applications/thumbnails/';
			const filenameToUse = template.hasLiteThumbnail ? template.key + '.png' : 'placeholder.svg';
			image.setAttribute( 'src', thumbnailFolderUrl + filenameToUse );

			const imageWrapper = div({ child: image });
			const href = frmGlobal.applicationsUrl + '&triggerViewApplicationModal=1&template=' + template.key;

			return tag(
				'li',
				{
					className: 'frm-searchable-template frm-ready-made-solution frm-selectable',
					data: {
						href: href
					},
					children: [
						imageWrapper,
						div({
							className: 'frm-solution-details',
							children: [
								span({
									className: 'frm-meta-tag',
									text: __( 'Ready Made Solution', 'formidable' )
								}),
								tag( 'h3', template.name ),
								a({
									text: __( 'See all applications', 'formidable' ),
									href: frmGlobal.applicationsUrl
								})
							]
						}),
						div({
							className: 'frm-hover-icons',
							child: a({
								child: svg({ href: '#frm_plus_icon' }),
								href: href
							})
						})
					]
				}
			);
		}
	}

	function initNewFormModal() {
		var installFormTrigger,
			activeHoverIcons,
			$modal,
			handleError,
			handleEmailAddressError,
			handleConfirmEmailAddressError,
			showFreeTemplatesForm,
			firstLockedTemplate,
			isShowFreeTemplatesFormFirst,
			url,
			urlParams;

		url       = new URL( window.location.href );
		urlParams = url.searchParams;

		isShowFreeTemplatesFormFirst = urlParams.get( 'free-templates' );


		// Welcome page
		jQuery( document ).on( 'click', '.frm-trigger-new-form-modal', triggerNewFormModal );

		$modal = initModal( '#frm-form-templates-page', '600px' );

		if ( false === $modal ) {
			return;
		}

		setTimeout(
			function() {
				$modal.get( 0 ).querySelector( '.postbox' ).style.display = 'block'; // Fixes pro issue #3508, prevent a conflict that hides the postbox in modal.
			},
			0
		);

		installFormTrigger = document.createElement( 'a' );
		installFormTrigger.classList.add( 'frm-install-template', 'frm_hidden' );
		document.body.appendChild( installFormTrigger );

		jQuery( '.frm-install-template' ).on( 'click', function( event ) {
			var $h3Clone = jQuery( this ).closest( 'li, td' ).find( 'h3' ).clone(),
				nameLabel = document.getElementById( 'frm_new_name' ),
				descLabel = document.getElementById( 'frm_new_desc' ),
				oldName;

			$h3Clone.find( 'svg, .frm-plan-required-tag' ).remove();
			oldName = $h3Clone.html().trim();

			event.preventDefault();

			document.getElementById( 'frm_template_name' ).value = oldName;
			document.getElementById( 'frm_link' ).value = this.attributes.rel.value;
			document.getElementById( 'frm_action_type' ).value = 'frm_install_template';
			nameLabel.textContent = nameLabel.getAttribute( 'data-form' );
			descLabel.textContent = descLabel.getAttribute( 'data-form' );
			$modal.dialog( 'open' );
		});

		jQuery( document ).on( 'submit', '#frm-new-template', installTemplate );

		jQuery( document ).on( 'click', '.frm-hover-icons .frm-preview-form', function( event ) {
			var $li, link, iframe,
				container = document.getElementById( 'frm-preview-block' );

			event.preventDefault();

			$li = jQuery( this ).closest( 'li' );
			link = $li.attr( 'data-preview' );

			if ( link.indexOf( ajaxurl ) > -1 ) {
				iframe = document.createElement( 'iframe' );
				iframe.src = link;
				iframe.height = '400';
				iframe.width = '100%';
				container.innerHTML = '';
				container.appendChild( iframe );
			} else {
				frmApiPreview( container, link );
			}

			jQuery( '#frm-preview-title' ).text( getStrippedTemplateName( $li ) );
			$modal.attr( 'frm-page', 'preview' );
			activeHoverIcons = jQuery( this ).closest( '.frm-hover-icons' );
		});

		jQuery( document ).on( 'click', 'li.frm-ready-made-solution[data-href]', function() {
			window.location = this.getAttribute( 'data-href' );
		});

		jQuery( document ).on( 'click', 'li .frm-hover-icons .frm-create-form', function( event ) {
			var $li, name, link, action;

			event.preventDefault();

			$li = jQuery( this ).closest( 'li' );

			if ( $li.is( '[data-href]' ) ) {
				window.location = $li.attr( 'data-href' );
				return;
			}

			if ( $li.hasClass( 'frm-add-blank-form' ) ) {
				name = link = '';
				action = 'frm_install_form';
			} else if ( $li.is( '[data-rel]' ) ) {
				name = getStrippedTemplateName( $li );
				link = $li.attr( 'data-rel' );
				action = 'frm_install_template';
			} else {
				return;
			}

			transitionToAddDetails( $modal, name, link, action );
		});

		// Welcome page modals.
		jQuery( document ).on( 'click', '.frm-create-blank-form', function( event ) {
			event.preventDefault();
			jQuery( '.frm-trigger-new-form-modal' ).trigger( 'click' );
			transitionToAddDetails( $modal, '', '', 'frm_install_form' );

			// Close the modal with the cancel button.
			jQuery( '.frm-modal-cancel.frm-back-to-all-templates' ).on( 'click', function() {
				jQuery( '.ui-widget-overlay' ).trigger( 'click' );
			});
		});

		jQuery( document ).on( 'click', '.frm-featured-forms.frm-templates-list li [role="button"]:not(a), .frm-templates-list .accordion-section.open li [role="button"]:not(a)', function( event ) {
			var $hoverIcons, $trigger,
				$li = jQuery( this ).closest( 'li' ),
				triggerClass = $li.hasClass( 'frm-locked-template' ) ? 'frm-unlock-form' : 'frm-create-form';

			$hoverIcons = $li.find( '.frm-hover-icons' );
			if ( ! $hoverIcons.length ) {
				$li.trigger( 'mouseover' );
				$hoverIcons = $li.find( '.frm-hover-icons' );
				$hoverIcons.hide();
			}

			$trigger = $hoverIcons.find( '.' + triggerClass );
			$trigger.trigger( 'click' );
		});

		jQuery( document ).on( 'click', 'li .frm-hover-icons .frm-delete-form', function( event ) {
			var $li,
				trigger;

			event.preventDefault();

			$li = jQuery( this ).closest( 'li' );
			$li.addClass( 'frm-deleting' );
			trigger = document.createElement( 'a' );
			trigger.setAttribute( 'href', '#' );
			trigger.setAttribute( 'data-id', $li.attr( 'data-formid' ) );
			$li.attr( 'id', 'frm-template-custom-' + $li.attr( 'data-formid' ) );
			jQuery( trigger ).on( 'click', trashTemplate );
			trigger.click();
			setTemplateCount( $li.closest( '.accordion-section' ).get( 0 ) );
		});

		showFreeTemplatesForm = function( $el ) {
			var formContainer = document.getElementById( 'frmapi-email-form' );
			jQuery.ajax({
				dataType: 'json',
				url: formContainer.getAttribute( 'data-url' ),
				success: function( json ) {
					var form = json.renderedHtml;
					form = form.replace( /<link\b[^>]*(formidableforms.css|action=frmpro_css)[^>]*>/gi, '' );
					formContainer.innerHTML = form;
				}
			});

			$modal.attr( 'frm-page', 'email' );
			$modal.attr( 'frm-this-form', $el.attr( 'data-key' ) );
			$el.append( installFormTrigger );
		};

		jQuery( document ).on( 'click', 'li.frm-locked-template .frm-hover-icons .frm-unlock-form', function( event ) {
			var $li,
				activePage;

			event.preventDefault();

			$li = jQuery( this ).closest( '.frm-locked-template' );

			if ( $li.hasClass( 'frm-free-template' ) ) {
				showFreeTemplatesForm( $li );
				return;
			}

			if ( $modal.hasClass( 'frm-expired' ) ) {
				activePage = 'renew';
			} else {
				activePage = 'upgrade';
			}

			$modal.attr( 'frm-page', activePage );
		});

		jQuery( document ).on( 'click', '#frm-form-templates-page #frm-template-drop', function() {
			jQuery( this )
				.closest( '.accordion-section-content' ).css( 'overflow', 'visible' )
				.closest( '.accordion-section' ).css( 'z-index', 1 );
		});

		jQuery( document ).on( 'click', '#frm-form-templates-page #frm-template-drop + .frm-dropdown-menu .frm-build-template', function() {
			var name = this.getAttribute( 'data-fullname' ),
				link = this.getAttribute( 'data-formid' ),
				action = 'frm_build_template';
			transitionToAddDetails( $modal, name, link, action );
		});

		handleError = function( inputId, errorId, type, message ) {
			var $error = jQuery( errorId );
			$error.removeClass( 'frm_hidden' ).attr( 'frm-error', type );

			if ( typeof message !== 'undefined' ) {
				$error.find( 'span[frm-error="' + type + '"]' ).text( message );
			}

			jQuery( inputId ).one( 'keyup', function() {
				$error.addClass( 'frm_hidden' );
			});
		};

		handleEmailAddressError = function( type ) {
			handleError( '#frm_leave_email', '#frm_leave_email_error', type );
		};

		jQuery( document ).on( 'click', '#frm-add-my-email-address', function( event ) {
			var email = document.getElementById( 'frm_leave_email' ).value.trim(),
				regex,
				$hiddenForm,
				$hiddenEmailField;

			event.preventDefault();

			if ( '' === email ) {
				handleEmailAddressError( 'empty' );
				return;
			}

			regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;

			if ( regex.test( email ) === false ) {
				handleEmailAddressError( 'invalid' );
				return;
			}

			$hiddenForm = jQuery( '#frmapi-email-form' ).find( 'form' );
			$hiddenEmailField = $hiddenForm.find( '[type="email"]' ).not( '.frm_verify' );
			if ( ! $hiddenEmailField.length ) {
				return;
			}

			$hiddenEmailField.val( email );
			jQuery.ajax({
				type: 'POST',
				url: $hiddenForm.attr( 'action' ),
				data: $hiddenForm.serialize() + '&action=frm_forms_preview'
			}).done( function( data ) {
				var message = jQuery( data ).find( '.frm_message' ).text().trim();
				if ( message.indexOf( 'Thanks!' ) >= 0 ) {
					$modal.attr( 'frm-page', 'code' );
				} else {
					handleEmailAddressError( 'invalid' );
				}
			});
		});

		handleConfirmEmailAddressError = function( type, message ) {
			handleError( '#frm_code_from_email', '#frm_code_from_email_error', type, message );
		};

		jQuery( document ).on( 'click', '.frm-confirm-email-address', function( event ) {
			var code = document.getElementById( 'frm_code_from_email' ).value.trim();

			event.preventDefault();

			if ( '' === code ) {
				handleConfirmEmailAddressError( 'empty' );
				return;
			}

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: {
					action: 'template_api_signup',
					nonce: frmGlobal.nonce,
					code: code,
					key: $modal.attr( 'frm-this-form' )
				},
				success: function( response ) {
					if ( response.success ) {
						if ( isShowFreeTemplatesFormFirst ) {
							// Remove free-templates param from URL then reload page.
							urlParams.delete( 'free-templates' );
							url.search = urlParams.toString();
							window.location.href = url.toString();

							return;
						}

						if ( typeof response.data !== 'undefined' && typeof response.data.url !== 'undefined' ) {
							installFormTrigger.setAttribute( 'rel', response.data.url );
							installFormTrigger.click();
							$modal.attr( 'frm-page', 'details' );

							const hookName = 'frm-form-templates-page_form';
							wp.hooks.doAction( hookName, $modal );

							document.getElementById( 'frm_action_type' ).value = 'frm_install_template';

							if ( typeof response.data.urlByKey !== 'undefined' ) {
								updateTemplateModalFreeUrls( response.data.urlByKey );
							}
						}
					} else {
						if ( Array.isArray( response.data ) && response.data.length ) {
							handleConfirmEmailAddressError( 'custom', response.data[0].message );
						} else {
							handleConfirmEmailAddressError( 'wrong-code' );
						}

						jQuery( '#frm_code_from_email_options' ).removeClass( 'frm_hidden' );
					}
				}
			});
		});

		jQuery( document ).on( 'click', '#frm-change-email-address', function() {
			$modal.attr( 'frm-page', 'email' );
		});

		jQuery( document ).on( 'click', '#frm-resend-code', function() {
			document.getElementById( 'frm_code_from_email' ).value = '';
			jQuery( '#frm_code_from_email_options, #frm_code_from_email_error' ).addClass( 'frm_hidden' );
			document.getElementById( 'frm-add-my-email-address' ).click();
		});

		jQuery( document ).on( 'click', '#frm-form-templates-page .frm-modal-back, #frm-form-templates-page .frm_modal_footer .frm-modal-cancel, #frm-form-templates-page .frm-back-to-all-templates', function( event ) {
			document.getElementById( 'frm-create-title' ).removeAttribute( 'frm-type' );
			$modal.attr( 'frm-page', 'create' );
		});

		jQuery( document ).on( 'click', '.frm-use-this-template', function( event ) {
			var $trigger;

			event.preventDefault();

			$trigger = activeHoverIcons.find( '.frm-create-form' );
			if ( $trigger.closest( '.frm-selectable' ).hasClass( 'frm-locked-template' ) ) {
				$trigger = activeHoverIcons.find( '.frm-unlock-form' );
			}

			$trigger.trigger( 'click' );
		});

		jQuery( document ).on( 'click', '.frm-submit-new-template', function( event ) {
			var button;
			event.preventDefault();
			button = document.getElementById( 'frm-new-template' ).querySelector( 'button' );
			if ( null !== button ) {
				button.click();
			}
		});


		if ( urlParams.get( 'triggerNewFormModal' ) ) {
			triggerNewFormModal();

			if ( isShowFreeTemplatesFormFirst ) {
				firstLockedTemplate = jQuery( 'li.frm-locked-template.frm-free-template' ).eq( 0 );

				if ( firstLockedTemplate.length ) {
					showFreeTemplatesForm( firstLockedTemplate );
				}

				// Hides the back button in the Free Template Modal and shows it when the cancel button is clicked
				$modalBackButton = $modal.find( '.frm-modal-back' );
				$modalBackButton.hide();
				$modal.find( '.frm-modal-cancel' ).on( 'click', ( event ) => {
					$modalBackButton.show();
					$modal.dialog( 'close' );
				});
			}
		}

		initSearch( 'template-search-input', 'control-section accordion-section' );
	}

	function initSearch( inputID, itemClass ) {
		const searchInput = document.getElementById( inputID );

		if ( itemClass === 'control-section accordion-section' ) {
			itemClass = 'frm-selectable frm-searchable-template';

			const handleTemplateSearch = () => {
				document.querySelectorAll( '.control-section.accordion-section' ).forEach( category => {
					const found = category.querySelector( '.frm-selectable.frm-searchable-template:not(.frm_hidden)' ) || ( searchInput.value === '' && category.querySelector( '#frm-template-drop' ) );
					if ( found ) {
						setTemplateCount( category );
					}
					category.classList.toggle( 'frm_hidden', ! found );
				});
			};

			frmDom.search.init( searchInput, itemClass, { handleSearchResult: handleTemplateSearch });

		} else {
			if ( itemClass === 'frm-searchable-template frm-ready-made-solution' ) {
				Array.from( document.getElementsByClassName( itemClass ) ).forEach( item => {
					let innerText = '';
					innerText = item.querySelector( 'h3' ).innerText;
					item.setAttribute( 'frm-search-text', innerText.toLowerCase() );
				});
			}
			frmDom.search.init( searchInput, itemClass );
		}

	}

	function updateTemplateModalFreeUrls( urlByKey ) {
		jQuery( '#frm-form-templates-page' ).find( '.frm-selectable[data-key]' ).each( function() {
			var $template = jQuery( this ),
				key = $template.attr( 'data-key' );
			if ( 'undefined' !== typeof urlByKey[ key ]) {
				$template.removeClass( 'frm-locked-template' );
				$template.find( 'h3 svg' ).remove(); // remove the lock from the title
				$template.attr( 'data-rel', urlByKey[ key ]);
			}
		});
	}

	function transitionToAddDetails( $modal, name, link, action ) {
		var nameLabel = document.getElementById( 'frm_new_name' ),
			descLabel = document.getElementById( 'frm_new_desc' ),
			type = [ 'frm_install_template', 'frm_install_form' ].indexOf( action ) >= 0 ? 'form' : 'template',
			templateNameInput = document.getElementById( 'frm_template_name' );

		templateNameInput.value = name;
		document.getElementById( 'frm_link' ).value = link;
		document.getElementById( 'frm_action_type' ).value = action;
		nameLabel.textContent = nameLabel.getAttribute( 'data-' + type );
		if ( descLabel !== null ) {
			descLabel.textContent = descLabel.getAttribute( 'data-' + type );
		}

		document.getElementById( 'frm-create-title' ).setAttribute( 'frm-type', type );

		$modal.attr( 'frm-page', 'details' );

		const hookName = 'frm-form-templates-page_form';
		wp.hooks.doAction( hookName, $modal );

		if ( '' === name ) {
			templateNameInput.focus();
		}
	}

	function getStrippedTemplateName( $li ) {
		var $clone = $li.find( 'h3' ).clone();
		$clone.find( 'svg, .frm-plan-required-tag, .frm-new-pill' ).remove();
		return $clone.html().trim();
	}

	function setTemplateCount( category, searchableTemplates ) {
		var count,
			templateIndex,
			availableCounter,
			availableCount;

		if ( typeof searchableTemplates === 'undefined' ) {
			searchableTemplates = category.querySelectorAll( '.frm-searchable-template:not(.frm_hidden):not(.frm-deleting)' );
		}

		count = searchableTemplates.length;
		category.querySelector( '.frm-template-count' ).textContent = count;

		jQuery( category ).find( '.frm-templates-plural' ).toggleClass( 'frm_hidden', count === 1 );
		jQuery( category ).find( '.frm-templates-singular' ).toggleClass( 'frm_hidden', count !== 1 );

		availableCounter = category.querySelector( '.frm-available-templates-count' );
		if ( availableCounter !== null ) {
			availableCount = 0;
			for ( templateIndex in searchableTemplates ) {
				if ( ! isNaN( templateIndex ) && ! searchableTemplates[ templateIndex ].classList.contains( 'frm-locked-template' ) ) {
					availableCount++;
				}
			}

			availableCounter.textContent = availableCount;
		}
	}
