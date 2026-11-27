wp.hooks.addAction( 'frmAdminImportExportInit', 'formidable', function( searchContent, initiateMultiselect ) {
	const migration = {};

	/* Import/Export page */

	function startFormMigration( event ) {
		event.preventDefault();

		const checkedBoxes = jQuery( event.target ).find( 'input:checked' );
		if ( ! checkedBoxes.length ) {
			return;
		}

		const ids = [];
		checkedBoxes.each( function( i ) {
			ids[ i ] = this.value;
		} );

		// Begin the import process.
		importForms( ids, event.target );
	}

	/**
	 * Begins the process of importing the forms.
	 * @param {Array}       forms      The form IDs to import.
	 * @param {HTMLElement} targetForm The form element that triggered the import.
	 */
	function importForms( forms, targetForm ) {
		// Hide the form select section.
		const $form = jQuery( targetForm );
		const $processSettings = $form.next( '.frm-importer-process' );

		// Display total number of forms we have to import.
		$processSettings.find( '.form-total' ).text( forms.length );
		$processSettings.find( '.form-current' ).text( '1' );

		$form.hide();

		// Show processing status.
		// '.process-completed' might have been shown earlier during a previous import, so hide now.
		$processSettings.find( '.process-completed' ).hide();
		$processSettings.show();

		// Create global import queue.
		migration.importQueue = forms;
		migration.imported = 0;

		// Import the first form in the queue.
		importForm( $processSettings );
	}

	/**
	 * Imports a single form from the import queue.
	 * @param {jQuery} $processSettings The jQuery wrapped process settings element.
	 */
	function importForm( $processSettings ) {
		const formID = migration.importQueue[ 0 ];
		const provider = jQuery( '#welcome-panel' ).find( 'input[name="slug"]' ).val();
		const data = {
			action: `frm_import_${ provider }`,
			form_id: formID,
			nonce: frmGlobal.nonce
		};

		// Trigger AJAX import for this form.
		jQuery.post( ajaxurl, data, function( res ) {
			if ( res.success ) {
				let statusUpdate;

				if ( res.data.error ) {
					statusUpdate = `<p>${ res.data.name }: ${ res.data.msg }</p>`;
				} else {
					statusUpdate = `<p>Imported <a href="${ res.data.link }" target="_blank">${ res.data.name }</a></p>`;
				}

				$processSettings.find( '.status' ).prepend( statusUpdate );
				$processSettings.find( '.status' ).show();

				// Remove this form ID from the queue.
				migration.importQueue = jQuery.grep( migration.importQueue, function( value ) {
					return value != formID;
				} );
				migration.imported++;

				if ( migration.importQueue.length === 0 ) {
					$processSettings.find( '.process-count' ).hide();
					$processSettings.find( '.forms-completed' ).text( migration.imported );
					$processSettings.find( '.process-completed' ).show();
				} else {
					// Import next form in the queue.
					$processSettings.find( '.form-current' ).text( migration.imported + 1 );
					importForm( $processSettings );
				}
			}
		} );
	}

	function validateExport( e ) {
		/*jshint validthis:true */
		e.preventDefault();

		let status = false;
		const $exportForms = jQuery( 'input[name="frm_export_forms[]"]' );

		if ( ! jQuery( 'input[name="frm_export_forms[]"]:checked' ).val() ) {
			$exportForms.closest( '.frm-table-box' ).addClass( 'frm_blank_field' );
			status = 'stop';
		}

		const $exportType = jQuery( 'input[name="type[]"]' );
		if ( ! jQuery( 'input[name="type[]"]:checked' ).val() && $exportType.attr( 'type' ) === 'checkbox' ) {
			$exportType.closest( 'p' ).addClass( 'frm_blank_field' );
			status = 'stop';
		}

		if ( status === 'stop' ) {
			return false;
		}

		e.stopPropagation();
		this.submit();
	}

	function removeExportError() {
		/*jshint validthis:true */
		const $blankField = jQuery( this ).closest( '.frm_blank_field' );
		if ( $blankField === undefined ) {
			return;
		}

		const $thisName = this.name;
		if ( $thisName === 'type[]' && jQuery( 'input[name="type[]"]:checked' ).val() ) {
			$blankField.removeClass( 'frm_blank_field' );
		} else if ( $thisName === 'frm_export_forms[]' && jQuery( this ).val() ) {
			$blankField.removeClass( 'frm_blank_field' );
		}
	}

	function checkCSVExtension() {
		/*jshint validthis:true */
		const fileName = jQuery( this ).val();
		const re = /\.csv$/i;
		if ( fileName.match( re ) !== null ) {
			jQuery( '.show_csv' ).fadeIn();
		} else {
			jQuery( '.show_csv' ).fadeOut();
		}
	}

	function getExportOption() {
		const exportFormatSelect = document.querySelector( 'select[name="format"]' );
		if ( exportFormatSelect ) {
			return exportFormatSelect.value;
		}
		return '';
	}

	function exportTypeChanged( event ) {
		const { value } = event.target;
		showOrHideRepeaters( value );
		checkExportTypes.call( event.target );
		checkSelectedAllFormsCheckbox( value );
	}

	function checkSelectedAllFormsCheckbox( exportType ) {
		const selectAllCheckbox = document.getElementById( 'frm-export-select-all' );
		if ( exportType === 'csv' ) {
			selectAllCheckbox.checked = false;
			selectAllCheckbox.disabled = true;
		} else {
			selectAllCheckbox.disabled = false;
		}
	}

	function checkExportTypes() {
		/*jshint validthis:true */
		const $dropdown = jQuery( this );
		const $selected = $dropdown.find( ':selected' );
		const support = $selected.data( 'support' );

		const multiple = support.indexOf( '|' );
		jQuery( 'input[name="type[]"]' ).each( function() {
			this.checked = false;
			if ( support.includes( this.value ) ) {
				this.disabled = false;
				if ( multiple === -1 ) {
					this.checked = true;
				}
			} else {
				this.disabled = true;
			}
		} );

		if ( $dropdown.val() === 'csv' ) {
			jQuery( '.csv_opts' ).show();
			jQuery( '.xml_opts' ).hide();
		} else {
			jQuery( '.csv_opts' ).hide();
			jQuery( '.xml_opts' ).show();
		}

		const count = $selected.data( 'count' );
		const exportField = jQuery( 'input[name="frm_export_forms[]"]' );
		if ( count === 'single' ) {
			exportField.prop( 'multiple', false );
			exportField.prop( 'checked', false );
		} else {
			exportField.prop( 'multiple', true );
			exportField.prop( 'disabled', false );
		}
		$dropdown.trigger( 'change' );
	}

	function showOrHideRepeaters( exportOption ) {
		if ( exportOption === '' ) {
			return;
		}

		const repeaters = document.querySelectorAll( '.frm-is-repeater' );
		if ( ! repeaters.length ) {
			return;
		}

		if ( exportOption === 'csv' ) {
			repeaters.forEach( form => {
				form.classList.remove( 'frm_hidden' );
			} );
		} else {
			repeaters.forEach( form => {
				form.classList.add( 'frm_hidden' );
			} );
		}

		searchContent.call( document.querySelector( '.frm-auto-search' ) );
	}

	function preventMultipleExport() {
		const type = jQuery( 'select[name=format]' );
		const selected = type.find( ':selected' );
		const count = selected.data( 'count' );
		const exportField = jQuery( 'input[name="frm_export_forms[]"]' );

		if ( count === 'single' ) {
			// Disable all other fields to prevent multiple selections.
			if ( this.checked ) {
				exportField.prop( 'disabled', true );
				this.removeAttribute( 'disabled' );
			} else {
				exportField.prop( 'disabled', false );
			}
		} else {
			exportField.prop( 'disabled', false );
		}
	}

	jQuery( '.frm_form_importer' ).on( 'submit', startFormMigration );
	jQuery( document.getElementById( 'frm_export_xml' ) ).on( 'submit', validateExport );
	jQuery( '#frm_export_xml input, #frm_export_xml select' ).on( 'change', removeExportError );
	jQuery( 'input[name="frm_import_file"]' ).on( 'change', checkCSVExtension );
	document.querySelector( 'select[name="format"]' ).addEventListener( 'change', exportTypeChanged );

	jQuery( 'input[name="frm_export_forms[]"]' ).on( 'click', preventMultipleExport );
	initiateMultiselect();

	jQuery( '.frm-feature-banner .dismiss' ).on( 'click', function( event ) {
		event.preventDefault();
		jQuery.post( ajaxurl, {
			action: 'frm_dismiss_migrator',
			plugin: this.id,
			nonce: frmGlobal.nonce
		} );
		this.parentElement.remove();
	} );

	showOrHideRepeaters( getExportOption() );

	document.querySelector( '#frm-export-select-all' ).addEventListener( 'change', event => {
		document.querySelectorAll( '[name="frm_export_forms[]"]' ).forEach( cb => {
			cb.checked = event.target.checked;
		} );
	} );
} );

window.frmImportCsv = formID => {
	let urlVars = '';
	if ( typeof __FRMURLVARS !== 'undefined' ) {
		urlVars = __FRMURLVARS;
	}

	jQuery.ajax( {
		type: 'POST', url: ajaxurl,
		data: `action=frm_import_csv&nonce=${ frmGlobal.nonce }&frm_skip_cookie=1${ urlVars }`,
		success( count ) {
			const max = jQuery( '.frm_admin_progress_bar' ).attr( 'aria-valuemax' );
			const imported = max - count;
			const percent = ( imported / max ) * 100;
			jQuery( '.frm_admin_progress_bar' ).css( 'width', `${ percent }%` ).attr( 'aria-valuenow', imported );

			if ( parseInt( count, 10 ) > 0 ) {
				jQuery( '.frm_csv_remaining' ).html( count );
				frmImportCsv( formID );
			} else {
				jQuery( document.getElementById( 'frm_import_message' ) ).html( frm_admin_js.import_complete );
				setTimeout( function() {
					location.href = `?page=formidable-entries&frm_action=list&form=${ formID }&import-message=1`;
				}, 2000 );
			}
		}
	} );
};
