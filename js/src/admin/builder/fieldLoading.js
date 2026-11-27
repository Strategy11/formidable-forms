const { UNRENDERED_FIELD_SELECTOR } = require( '../fieldPreviewSelectors' );
const { queueFieldPreview, renderFieldPreviewNow } = require( '../fieldPreviewQueue' );
const { addFieldSettingsMetadata, cacheFieldSettings } = require( '../deferredFieldSettings' );
const { observeFieldSettings, startFieldSettingsQueue, waitForAwaitedFieldSettings, whenFieldSettingsLoaded } = require( '../fieldSettingsLoader' );
const { renderFieldPreviews, getFieldsWithLogicOn, PENDING_PREVIEW_CLASS } = require( '../fieldPreviews' );
const { processFieldLoadBatch } = require( '../fieldLoadBatch' );
const { getFieldLoadBatch } = require( '../fieldLoadWeights' );
const fieldHtmlTemplate = document.createElement( 'template' );

module.exports = function createFieldLoading( {
	deleteTooltips,
	ensureFieldSettings,
	fieldsAwaitingSettings,
	fieldsContainer,
	initiateMultiselect,
	maybeHideQuantityProductFieldOption,
	moveFieldSettings,
	prepareLoadedFieldMarkup,
	renumberPageBreaks,
}, builder ) {
	const { span } = frmDom;
	const { __, _n, sprintf } = wp.i18n;
	/**
	 * How many fields to ask for in a single frm_load_field request.
	 *
	 * A field costs about the same to render whether it arrives on its own or with others, so this
	 * mostly decides how many times the browser pays for loading WordPress again. Fewer, larger
	 * requests finish sooner, at the cost of a slightly longer wait for the first fields to appear.
	 */
	const FIELD_LOAD_BATCH_SIZE = 40;

	/**
	 * How many field batches may be downloading or rendering at once.
	 *
	 * Responses are safe to arrive in any order because every field replaces its own placeholder by
	 * id, and the placeholders are already sitting in the page in the right order.
	 */
	const FIELD_LOAD_CONCURRENCY = 3;

	let fieldLoadStarted = false;

	// Fields whose settings failed to load during the last save attempt.
	const fieldsFailedOnSave = new Set();

	let placeholderSpinnerObserver;

	/**
	 * Give each field placeholder a spinner only while it is in the viewport.
	 *
	 * A long form can have hundreds of placeholders, and most of them are swapped for the real
	 * field before anyone scrolls to them, so a placeholder that is never seen never gets one.
	 * Remove spinners that leave the viewport so their animations do not keep running offscreen.
	 * The placeholder holds the spinner's space, so adding or removing it does not move fields.
	 *
	 * @since 7.0
	 *
	 * @return {void}
	 */
	function observeFieldPlaceholders() {
		const placeholders = fieldsContainer.querySelectorAll( '.frm_field_loading' );
		if ( ! placeholders.length ) {
			return;
		}

		placeholderSpinnerObserver = new IntersectionObserver( handlePlaceholderIntersections );
		placeholders.forEach( placeholder => placeholderSpinnerObserver.observe( placeholder ) );
	}

	/**
	 * @since 7.0
	 *
	 * @param {IntersectionObserverEntry[]} entries The placeholders that moved into or out of view.
	 * @return {void}
	 */
	function handlePlaceholderIntersections( entries ) {
		entries.forEach(
			( { target, isIntersecting } ) => {
				if ( ! isIntersecting ) {
					target.querySelector( '.frm_visible_spinner' )?.remove();
					return;
				}

				// A placeholder that failed to load shows an error message instead.
				if ( target.hasChildNodes() ) {
					return;
				}

				// eslint-disable-next-line formidable/prefer-document-fragment -- each spinner goes into a different placeholder
				target.append( span( { className: 'frm-wait frm_visible_spinner' } ) );
			}
		);
	}

	/**
	 * Start as many field load requests as the concurrency limit allows, and finish up once the
	 * last one has come back.
	 *
	 * Fields are flagged with frm_load_now as soon as a request claims them, so the search for the
	 * next batch never hands the same field to two requests.
	 */
	function fillFieldLoadQueue() {
		while ( builder.state.activeFieldLoadRequests < FIELD_LOAD_CONCURRENCY ) {
			const nextField = fieldsContainer.querySelector( '.frm_field_loading:not(.frm_load_now)' );
			if ( ! nextField ) {
				break;
			}

			loadFields( nextField );
		}

		// Only of interest on a form that actually loads fields over ajax. buildInit covers the rest.
		if ( fieldLoadStarted && 0 === builder.state.activeFieldLoadRequests ) {
			afterAllFieldsLoad();
		}
	}

	/**
	 * Claim the next batch of placeholders and ask the server to render them.
	 *
	 * Only the field ids go up. The server already has the fields, so sending their data back to
	 * it would mean the whole form travelled down to the page and straight back up again.
	 *
	 * @param {HTMLElement} thisField The first placeholder in the batch.
	 * @return {void}
	 */
	function loadFields( thisField ) {
		const candidates = [];
		let candidate = thisField;
		while ( candidate && candidates.length < FIELD_LOAD_BATCH_SIZE ) {
			if ( candidate.classList.contains( 'frm_field_loading' ) && ! candidate.classList.contains( 'frm_load_now' ) ) {
				candidates.push( candidate.dataset.fid );
			}
			candidate = getNextField( candidate );
		}
		const fieldIds = getFieldLoadBatch( candidates, FIELD_LOAD_BATCH_SIZE );
		fieldIds.forEach( fieldId => document.getElementById( `frm_field_id_${ fieldId }` ).classList.add( 'frm_load_now' ) );

		if ( ! fieldIds.length ) {
			return;
		}

		++builder.state.activeFieldLoadRequestsldLoadRequests;
		fieldLoadStarted = true;

		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_load_field',
				field_ids: fieldIds,
				form_id: builder.state.thisFormId,
				// Only acknowledge received definitions so concurrent batches remain independent.
				known_tooltips: Object.keys( frm_admin_js.tooltips || {} ).join( ',' ),
				known_select_options: Object.keys( frm_admin_js.selectOptions || {} ).join( ',' ),
				defer_settings: 1,
				nonce: frmGlobal.nonce
			},
			success: async response => {
				try {
					await handleAjaxLoadFieldSuccess( response, fieldIds );
				} finally {
					completeFieldLoadRequest();
				}
			},
			error: () => {
				showFieldLoadError( fieldIds );
				completeFieldLoadRequest();
			}
		} );
	}

	function getNextField( field ) {
		if ( field.nextElementSibling ) {
			return field.nextElementSibling;
		}
		return field.parentNode?.closest( '.frm_field_box' )?.nextElementSibling?.querySelector( '.form-field' );
	}

	/**
	 * Release a queue slot only after its fields and their controls are ready.
	 *
	 * @since 7.0
	 * @return {void}
	 */
	function completeFieldLoadRequest() {
		--builder.state.activeFieldLoadRequestsldLoadRequests;
		fillFieldLoadQueue();
	}

	/**
	 * Keep failed placeholders claimed so they are not automatically requested again.
	 *
	 * @since 7.0
	 * @param {string[]} fieldIds The fields belonging to the failed request.
	 * @return {void}
	 */
	function showFieldLoadError( fieldIds ) {
		fieldIds.forEach( fieldId => {
			const field = document.getElementById( `frm_field_id_${ fieldId }` );
			if ( field?.classList.contains( 'frm_field_loading' ) ) {
				placeholderSpinnerObserver?.unobserve( field );
				field.textContent = __( 'Unable to load field.', 'formidable' );
			}
		} );
	}

	/**
	 * Swap placeholders in short slices while keeping each slice's controls ready for input.
	 *
	 * @param {string}   response A JSON object of field HTML and shared option and tooltip maps.
	 * @param {string[]} fieldIds The fields requested in this batch.
	 * @return {Promise<void>|void} Resolves after the last slice has initialized.
	 */
	function handleAjaxLoadFieldSuccess( response, fieldIds ) {
		let data;
		try {
			data = JSON.parse( response );
		} catch {
			showFieldLoadError( fieldIds );
			return;
		}
		if ( ! data || typeof data !== 'object' || Array.isArray( data ) ) {
			showFieldLoadError( fieldIds );
			return;
		}

		const { tooltips, selectOptions, ...loadedFields } = data;
		if ( selectOptions ) {
			frm_admin_js.selectOptions = { ...frm_admin_js.selectOptions, ...selectOptions };
		}
		if ( tooltips && window.frm_admin_js ) {
			frm_admin_js.tooltips = { ...frm_admin_js.tooltips, ...tooltips };
		}

		return processFieldLoadBatch(
			Object.entries( loadedFields ),
			queueLoadedFieldPreview,
			insertLoadedFieldSlice,
			initializeLoadedFieldSlice
		);
	}

	/**
	 * Cache compatible AJAX previews until their placeholders approach the viewport.
	 *
	 * Specialized fields keep their existing initialization contract.
	 *
	 * @since 7.1
	 * @param {Array} entry The field ID and its rendered HTML and settings metadata.
	 * @return {Object|null} A field to insert immediately, or null when queued or deleted.
	 */
	function queueLoadedFieldPreview( entry ) {
		const [ key, field ] = entry;
		if ( ! field.settingsMeta ) {
			return renderLoadedField( entry );
		}
		const placeholder = document.getElementById( `frm_field_id_${ key }` );
		if ( ! placeholder ) {
			return null;
		}
		cacheFieldSettings( key, field.settingsHtml );
		addFieldSettingsMetadata( placeholder, key, field.settingsMeta );
		placeholderSpinnerObserver?.unobserve( placeholder );
		placeholder.querySelector( '.frm_visible_spinner' )?.remove();
		placeholder.classList.remove( 'frm_field_loading' );
		placeholder.classList.add( PENDING_PREVIEW_CLASS );
		placeholder.dataset.type = field.type;
		queueFieldPreview( placeholder, () => {
			const rendered = renderLoadedField( entry );
			insertLoadedFieldSlice( [ rendered ] );
			return rendered;
		}, initializeLoadedFieldSlice );
		return null;
	}

	/**
	 * Replace one placeholder and prepare its builder interactions.
	 *
	 * @since 7.0
	 * @param {Array} entry The field ID and its rendered HTML and type.
	 * @return {Object|null} The detached field, or null if its placeholder was deleted.
	 */
	function renderLoadedField( entry ) {
		const [ key, field ] = entry;
		const oldField = document.getElementById( `frm_field_id_${ key }` );
		if ( ! oldField ) {
			return null;
		}
		placeholderSpinnerObserver?.unobserve( oldField );
		builder.state.dragDropObserver.unobserve( oldField );
		builder.state.dragDropAttachers.delete( oldField );
		cacheFieldSettings( key, field.settingsHtml );
		fieldHtmlTemplate.innerHTML = field.html;
		const element = fieldHtmlTemplate.content.firstElementChild;
		if ( ! element ) {
			oldField.remove();
			return null;
		}
		const metadata = oldField.querySelector( ':scope > .frm-deferred-settings-meta' );
		if ( metadata ) {
			const classes = metadata.querySelector( '.frm_classes' )?.value;
			if ( classes !== undefined && classes !== field.settingsMeta.classes ) {
				const previous = ( field.settingsMeta.classes || '' ).split( /\s+/ ).filter( Boolean );
				const current = classes.split( /\s+/ ).filter( Boolean );
				element.classList.remove( ...previous );
				element.classList.add( ...current );
			}
			element.append( metadata );
		} else if ( field.settingsMeta ) {
			addFieldSettingsMetadata( element, key, field.settingsMeta );
		}
		if ( oldField.classList.contains( 'selected' ) ) {
			element.classList.add( 'selected' );
		}
		prepareLoadedFieldMarkup( element );
		const fragment = document.createDocumentFragment();
		fragment.append( fieldHtmlTemplate.content );
		return { placeholder: oldField, fragment, id: key, type: field.type, element };
	}

	/**
	 * Insert adjacent fields together when their placeholders share a parent.
	 *
	 * @since 7.1
	 * @param {Array} fields The detached fields and their placeholders.
	 * @return {void}
	 */
	function insertLoadedFieldSlice( fields ) {
		let group = [];
		const insertGroup = () => {
			if ( ! group.length ) {
				return;
			}

			const fragment = document.createDocumentFragment();
			// eslint-disable-next-line formidable/prefer-document-fragment -- move each detached field into the insertion fragment
			group.forEach( field => fragment.append( field.fragment ) );
			group[ 0 ].placeholder.replaceWith( fragment );
			group.slice( 1 ).forEach( field => field.placeholder.remove() );
			group.forEach( field => {
				if ( field.element.classList.contains( 'edit_field_type_divider' ) ) {
					field.element.querySelectorAll( 'ul.frm_sorting' ).forEach( builder.dragDrop.setupSortableList );
				}
				builder.dragDrop.lazyMakeDraggable( field.element );
			} );
			group = [];
		};

		fields.forEach( field => {
			if ( ! field ) {
				insertGroup();
				return;
			}

			const previous = group[ group.length - 1 ];
			if ( previous && ( previous.placeholder.parentElement !== field.placeholder.parentElement || previous.placeholder.nextElementSibling !== field.placeholder ) ) {
				insertGroup();
			}
			group.push( field );
		} );
		insertGroup();
	}

	/**
	 * Run a change to the conditional logic that points at a field once those panels have loaded.
	 *
	 * Settings panels far from the viewport load only when needed. A field whose logic uses this
	 * one gets its settings first, so a renamed choice reaches its logic and is saved with it.
	 *
	 * @since x.x
	 * @param {string}   fieldId The field whose choices changed.
	 * @param {Function} update  Reads and changes the loaded logic rows.
	 * @return {void}
	 */
	function whenLogicSettingsLoaded( fieldId, update ) {
		// Saving waits for these, and they move into the save form when they arrive. See handleLoadedFieldSettings.
		whenFieldSettingsLoaded( getFieldsWithLogicOn( fieldId ), fieldsAwaitingSettings, update );
	}

	/**
	 * Prepare the previews drawn from page data, and start loading their settings.
	 *
	 * @since 7.1
	 * @return {void}
	 */
	function initBrowserPreviewFields() {
		if ( ! builder.state.browserPreviewFieldIds.length ) {
			return;
		}

		renderFieldPreviews( replaceFieldPreviewPlaceholder, initializeLoadedFieldSlice );

		// Previews still loading over ajax are on screen, so they go first. See afterAllFieldsLoad.
		if ( ! fieldLoadStarted ) {
			startFieldSettingsQueue();
		}
	}

	/**
	 * Prepare a preview drawn from page data as it takes its placeholder's place.
	 *
	 * @since 7.1
	 * @param {HTMLElement} placeholder The empty list item being replaced.
	 * @param {HTMLElement} field       The drawn preview.
	 * @return {Object} The field, in the shape initializeLoadedFieldSlice expects.
	 */
	function replaceFieldPreviewPlaceholder( placeholder, field ) {
		observeFieldSettings( field );
		builder.state.dragDropObserver?.unobserve( placeholder );
		builder.state.dragDropAttachers.delete( placeholder );
		prepareLoadedFieldMarkup( field );
		builder.dragDrop.lazyMakeDraggable( field, '.frm-move' );
		return { id: field.dataset.fid, type: field.dataset.type, element: field };
	}

	/**
	 * Apply settings that something asked for before they arrived.
	 *
	 * A selected field shows its settings. Any other field moves its settings into the save form,
	 * so an edit made in the meantime, like a new field order or layout class, is saved.
	 *
	 * @since 7.1
	 * @param {string[]} fieldIds The fields whose settings were just cached.
	 * @return {void}
	 */
	function handleLoadedFieldSettings( fieldIds ) {
		fieldIds.forEach( fieldId => {
			if ( ! fieldsAwaitingSettings.delete( fieldId ) ) {
				return;
			}

			const field = document.getElementById( `frm_field_id_${ fieldId }` );
			if ( field?.classList.contains( 'selected' ) ) {
				builder.fieldEditor.showFieldOptions( field );
				return;
			}

			moveFieldSettings( ensureFieldSettings( fieldId ) );
		} );
	}

	/**
	 * Hold a save until the settings that edits are waiting on have arrived.
	 *
	 * @since 7.1
	 * @param {Function}    save   The save handler to call again.
	 * @param {HTMLElement} button The save button that was clicked.
	 * @return {boolean} True if the save will run later.
	 */
	function deferSaveForFieldSettings( save, button ) {
		if ( ! fieldsAwaitingSettings.size ) {
			return false;
		}

		button.classList.add( 'frm_loading_button' );
		waitForAwaitedFieldSettings( fieldsAwaitingSettings ).then( failedFieldIds => {
			if ( ! failedFieldIds.length ) {
				fieldsFailedOnSave.clear();
			} else if ( ! confirmSaveWithoutFieldSettings( failedFieldIds, button ) ) {
				return;
			}
			button.classList.remove( 'frm_loading_button' );
			save.call( button );
		} ).catch( error => {
			console.error( error );
			builder.fieldEditor.showFormSaveFailed( button, __( 'The form could not be saved. Please try again.', 'formidable' ) );
		} );
		return true;
	}

	/**
	 * Decide whether to save while some field settings still fail to load.
	 *
	 * Edits made before these settings loaded are only kept beside the preview, so saving drops them.
	 * The first failed attempt keeps them and asks to try again. When the same fields fail again,
	 * the settings may never load, so ask before saving without them instead of blocking every save.
	 *
	 * @since 7.1
	 * @param {string[]}    failedFieldIds The fields whose settings failed to load.
	 * @param {HTMLElement} button         The save button that was clicked.
	 * @return {boolean} True to save without the failed fields.
	 */
	function confirmSaveWithoutFieldSettings( failedFieldIds, button ) {
		const failedBefore = failedFieldIds.some( fieldId => fieldsFailedOnSave.has( fieldId ) );
		fieldsFailedOnSave.clear();
		failedFieldIds.forEach( fieldId => fieldsFailedOnSave.add( fieldId ) );

		if ( ! failedBefore ) {
			failedFieldIds.forEach( fieldId => fieldsAwaitingSettings.add( fieldId ) );
			builder.fieldEditor.showFormSaveFailed( button, __( 'Some field settings could not be loaded, so the form was not saved. Your changes are still here. Please try saving again.', 'formidable' ) );
			return false;
		}

		const message = sprintf(
			/* translators: %d: The number of fields whose settings could not be loaded. */
			_n(
				'Save anyway? Unsaved changes to %d field whose settings could not be loaded will be lost.',
				'Save anyway? Unsaved changes to %d fields whose settings could not be loaded will be lost.',
				failedFieldIds.length,
				'formidable'
			),
			failedFieldIds.length
		);

		if ( window.confirm( message ) ) {
			return true;
		}

		failedFieldIds.forEach( fieldId => fieldsAwaitingSettings.add( fieldId ) );
		button.classList.remove( 'frm_loading_form', 'frm_loading_button' );
		button.setAttribute( 'aria-busy', 'false' );
		return false;
	}

	/**
	 * Prepare settings before delegated preview handlers read or modify them.
	 *
	 * @since 7.0
	 * @param {Event} event The preview interaction.
	 * @return {void}
	 */
	function prepareInteractedFieldSettings( event ) {
		const field = event.target.closest( 'li.form-field' );
		if ( field && ! field.matches( UNRENDERED_FIELD_SELECTOR ) ) {
			ensureFieldSettings( field.dataset.fid );
		}
	}

	/**
	 * Stop placeholder mouse events before delegated selection and hover handlers run.
	 *
	 * @since 7.0
	 * @param {MouseEvent} event The interaction inside the builder fields container.
	 * @return {void}
	 */
	function ignoreLoadingFieldMouseEvent( event ) {
		const pendingPreview = event.type === 'click' && event.target.closest( `.${ PENDING_PREVIEW_CLASS }` );
		if ( pendingPreview ) {
			event.preventDefault();
			event.stopImmediatePropagation();
			const rendered = renderFieldPreviewNow( pendingPreview.dataset.fid );
			rendered?.element.click();
			return;
		}
		// A drag helper lets the pointer reach placeholders, so let jQuery UI track the drag.
		if ( document.body.classList.contains( 'frm-dragging' ) || ! event.target.closest( UNRENDERED_FIELD_SELECTOR ) ) {
			return;
		}

		// Clear the previous row before stopping the delegated hover cleanup.
		if ( false !== builder.fieldActions.maybeRemoveGroupHoverTarget() ) {
			deleteTooltips();
		}

		event.preventDefault();
		event.stopImmediatePropagation();
	}

	/**
	 * Initialize only the fields inserted in this slice before yielding to user input.
	 *
	 * @since 7.0
	 * @param {Array} fields The inserted fields, including null for deleted placeholders.
	 * @return {void}
	 */
	function initializeLoadedFieldSlice( fields ) {
		const loadedFields = fields.filter( Boolean );
		if ( ! loadedFields.length ) {
			return;
		}
		initiateMultiselect( loadedFields.map( field => field.element ) );

		if ( builder.state.dragState.dragging ) {
			builder.dragDrop.refreshDroppableOffsets();
		}

		const loadedEvent = new Event( 'frm_ajax_loaded_field', { bubbles: false } );
		loadedEvent.frmFields = loadedFields.map( ( { id, type } ) => ( { id, type } ) );
		document.dispatchEvent( loadedEvent );
	}

	/**
	 * Handle the things that look at the form as a whole, once every field has arrived.
	 *
	 * These used to run after each batch, so a form loading in n batches did n passes over a
	 * steadily growing page for a result only the last pass could get right.
	 */
	function afterAllFieldsLoad() {
		placeholderSpinnerObserver?.disconnect();
		startFieldSettingsQueue();
		renumberPageBreaks();
		maybeHideQuantityProductFieldOption();
		builder.fieldOptions.scheduleBulkOptionsOverlay();
	}

	return {
		deferSaveForFieldSettings,
		fillFieldLoadQueue,
		handleLoadedFieldSettings,
		ignoreLoadingFieldMouseEvent,
		initBrowserPreviewFields,
		observeFieldPlaceholders,
		prepareInteractedFieldSettings,
		whenLogicSettingsLoaded,
	};
};
