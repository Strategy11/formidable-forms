/**
 * Gated Content form action — add/remove item rows and type switching.
 *
 * Uses document-level event delegation so it works both for actions already on
 * the page and for actions loaded dynamically via AJAX (frm_form_action_fill /
 * frm_added_form_action).
 *
 * Each item row stores its active type in a <select class="frm-gc-item-type">.
 * Type-specific settings live in <div class="frm-gc-type-settings" data-type="…">
 * children. Only the active type's settings div is visible and has its [id]
 * select named — preventing duplicate field names on form submit.
 *
 * @since x.x
 */
( function() {
	/**
	 * Show the active type's settings panel and assign field names.
	 * Hide and strip names from all other type panels.
	 *
	 * Handles any number of `data-frm-gc-field` elements per type panel,
	 * assigning `name="${base}[${fieldKey}]"` when active and removing it when not.
	 *
	 * @param {HTMLElement} itemRow - A .frm_gc_item_row element.
	 */
	function activateType( itemRow ) {
		const typeSelect = itemRow.querySelector( '.frm-gc-item-type' );
		const activeType = typeSelect.value;

		// Derive the item base (e.g. "frm_form_action[X][post_content][items][2]")
		// from the type select's name by stripping the trailing "[type]" segment.
		const base = typeSelect.name.replace( /\[type\]$/, '' );

		itemRow.querySelectorAll( '.frm-gc-type-settings' ).forEach( typeDiv => {
			const isActive = typeDiv.dataset.type === activeType;
			typeDiv.toggleAttribute( 'hidden', ! isActive );

			// Assign or remove names for all fields in this type panel.
			typeDiv.querySelectorAll( '[data-frm-gc-field]' ).forEach( field => {
				const fieldKey = field.dataset.frmGcField;
				if ( isActive ) {
					field.name = `${ base }[${ fieldKey }]`;
				} else {
					field.removeAttribute( 'name' );
				}
			} );
		} );
	}

	/**
	 * Re-index all item rows after an addition or removal.
	 *
	 * Keeps name and id attributes contiguous so the submitted PHP array has no
	 * gaps and for/id pairs remain unique. Called after every add or remove.
	 *
	 * @since x.x
	 *
	 * @param {HTMLElement} wrapper - The .frm_gated_content_settings element.
	 * @return {void}
	 */
	function reindexItems( wrapper ) {
		const addBtn = wrapper.querySelector( '.frm_gc_add_item' );
		const fieldBase = addBtn?.dataset.fieldNameBase ?? '';
		const rows = wrapper.querySelectorAll( '.frm_gc_item_row' );

		rows.forEach( ( row, idx ) => {
			const typeSelect = row.querySelector( '.frm-gc-item-type' );
			if ( typeSelect && fieldBase ) {
				typeSelect.name = `${ fieldBase }[${ idx }][type]`;
			}
			assignItemIds( row, wrapper.id, idx );
			activateType( row );
		} );

		wrapper.dataset.itemCount = rows.length;
	}

	/**
	 * Assign id and for attributes to a cloned template row.
	 *
	 * Template labels use data-frm-gc-for="KEY" and selects use data-frm-gc-field="KEY"
	 * instead of for/id attributes to avoid duplicate IDs before cloning. This function
	 * assigns real id/for pairs using the wrapper ID and item index as a unique prefix.
	 *
	 * Handles any number of `data-frm-gc-field` elements per type panel so that
	 * Pro types with multiple selects (e.g. form_id + id for frm_file) work correctly.
	 *
	 * @param {HTMLElement} itemRow       - The .frm_gc_item_row element already in the DOM.
	 * @param {string}      wrapperBaseId - The wrapper element's id attribute value.
	 * @param {number}      idx           - Zero-based item index used for unique IDs.
	 */
	function assignItemIds( itemRow, wrapperBaseId, idx ) {
		// Type select.
		const typeSelect = itemRow.querySelector( '[data-frm-gc-field="type"]' );
		if ( typeSelect ) {
			typeSelect.id = `${ wrapperBaseId }_type_${ idx }`;
			const typeLabel = itemRow.querySelector( '[data-frm-gc-for="type"]' );
			if ( typeLabel ) {
				typeLabel.htmlFor = typeSelect.id;
			}
		}

		// Per-type fields — each type panel can have multiple data-frm-gc-field elements.
		itemRow.querySelectorAll( '.frm-gc-type-settings' ).forEach( typeDiv => {
			const type = typeDiv.dataset.type;
			typeDiv.querySelectorAll( '[data-frm-gc-field]' ).forEach( field => {
				const fieldKey = field.dataset.frmGcField;
				field.id = `${ wrapperBaseId }_${ fieldKey }_${ type }_${ idx }`;
				const label = typeDiv.querySelector( `[data-frm-gc-for="${ fieldKey }"]` );
				if ( label ) {
					label.htmlFor = field.id;
				}
			} );
		} );
	}

	/**
	 * Filter the file field select to show only options matching the selected form.
	 *
	 * When a .frm-gc-file-form-select changes, all options in the sibling file-field
	 * select ([data-frm-gc-field="id"]) are shown or hidden based on their data-form-id.
	 * Options without data-form-id are always shown (e.g. the empty placeholder).
	 *
	 * @param {HTMLElement} formSelect - A .frm-gc-file-form-select element.
	 */
	function filterFileFields( formSelect ) {
		const typeDiv = formSelect.closest( '.frm-gc-type-settings' );
		const fieldSelect = typeDiv?.querySelector( '[data-frm-gc-field="id"]' );
		if ( ! fieldSelect ) {
			return;
		}

		const selectedFormId = formSelect.value;

		Array.from( fieldSelect.options ).forEach( option => {
			const optFormId = option.dataset.formId;
			if ( ! optFormId ) {
				// Placeholder option — always visible.
				option.style.display = '';
				return;
			}
			option.style.display = ! selectedFormId || optFormId !== selectedFormId ? 'none' : '';
		} );

		// If the currently selected field option is now hidden, reset the select.
		const selected = fieldSelect.options[ fieldSelect.selectedIndex ];
		if ( selected?.style.display === 'none' ) {
			fieldSelect.value = '';
		}
	}

	/**
	 * Transition the button to the "copied" state and revert after 1600 ms.
	 *
	 * Adds `.is-copied` so CSS cross-fades the copy↔check icons via scale+blur.
	 *
	 * @since x.x
	 *
	 * @param {HTMLElement} btn - The .frm_gc_copy_shortcode button element.
	 */
	function showCopied( btn ) {
		const originalLabel = btn.getAttribute( 'aria-label' );
		btn.classList.add( 'is-copied' );
		btn.setAttribute( 'aria-label', btn.dataset.copiedLabel || 'Copied!' );

		setTimeout( () => {
			btn.classList.remove( 'is-copied' );
			btn.setAttribute( 'aria-label', originalLabel );
		}, 1600 );
	}

	document.addEventListener( 'click', function( event ) {
		const addBtn = event.target.closest( '.frm_gc_add_item' );
		if ( addBtn ) {
			const wrapper = addBtn.closest( '.frm_gated_content_settings' );
			const list = wrapper.querySelector( '.frm_gc_items_list' );
			const template = wrapper.querySelector( '.frm_gc_item_template' );

			list.append( template.content.cloneNode( true ) );
			reindexItems( wrapper );
			frmDom.autocomplete.initSelectionAutocomplete( list.lastElementChild );
			return;
		}

		const removeBtn = event.target.closest( '.frm_gc_remove_item' );
		if ( removeBtn ) {
			const wrapper = removeBtn.closest( '.frm_gated_content_settings' );
			removeBtn.closest( '.frm_gc_item_row' ).remove();
			reindexItems( wrapper );
			return;
		}

		const copyBtn = event.target.closest( '.frm_gc_copy_shortcode' );
		if ( copyBtn ) {
			const text = copyBtn.dataset.frmCopy;
			if ( navigator.clipboard?.writeText ) {
				navigator.clipboard.writeText( text ).then( () => showCopied( copyBtn ) );
			} else {
				// Fallback for browsers without Clipboard API.
				const textarea = document.createElement( 'textarea' );
				textarea.value = text;
				textarea.style.cssText = 'position:fixed;opacity:0;';
				document.body.append( textarea );
				textarea.select();
				document.execCommand( 'copy' );
				textarea.remove();
				showCopied( copyBtn );
			}
		}
	} );

	document.addEventListener( 'change', function( event ) {
		const typeSelect = event.target.closest( '.frm-gc-item-type' );
		if ( typeSelect ) {
			activateType( typeSelect.closest( '.frm_gc_item_row' ) );
			return;
		}

		const fileFormSelect = event.target.closest( '.frm-gc-file-form-select' );
		if ( fileFormSelect ) {
			filterFileFields( fileFormSelect );
		}
	} );

	// Show/hide "Keep old token when entry is updated" when the event multi-select changes.
	jQuery( document ).on( 'frm-multiselect-changed', 'select[id^="event_"]', function() {
		const section = document.querySelector( `.frm_gc_update_section[data-frm-gc-event-id="${ this.id }"]` );
		if ( ! section ) {
			return;
		}
		const hasUpdate = Array.from( this.options ).some( function( o ) {
			return o.selected && 'update' === o.value;
		} );
		section.hidden = ! hasUpdate;
	} );
}() );
