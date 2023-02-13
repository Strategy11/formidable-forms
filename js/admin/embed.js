( function() {
	/** globals ajaxurl, wp, frmDom */

	if ( 'undefined' === typeof ajaxurl || 'undefined' === typeof wp || 'undefined' === typeof frmDom ) {
		return;
	}

	const __ = wp.i18n.__;
	const { div, tag, svg } = frmDom;
	const { maybeCreateModal, footerButton } = frmDom.modal;

	let autoId = 0;
	let modal;

	const state = {
		type: 'form',
		objectId: 0,
		objectKey: ''
	};

	initEmbedModal();

	function initEmbedModal() {
		document.addEventListener( 'click', listenForEmbedClick );
	}

	function listenForEmbedClick( event ) {
		let clicked = false;

		const element = event.target;
		const tag = element.tagName.toLowerCase();

		switch ( tag ) {
			case 'a':
				clicked = 'frm-embed-action' === element.id || element.classList.contains( 'frm-embed-form' );

				if ( clicked ) {
					state.type = 'form';
				} else {
					clicked = element.classList.contains( 'frm-embed-view' );
					if ( clicked ) {
						state.type = 'view';
					}
				}
				break;

			case 'svg':
			case 'use':
				clicked = null !== element.closest( '.frm-embed-form' );
				if ( clicked ) {
					state.type = 'form';
				}
				break;
		}

		if ( clicked ) {
			event.preventDefault();

			const hookName = 'frm_before_embed_modal';
			const initialIds = [ 0, 0 ];
			const hookArgs = {
				element,
				type: state.type
			};
			const [ objectId, objectKey ] = wp.hooks.applyFilters( hookName, initialIds, hookArgs );

			if ( objectId && objectKey ) {
				state.objectId = objectId;
				state.objectKey = objectKey;
				openEmbedModal();
			}
		}
	}

	function openEmbedModal() {
		/* translators: %s type: ie form, view. */
		const title = __( 'Embed %s', 'formidable' ).replace( '%s', getTypeDescription() );

		modal = maybeCreateModal(
			'frm_embed_modal',
			{
				title,
				content: getModalOptions(),
				footer: getModalFooter()
			}
		);
		modal.classList.add( 'frm_common_modal' );
		modal.classList.remove( 'frm-on-page-2' );

		const $modal = jQuery( modal );
		offsetModalY( $modal, '50px' );
		$modal.parent().addClass( 'frm-embed-modal-wrapper' );
	}

	function getModalFooter() {
		const doneButton = footerButton({
			text: __( 'Done', 'formidable' ),
			buttonType: 'primary'
		});

		const cancelButton = footerButton({
			text: __( 'Back', 'formidable' ),
			buttonType: 'cancel'
		});
		cancelButton.addEventListener(
			'click',
			function( event ) {
				event.preventDefault();
				openEmbedModal();
			}
		);

		return div({
			children: [ doneButton, cancelButton ]
		});
	}

	function offsetModalY( $modal, amount ) {
		const position = {
			my: 'top',
			at: 'top+' + amount,
			of: window
		};
		$modal.dialog( 'option', 'position', position );
	}

	function getModalOptions() {
		const content = div({ className: 'frm-embed-modal-content frm_wrap' });
		const typeDescription = getTypeDescription();

		/* translators: %s type: ie form, view. */
		const existingPageDescription = __( 'Embed your %s into an existing page.', 'formidable' ).replace( '%s', typeDescription );

		/* translators: %s type: ie form, view. */
		const newPageDescription = __( 'Put your %s on a newly created page.', 'formidable' ).replace( '%s', typeDescription );

		/* translators: %s type: ie form, view. */
		const insertManuallyDescription = __( 'Use shortcodes or PHP code to put the %s anywhere.', 'formidable' ).replace( '%s', typeDescription );

		const options = [
			{
				icon: '#frm_file_icon',
				label: __( 'Select existing page', 'formidable' ),
				description: existingPageDescription,
				callback: () => {
					content.innerHTML = '';

					const spinner = tag( 'span' );
					spinner.className = 'frm-wait frm_spinner';
					spinner.style.visibility = 'visible';
					content.appendChild( spinner );

					const gap = div();
					gap.style.height = '20px';
					content.appendChild( gap );

					content.classList.add( 'frm-loading-page-options' );

					jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {
							action: 'get_page_dropdown',
							nonce: frmGlobal.nonce
						},
						dataType: 'json',
						success: addExistingPageDropdown
					});

					function addExistingPageDropdown( response ) {
						if ( 'object' !== typeof response || 'string' !== typeof response.html ) {
							return;
						}

						content.classList.remove( 'frm-loading-page-options' );
						content.innerHTML = '';

						const typeDescription = getTypeDescription();

						/* translators: %s: type (ie. view, form). */
						const titleText = __( 'Select the page you want to embed your %s into.', 'formidable' ).replace( '%s', typeDescription );

						const title = getLabel( titleText );
						title.setAttribute( 'for', 'frm_page_dropdown' );
						content.appendChild( title );

						let editPageUrl;

						doneButton = modal.querySelector( '.frm_modal_footer .button-primary' );
						doneButton.classList.remove( 'dismiss' );
						/* translators: %s: type (ie. view, form). */
						doneButton.textContent = __( 'Insert %s', 'formidable' ).replace( '%s', typeDescription );
						doneButton.addEventListener( 'click', redirectToExistingPageWithInjectedShortcode );

						const dropdownWrapper = div();
						dropdownWrapper.innerHTML = response.html;
						content.appendChild( dropdownWrapper );

						if ( 'form' === state.type ) {
							editPageUrl = response.edit_page_url + '&frmForm=' + state.objectId;
						} else {
							const hookName = 'frm_embed_edit_page_url';
							const hookArgs = {
								objectId: state.objectId,
								type: state.type
							};
							editPageUrl = wp.hooks.applyFilters( hookName, response.edit_page_url, hookArgs );
						}

						frmDom.autocomplete.initAutocomplete( 'page', dropdownWrapper );

						function redirectToExistingPageWithInjectedShortcode( event ) {
							event.preventDefault();

							const pageDropdown = modal.querySelector( '[name="frm_page_dropdown"]' );
							modal.querySelectorAll( '.frm_error_style' ).forEach( error => error.remove() );

							const pageId = pageDropdown.value;

							if ( '0' === pageId || '' === pageId ) {
								const error = div({ className: 'frm_error_style' });
								error.setAttribute( 'role', 'alert' );
								error.textContent = __( 'Please select a page', 'formidable' );
								content.insertBefore( error, title.nextElementSibling );
								return;
							}

							window.location.href = editPageUrl.replace( 'post=0', 'post=' + pageId );
						}
					}
				}
			},
			{
				icon: '#frm_plus_icon',
				label: __( 'Create new page', 'formidable' ),
				description: newPageDescription,
				callback: () => {
					content.innerHTML = '';

					const wrapper = div({ className: 'field-group' });
					const form = tag( 'form' );

					const createPageWithShortcode = () => {
						const hookName = 'frm_create_page_with_shortcode_data';
						const data = wp.hooks.applyFilters(
							hookName,
							{
								action: 'frm_create_page_with_shortcode',
								object_id: state.objectId,
								type: state.type,
								name: input.value,
								nonce: frmGlobal.nonce
							}
						);
						jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data,
							dataType: 'json',
							success: function( response ) {
								if ( 'object' === typeof response && 'string' === typeof response.redirect ) {
									window.location.href = response.redirect;
								}
							}
						});
					};

					form.addEventListener(
						'submit',
						function( event ) {
							event.preventDefault();
							createPageWithShortcode();
							return false;
						},
						true
					);

					const title = getLabel( __( 'What will you call the new page?', 'formidable' ) );
					title.setAttribute( 'for', 'frm_name_your_page' );
					form.appendChild( title );

					const input = tag( 'input' );
					input.id = 'frm_name_your_page';
					input.placeholder = __( 'Name your page', 'formidable' );
					form.appendChild( input );

					wrapper.appendChild( form );
					content.appendChild( wrapper );

					input.type = 'text';
					input.focus();

					doneButton = modal.querySelector( '.frm_modal_footer .button-primary' );
					doneButton.textContent = __( 'Create page', 'formidable' );
					doneButton.addEventListener(
						'click',
						function( event ) {
							event.preventDefault();
							createPageWithShortcode();
						}
					);
				}
			},
			{
				icon: '#frm_code_icon',
				label: __( 'Insert manually', 'formidable' ),
				description: insertManuallyDescription,
				callback: () => {
					content.innerHTML = '';

					if ( 'form' === state.type ) {
						getEmbedFormManualExamples().forEach( example => content.appendChild( getEmbedExample( example ) ) );
					} else {
						const hookName = 'frm_embed_examples';
						const hookArgs = {
							type: state.type,
							objectId: state.objectId,
							objectKey: state.objectKey
						};
						wp.hooks.applyFilters( hookName, [], hookArgs ).forEach(
							example => content.appendChild( getEmbedExample( example ) )
						);
					}
				}
			}
		];

		options.forEach(
			option => content.appendChild( getModalOption( option ) )
		);

		return content;
	}

	function getTypeDescription() {
		switch ( state.type ) {
			case 'view':
				return __( 'view', 'formidable' );
			case 'form':
			default:
				return __( 'form', 'formidable' );
		}
	}

	function getModalOption({ icon, label, description, callback }) {
		const output = div();
		output.appendChild( wrapModalOptionIcon( icon ) );
		output.className = 'frm-embed-modal-option';
		output.setAttribute( 'tabindex', 0 );
		output.setAttribute( 'role', 'button' );

		const textWrapper = div();
		textWrapper.appendChild( getLabel( label ) );
		textWrapper.appendChild( div( description ) );
		output.appendChild( textWrapper );

		output.appendChild( div({ className: 'caret' }) );

		output.addEventListener(
			'click',
			function() {
				modal.classList.add( 'frm-on-page-2' );
				callback();
			}
		);
		return output;
	}

	function wrapModalOptionIcon( iconHref ) {
		return div({
			className: 'frm-icon-wrapper',
			child: svg({ href: iconHref })
		});
	}

	function getEmbedFormManualExamples() {
		const formId = state.objectId;
		const formKey = state.objectKey;

		let examples = [
			{
				label: __( 'WordPress shortcode', 'formidable' ),
				example: '[formidable id=' + formId + ']',
				link: 'https://formidableforms.com/knowledgebase/publish-a-form/#kb-insert-the-shortcode-manually',
				linkLabel: __( 'How to use shortcodes in WordPress', 'formidable' )
			},
			{
				label: __( 'Use PHP code', 'formidable' ),
				example: '<?php echo FrmFormsController::get_form_shortcode( array( \'id\' => ' + formId + ' ) ); ?>'
			}
		];

		const filterArgs = { formId, formKey };
		examples = wp.hooks.applyFilters( 'frmEmbedFormExamples', examples, filterArgs );

		return examples;
	}

	function getEmbedExample({ label, example, link, linkLabel }) {
		let unique, element, labelElement, exampleElement, linkElement;

		unique = getAutoId();
		element = div();

		labelElement = getLabel( label );
		labelElement.id = 'frm_embed_example_label_' + unique;
		element.appendChild( labelElement );

		if ( example.length > 80 ) {
			exampleElement = tag( 'textarea' );
		} else {
			exampleElement = tag( 'input' );
			exampleElement.type = 'text';
		}

		exampleElement.id = 'frm_embed_example_' + unique;
		exampleElement.className = 'frm_embed_example';
		exampleElement.value = example;
		exampleElement.readOnly = true;
		exampleElement.setAttribute( 'tabindex', -1 );

		if ( 'undefined' !== typeof link && 'undefined' !== typeof linkLabel ) {
			linkElement = tag( 'a' );
			linkElement.href = link;
			linkElement.textContent = linkLabel;
			linkElement.setAttribute( 'target', '_blank' );
			element.appendChild( linkElement );
		}

		element.appendChild( exampleElement );
		element.appendChild( getCopyIcon( label ) );

		return element;
	}

	function getLabel( text ) {
		const args = { text };
		return tag( 'label', args );
	}

	function getCopyIcon( label ) {
		const icon = svg({ href: '#frm_clone_icon' });
		icon.id = 'frm_copy_embed_' + getAutoId();
		icon.setAttribute( 'tabindex', 0 );
		icon.setAttribute( 'role', 'button' );
		/* translators: %s: Example type (ie. WordPress shortcode, API Form script) */
		icon.setAttribute( 'aria-label', __( 'Copy %s', 'formidable' ).replace( '%s', label ) );
		icon.addEventListener(
			'click',
			() => copyExampleToClipboard( icon.parentNode.querySelector( '.frm_embed_example' ) )
		);
		return icon;
	}

	function copyExampleToClipboard( example ) {
		if ( navigator.clipboard ) {
			navigator.clipboard.writeText( example.value ).then( handleCopySuccess );
			return;
		}

		let copySuccess;

		example.focus();
		example.select();
		example.setSelectionRange( 0, 99999 );

		try {
			copySuccess = document.execCommand( 'copy' );
		} catch ( error ) {
			copySuccess = false;
		}

		if ( copySuccess ) {
			handleCopySuccess();
		}

		return copySuccess;
	}

	function handleCopySuccess() {
		speak( __( 'Successfully copied embed example', 'formidable' ) );
	}

	function speak( message ) {
		const element = document.createElement( 'div' );
		const id = 'speak-' + getAutoId();

		element.setAttribute( 'aria-live', 'assertive' );
		element.setAttribute( 'id', id );
		element.className = 'frm_screen_reader frm_hidden';
		element.textContent = message;
		document.body.appendChild( element );

		setTimeout( () => document.body.removeChild( element ), 1000 );
	}

	/**
	 * Get a unique id that automatically increments with every function call.
	 * Can be used for any UI that requires a unique id.
	 * Not to be used in data.
	 *
	 * @returns {integer}
	 */
	function getAutoId() {
		return ++autoId;
	}
}() );
