( function() {
	/** globals ajaxurl, wp, frmDom */

	if ( 'undefined' === typeof ajaxurl || 'undefined' === typeof wp || 'undefined' === typeof frmDom ) {
		return;
	}

	const __ = wp.i18n.__;
	const { div, tag } = frmDom;
	const { maybeCreateModal, footerButton } = frmDom.modal;

	let autoId = 0;

	initEmbedFormModal();

	function initEmbedFormModal() {
		document.addEventListener( 'click', listenForFormEmbedClick );
	}

	function listenForFormEmbedClick( event ) {
		let clicked = false;

		const element = event.target;
		const tag = element.tagName.toLowerCase();

		switch ( tag ) {
			case 'a':
				clicked = 'frm-embed-action' === element.id || element.classList.contains( 'frm-embed-form' );
				break;

			case 'svg':
				clicked = element.parentNode.classList.contains( 'frm-embed-form' );
				break;
		}

		if ( clicked ) {
			event.preventDefault();

			const [ formId, formKey ] = wp.hooks.applyFilters( 'frmBeforeEmbedFormModal', [ 0, 0 ], element );
			if ( ! formId || ! formKey ) {
				return;
			}

			openFormEmbedModal( formId, formKey );
		}
	}

	function openFormEmbedModal( formId, formKey ) {
		const modal = maybeCreateModal(
			'frm_form_embed_modal',
			{
				title: __( 'Embed form', 'formidable' ),
				content: getEmbedFormModalOptions( formId, formKey ),
				footer: getFormEmbedFooter( formId, formKey )
			}
		);
		modal.classList.add( 'frm_common_modal' );
		modal.classList.remove( 'frm-on-page-2' );

		const $modal = jQuery( modal );
		offsetModalY( $modal, '50px' );
		$modal.parent().addClass( 'frm-embed-form-modal-wrapper' );
	}

	function getFormEmbedFooter( formId, formKey ) {
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
				openFormEmbedModal( formId, formKey );
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

	function getEmbedFormModalOptions( formId, formKey ) {
		const content = div({ className: 'frm_embed_form_content frm_wrap' });

		const options = [
			{
				icon: 'frm_select_existing_page_icon',
				label: __( 'Select existing page', 'formidable' ),
				description: __( 'Embed your form into an existing page.', 'formidable' ),
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
						success: function( response ) {
							if ( 'object' === typeof response && 'string' === typeof response.html ) {
								content.classList.remove( 'frm-loading-page-options' );
								content.innerHTML = '';

								const title = getLabel( __( 'Select the page you want to embed your form into.', 'formidable' ) );
								title.setAttribute( 'for', 'frm_page_dropdown' );
								content.appendChild( title );

								let editPageUrl;

								const modal = document.getElementById( 'frm_form_embed_modal' );
								doneButton = modal.querySelector( '.frm_modal_footer .button-primary' );
								doneButton.classList.remove( 'dismiss' );
								doneButton.textContent = __( 'Insert Form', 'formidable' );
								doneButton.addEventListener(
									'click',
									function( event ) {
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
								);

								const dropdownWrapper = div();
								dropdownWrapper.innerHTML = response.html;
								content.appendChild( dropdownWrapper );
								editPageUrl = response.edit_page_url + '&frmForm=' + formId;
								frmDom.autocomplete.initSelectionAutocomplete();
							}
						}
					});
				}
			},
			{
				icon: 'frm_create_new_page_icon',
				label: __( 'Create new page', 'formidable' ),
				description: __( 'Put your form on a newly created page.', 'formidable' ),
				callback: () => {
					content.innerHTML = '';

					const wrapper = div({ className: 'field-group' });
					const form = tag( 'form' );

					const createPageWithShortcode = () => {
						jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {
								action: 'frm_create_page_with_shortcode',
								form_id: formId,
								name: input.value,
								nonce: frmGlobal.nonce
							},
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

					const modal = document.getElementById( 'frm_form_embed_modal' );
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
				icon: 'frm_insert_manually_icon',
				label: __( 'Insert manually', 'formidable' ),
				description: __( 'Use WP shortcodes or PHP code to put the form in any place.', 'formidable' ),
				callback: () => {
					content.innerHTML = '';
					getEmbedFormManualExamples( formId, formKey ).forEach( example => content.appendChild( getEmbedExample( example ) ) );
				}
			}
		];

		options.forEach(
			option => content.appendChild( getEmbedFormModalOption( option ) )
		);

		return content;
	}

	function getEmbedFormModalOption({ icon, label, description, callback }) {
		const output = div();
		output.appendChild( wrapEmbedFormModalOptionIcon( icon ) );
		output.className = 'frm-embed-modal-option';
		output.setAttribute( 'tabindex', 0 );
		output.setAttribute( 'role', 'button' );

		const textWrapper = div();
		textWrapper.appendChild( getLabel( label ) );
		textWrapper.appendChild( div({ text: description }) );
		output.appendChild( textWrapper );

		output.addEventListener(
			'click',
			function() {
				document.getElementById( 'frm_form_embed_modal' ).classList.add( 'frm-on-page-2' );
				callback();
			}
		);
		return output;
	}

	function wrapEmbedFormModalOptionIcon( sourceIconId ) {
		const clone = document.getElementById( sourceIconId ).cloneNode( true );
		const wrapper = div({ child: clone });
		wrapper.className = 'frm-embed-form-icon-wrapper';
		return wrapper;
	}

	function getEmbedFormManualExamples( formId, formKey ) {
		let examples = [
			{
				label: __( 'WordPress shortcode', 'formidable' ),
				example: '[formidable id=' + formId + ' title=true description=true]',
				link: 'https://formidableforms.com/knowledgebase/publish-a-form/#kb-insert-the-shortcode-manually',
				linkLabel: __( 'How to use shortcodes in WordPress', 'formidable' )
			},
			{
				label: __( 'Use PHP code', 'formidable' ),
				example: '<?php echo FrmFormsController::get_form_shortcode( array( \'id\' => ' + formId + ', \'title\' => true, \'description\' => true ) ); ?>'
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
		const label = document.createElement( 'label' );
		label.textContent = text;
		return label;
	}

	function getCopyIcon( label ) {
		const icon = document.getElementById( 'frm_copy_embed_form_icon' );
		let clone = icon.cloneNode( true );
		clone.id = 'frm_copy_embed_' + getAutoId();
		clone.setAttribute( 'tabindex', 0 );
		clone.setAttribute( 'role', 'button' );
		/* translators: %s: Example type (ie. WordPress shortcode, API Form script) */
		clone.setAttribute( 'aria-label', __( 'Copy %s', 'formidable' ).replace( '%s', label ) );
		clone.addEventListener(
			'click',
			() => copyExampleToClipboard( clone.parentNode.querySelector( '.frm_embed_example' ) )
		);
		return clone;
	}

	function copyExampleToClipboard( example ) {
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
			speak( __( 'Successfully copied embed example', 'formidable' ) );
		}

		return copySuccess;
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
