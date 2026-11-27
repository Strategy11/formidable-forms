const { setPendingFieldSetting } = require( '../deferredFieldSettings' );
const { allowContainerDrop } = require( '../fieldPlacement' );

module.exports = function createDragDrop( {
	deleteTooltips,
	ensureFieldSettings,
	fieldUpdated,
	fieldsContainer,
	getFieldsInRow,
	infoModal,
	syncLayoutClasses,
	toggleSectionHolder,
	unselectFieldGroups,
}, builder ) {
	const { tag, div } = frmDom;
	const { __ } = wp.i18n;
	let copyHelper = false;

	let autoId = 0;

	// Prepare dragging and attach drop targets within a screen of the visible field list
	// to keep ahead of the auto scroll while a field is being dragged.
	const DRAG_DROP_ROOT_MARGIN = '100% 0px';

	const dragDropDeferredDetach = new Set();

	const pendingDraggables = new WeakSet();

	function empty( $obj ) {
		if ( $obj !== null ) {
			while ( $obj.firstChild ) {
				$obj.firstChild.remove();
			}
		}
	}

	function addClass( $obj, className ) {
		if ( $obj.classList ) {
			$obj.classList.add( className );
		} else {
			$obj.className += ` ${ className }`;
		}
	}

	function setupSortable( sortableSelector ) {
		document.querySelectorAll( sortableSelector ).forEach( setupSortableList );
	}

	/**
	 * Prepare one sorting list without searching for it again in the document.
	 *
	 * @since 7.0
	 * @param {HTMLElement} list The list containing draggable fields or field groups.
	 * @return {void}
	 */
	function setupSortableList( list ) {
		lazyMakeDroppable( list );
		Array.from( list.children ).forEach( child => lazyMakeDraggable( child, '.frm-move' ) );

		const sectionTitle = list.querySelector( ':scope > [data-type="divider"] > .divider_section_only' );
		if ( sectionTitle ) {
			lazyMakeDroppable( sectionTitle );
		}
	}

	/**
	 * Make a list droppable once it scrolls near the visible part of the builder.
	 *
	 * @since 7.0
	 *
	 * @param {HTMLElement} list The list that accepts dropped fields.
	 * @return {void}
	 */
	function lazyMakeDroppable( list ) {
		observeDragDropElement( list, () => makeDroppable( list ) );
	}

	/**
	 * Prepare a field or field group for interaction once it scrolls near the builder.
	 *
	 * @since 7.0
	 *
	 * @param {HTMLElement}      draggable The field or field group to drag.
	 * @param {string|undefined} handle    The selector for the drag handle, if the whole element should not start a drag.
	 * @return {void}
	 */
	function lazyMakeDraggable( draggable, handle ) {
		observeDragDropElement( draggable, () => makeDraggable( draggable, handle ) );
	}

	/**
	 * Every jQuery UI draggable and droppable is a live widget, and jQuery UI walks all of the
	 * droppables when a drag starts. On a form with a thousand fields, standing them all up at
	 * page load is thousands of widgets before anyone drags anything. The widget is attached
	 * as its element comes within a screen of the visible field list instead. Draggables
	 * wait for interaction as well. Widgets are destroyed again once they scroll away.
	 *
	 * @since 7.0
	 *
	 * @param {HTMLElement} element The element to make draggable or droppable.
	 * @param {Function}    attach  Attaches the widget to the element.
	 * @return {void}
	 */
	function observeDragDropElement( element, attach ) {
		if ( ! element ) {
			return;
		}

		if ( ! builder.state.dragDropObserver ) {
			builder.state.dragDropObserver = new IntersectionObserver(
				handleDragDropIntersections,
				{
					root: builder.state.postBodyContent,
					rootMargin: DRAG_DROP_ROOT_MARGIN
				}
			);
		}

		builder.state.dragDropAttachers.set( element, attach );
		builder.state.dragDropObserver.observe( element );
	}

	/**
	 * @since 7.0
	 *
	 * @param {IntersectionObserverEntry[]} entries The elements that moved into or out of range.
	 * @return {void}
	 */
	function handleDragDropIntersections( entries ) {
		let attachedDuringDrag = false;

		entries.forEach(
			( { target, isIntersecting } ) => {
				if ( ! target.isConnected ) {
					// The element was replaced or deleted, and jQuery removed its widget with it.
					builder.state.dragDropObserver.unobserve( target );
					builder.state.dragDropAttachers.delete( target );
					return;
				}

				if ( isIntersecting ) {
					if ( ! hasDragDropWidget( target ) ) {
						builder.state.dragDropAttachers.get( target )?.();
						attachedDuringDrag = builder.state.dragState.dragging;
					}
					return;
				}

				if ( ! hasDragDropWidget( target ) ) {
					return;
				}

				if ( document.body.classList.contains( 'frm-dragging' ) ) {
					// Pulling a widget out from under a drag leaves its hover state behind, so wait for the drop.
					dragDropDeferredDetach.add( target );
					return;
				}

				destroyDragDropWidget( target );
			}
		);

		if ( attachedDuringDrag ) {
			// Scrolling while dragging brought new drop targets into range. jQuery UI skips them until they are measured.
			refreshDroppableOffsets();
		}
	}

	/**
	 * Take another look at the widgets that scrolled out of range during a drag.
	 * Observing an element again reports where it is now, so anything that scrolled back
	 * into view keeps its widget.
	 *
	 * @since 7.0
	 *
	 * @return {void}
	 */
	function recheckDeferredDragDropWidgets() {
		dragDropDeferredDetach.forEach(
			element => {
				builder.state.dragDropObserver.unobserve( element );
				builder.state.dragDropObserver.observe( element );
			}
		);
		dragDropDeferredDetach.clear();
	}

	/**
	 * @since 7.0
	 *
	 * @param {HTMLElement} element
	 * @return {boolean} True if the element has a live draggable or droppable widget.
	 */
	function hasDragDropWidget( element ) {
		return element.classList.contains( 'ui-draggable' ) || element.classList.contains( 'ui-droppable' );
	}

	/**
	 * @since 7.0
	 *
	 * @param {HTMLElement} element
	 * @return {void}
	 */
	function destroyDragDropWidget( element ) {
		const $element = jQuery( element );
		if ( $element.draggable( 'instance' ) ) {
			$element.draggable( 'destroy' );
		}
		if ( $element.droppable( 'instance' ) ) {
			$element.droppable( 'destroy' );
		}
	}

	function makeDroppable( list ) {
		jQuery( list ).droppable( {
			accept: '.frmbutton, li.frm_field_box',
			deactivate: handleFieldDrop,
			over: onDragOverDroppable,
			out: onDraggableLeavesDroppable,
			tolerance: 'pointer'
		} );
	}

	function onDragOverDroppable( event, ui ) {
		const droppable = getDroppableForOnDragOver( event.target );
		const draggable = ui.draggable[ 0 ];

		if ( ! allowDrop( draggable, droppable, event ) ) {
			droppable.classList.remove( 'frm-over-droppable' );
			jQuery( droppable ).parents( 'ul.frm_sorting' ).addClass( 'frm-over-droppable' );
			return;
		}

		document.querySelectorAll( '.frm-over-droppable' ).forEach( droppable => droppable.classList.remove( 'frm-over-droppable' ) );
		droppable.classList.add( 'frm-over-droppable' );
		jQuery( droppable ).parents( 'ul.frm_sorting' ).addClass( 'frm-over-droppable' );
	}

	/**
	 * Maybe change the droppable.
	 * Section titles are made droppable, but are not a list, so we need to change the droppable to the section's list instead.
	 *
	 * @param {Element} droppable The droppable element.
	 * @return {Element} The adjusted droppable element.
	 */
	function getDroppableForOnDragOver( droppable ) {
		if ( droppable.classList.contains( 'divider_section_only' ) ) {
			droppable = jQuery( droppable ).nextAll( '.start_divider.frm_sorting' ).get( 0 );
		}
		return droppable;
	}

	function onDraggableLeavesDroppable( event ) {
		const droppable = event.target;
		droppable.classList.remove( 'frm-over-droppable' );
	}

	/**
	 * Initialize dragging on the first hover, press, or touch.
	 *
	 * @since 7.1
	 * @param {HTMLElement}      draggable The field or field group to drag.
	 * @param {string|undefined} handle    The selector for the drag handle.
	 * @return {void}
	 */
	function makeDraggable( draggable, handle ) {
		if ( pendingDraggables.has( draggable ) || draggable.classList.contains( 'ui-draggable' ) ) {
			return;
		}

		const events = [ 'mouseover', 'mousedown', 'touchstart', 'click' ];
		const initialize = () => {
			events.forEach( type => draggable.removeEventListener( type, initialize, true ) );
			pendingDraggables.delete( draggable );
			initializeDraggable( draggable, handle );
		};

		pendingDraggables.add( draggable );
		// Capture initializes the widget before jQuery UI handles the same press or touch.
		events.forEach( type => draggable.addEventListener( type, initialize, true ) );
	}

	/**
	 * Attach the draggable widget after interaction.
	 *
	 * @since 7.1
	 * @param {HTMLElement}      draggable The field or field group to drag.
	 * @param {string|undefined} handle    The selector for the drag handle.
	 * @return {void}
	 */
	function initializeDraggable( draggable, handle ) {
		const settings = {
			helper: getDraggableHelper,
			revert: 'invalid',
			delay: 10,
			start: handleDragStart,
			stop: handleDragStop,
			drag: handleDrag,
			cursor: 'grabbing',
			cursorAt: {
				top: 0,
				left: 90 // The width of draggable button is 180. 90 should center the draggable on the cursor.
			}
		};
		if ( 'string' === typeof handle ) {
			settings.handle = handle;
		}
		jQuery( draggable ).draggable( settings );
	}

	function getDraggableHelper( event ) {
		const draggable = event.delegateTarget;

		if ( isFieldGroup( draggable ) ) {
			const newTextFieldClone = document.getElementById( 'frm-insert-fields' ).querySelector( '.frm_ttext' ).cloneNode( true );
			newTextFieldClone.querySelector( 'use' ).setAttributeNS( 'http://www.w3.org/1999/xlink', 'href', '#frm_field_group_layout_icon' );
			newTextFieldClone.querySelector( 'span' ).textContent = __( 'Field Group', 'formidable' );
			newTextFieldClone.classList.add( 'frm_field_box' );
			newTextFieldClone.classList.add( 'ui-sortable-helper' );
			return newTextFieldClone;
		}

		let copyTarget;
		const isNewField = draggable.classList.contains( 'frmbutton' );
		if ( isNewField ) {
			copyTarget = draggable.cloneNode( true );
			copyTarget.classList.add( 'ui-sortable-helper' );
			draggable.classList.add( 'frm-new-field' );
			return copyTarget;
		}

		if ( draggable.hasAttribute( 'data-ftype' ) ) {
			const fieldType = draggable.getAttribute( 'data-ftype' );
			copyTarget = document.getElementById( 'frm-insert-fields' ).querySelector( `.frm_t${ fieldType }` );
			if ( copyTarget ) {
				copyTarget = copyTarget.cloneNode( true );
				copyTarget.classList.add( 'form-field' );
				copyTarget.classList.add( 'ui-sortable-helper' );
				return copyTarget.cloneNode( true );
			}
		}

		return div( { className: 'frmbutton' } );
	}

	function handleDragStart( event, ui ) {
		if ( event.target.classList.contains( 'frm_at_limit' ) ) {
			builder.fieldActions.showLimitModal();
			return false;
		}

		builder.state.dragState.dragging = true;
		builder.state.dragState.draggable = null;
		builder.state.dragState.offsetsRefreshed = false;

		const container = builder.state.postBodyContent;
		container.classList.add( 'frm-dragging-field' );

		document.body.classList.add( 'frm-dragging' );
		ui.helper.addClass( 'frm-sortable-helper' );
		ui.helper.initialOffset = container.scrollTop;

		event.target.classList.add( 'frm-drag-fade' );

		unselectFieldGroups();
		deleteEmptyDividerWrappers();
		builder.fieldActions.maybeRemoveGroupHoverTarget();
		builder.fieldActions.closeOpenFieldDropdowns();
		deleteTooltips();
	}

	function handleDragStop() {
		builder.state.dragState.draggable = null;

		const container = builder.state.postBodyContent;
		container.classList.remove( 'frm-dragging-field' );
		document.body.classList.remove( 'frm-dragging' );

		const fade = document.querySelector( '.frm-drag-fade' );
		if ( fade ) {
			fade.classList.remove( 'frm-drag-fade' );
		}

		recheckDeferredDragDropWidgets();
	}

	function handleDrag( event, ui ) {
		if ( ! builder.state.dragState.offsetsRefreshed ) {
			// The drag start handler tidies up the field list, so re-measure once before
			// the first drop target is worked out.
			builder.state.dragState.offsetsRefreshed = true;
			builder.state.dragState.draggable = jQuery( event.target ).draggable( 'instance' ) || null;
			refreshDroppableOffsets( event );
		}

		maybeScrollBuilder( event );
		const draggable = event.target;
		const droppable = getDroppableTarget();

		let placeholder = document.getElementById( 'frm_drag_placeholder' );
		if ( ! allowDrop( draggable, droppable, event ) ) {
			if ( placeholder ) {
				placeholder.remove();
			}
			return;
		}

		if ( ! placeholder ) {
			placeholder = tag( 'li', {
				id: 'frm_drag_placeholder',
				className: 'sortable-placeholder'
			} );
		}
		const frmSortableHelper = ui.helper.get( 0 );
		if ( frmSortableHelper.classList.contains( 'form-field' ) || frmSortableHelper.classList.contains( 'frm_field_box' ) ) {
			// Sync the y position of the draggable so it still follows the cursor after scrolling up and down the field list.
			frmSortableHelper.style.transform = `translateY(${ getDragOffset( ui.helper ) }px)`;
		}

		if ( 'frm-show-fields' === droppable.id || droppable.classList.contains( 'start_divider' ) ) {
			placeholder.style.left = 0;
			handleDragOverYAxis( { droppable, y: event.clientY, placeholder } );
			return;
		}

		placeholder.style.top = '';
		handleDragOverFieldGroup( { droppable, x: event.clientX, placeholder } );
	}

	function maybeScrollBuilder( event ) {
		const scrollTopBefore = builder.state.postBodyContent.scrollTop;

		builder.state.$postBodyContent.scrollTop(
			( _, v ) => {
				const moved = event.clientY;
				const contentHeight = builder.state.postBodyContent.offsetHeight;
				const relativePos = event.clientY - builder.state.postBodyContent.offsetTop;
				const offsetFromCenter = relativePos - ( contentHeight / 2 );

				if ( relativePos > ( contentHeight - 50 ) && moved > 5 ) {
					// Scrolling down.
					return v + ( offsetFromCenter * 0.1 );
				}

				if ( relativePos < 70 && moved < 130 ) {
					// Scrolling up.
					return v - Math.abs( offsetFromCenter * 0.1 );
				}

				return v;
			}
		);

		if ( scrollTopBefore !== builder.state.postBodyContent.scrollTop ) {
			refreshDroppableOffsets( event );
		}
	}

	/**
	 * Re-measure where every droppable sits on the page.
	 *
	 * The draggables deliberately leave jQuery UI's refreshPositions option off. With it on,
	 * jQuery UI re-measures every droppable on every mouse move, which is one offset() read
	 * per field group per event. On a form with hundreds of groups that is most of a second
	 * per mouse move, so the drop placeholder ends up trailing seconds behind the cursor and
	 * the narrow band between two field groups becomes impossible to hit.
	 *
	 * Dragging on its own does not move a droppable. The drop placeholder is laid out at zero
	 * height between rows and absolutely positioned inside a row, so inserting it shifts
	 * nothing. Two other things do move them: scrolling the field list, and a batch of
	 * AJAX loaded fields arriving while a drag is already under way. The measurements are
	 * refreshed on the first drag event and at those two points, and left alone otherwise.
	 *
	 * @param {Event} [event] The drag event, when the refresh is driven by one.
	 * @return {void}
	 */
	function refreshDroppableOffsets( event ) {
		if ( ! builder.state.dragState.draggable ) {
			return;
		}

		jQuery.ui.ddmanager.prepareOffsets( builder.state.dragState.draggable, event );
	}

	function getDragOffset( $helper ) {
		return builder.state.postBodyContent.scrollTop - $helper.initialOffset;
	}

	function getDroppableTarget() {
		let droppable = fieldsContainer;
		let nextDroppable = droppable.querySelector( '.frm-over-droppable' );
		while ( nextDroppable ) {
			droppable = nextDroppable;
			nextDroppable = droppable.querySelector( '.frm-over-droppable' );
		}
		if ( 'frm-show-fields' === droppable.id && ! droppable.classList.contains( 'frm-over-droppable' ) ) {
			droppable = false;
		}
		return droppable;
	}

	function handleFieldDrop( _, ui ) {
		if ( ! builder.state.dragState.dragging ) {
			// dragState.dragging is set to true on drag start.
			// The deactivate event gets called for every droppable. This check to make sure it happens once.
			return;
		}

		builder.state.dragState.dragging = false;

		const draggable = ui.draggable[ 0 ];
		const placeholder = document.getElementById( 'frm_drag_placeholder' );

		if ( ! placeholder ) {
			ui.helper.remove();
			builder.state.debouncedSyncAfterDragAndDrop();
			return;
		}

		maybeOpenCollapsedPage( placeholder );

		const $previousFieldContainer = ui.helper.parent();
		const sectionSelector = wp.hooks.applyFilters( 'frm_builder_section_selector', 'ul.start_divider' );
		const previousSection = ui.helper.get( 0 ).closest( sectionSelector );
		const newSection = placeholder.closest( sectionSelector );

		if ( draggable.classList.contains( 'frm-new-field' ) ) {
			insertNewFieldByDragging( draggable.id );
		} else {
			moveFieldThatAlreadyExists( draggable, placeholder );
			maybeMakeFieldGroupDraggableAfterDragging( placeholder.parentElement );
		}

		const previousSectionId = previousSection ? parseInt( previousSection.closest( '.edit_field_type_divider' ).getAttribute( 'data-fid' ) ) : 0;
		const newSectionId = newSection ? parseInt( newSection.closest( '.edit_field_type_divider' ).getAttribute( 'data-fid' ) ) : 0;

		placeholder.remove();
		ui.helper.remove();

		const $previousContainerFields = $previousFieldContainer.length ? getFieldsInRow( $previousFieldContainer ) : [];
		maybeUpdatePreviousFieldContainerAfterDrop( $previousFieldContainer, $previousContainerFields );
		maybeUpdateDraggableClassAfterDrop( draggable, $previousContainerFields );

		if ( previousSectionId !== newSectionId ) {
			updateFieldAfterMovingBetweenSections( jQuery( draggable ), previousSection );
		}

		builder.state.debouncedSyncAfterDragAndDrop();
	}

	/**
	 * When a field is moved into a field group, make sure the field group is draggable.
	 *
	 * @since 6.24
	 *
	 * @param {HTMLElement} placeholderParent
	 * @return {void}
	 */
	function maybeMakeFieldGroupDraggableAfterDragging( placeholderParent ) {
		const isDroppingIntoFieldGroup = placeholderParent.nodeName === 'UL' &&
			! placeholderParent.classList.contains( 'start_divider' ) &&
			'frm-show-fields' !== placeholderParent.id;

		if ( ! isDroppingIntoFieldGroup ) {
			return;
		}

		const fieldGroupLi = placeholderParent.closest( 'li' );
		if ( fieldGroupLi && ! fieldGroupLi.classList.contains( 'ui-draggable' ) ) {
			makeDraggable( fieldGroupLi, '.frm-move' );
		}
	}

	/**
	 * If a page if collapsed, expand it before dragging since only the page break will move.
	 *
	 * @param {Element} placeholder
	 * @return {void}
	 */
	function maybeOpenCollapsedPage( placeholder ) {
		if ( ! placeholder.previousElementSibling || ! placeholder.previousElementSibling.classList.contains( 'frm-is-collapsed' ) ) {
			return;
		}

		const $pageBreakField = jQuery( placeholder ).prevUntil( '[data-type="break"]' );
		if ( ! $pageBreakField.length ) {
			return;
		}

		const collapseButton = $pageBreakField.find( '.frm-collapse-page' ).get( 0 );
		if ( collapseButton ) {
			collapseButton.click();
		}
	}

	function maybeUpdatePreviousFieldContainerAfterDrop( $previousFieldContainer, $previousContainerFields ) {
		if ( ! $previousFieldContainer.length ) {
			return;
		}

		if ( $previousContainerFields.length ) {
			syncLayoutClasses( $previousContainerFields.first() );
		} else {
			maybeDeleteAnEmptyFieldGroup( $previousFieldContainer.get( 0 ) );
		}
	}

	function maybeUpdateDraggableClassAfterDrop( draggable, $previousContainerFields ) {
		if ( 0 !== $previousContainerFields.length || 1 !== getFieldsInRow( jQuery( draggable.parentNode ) ).length ) {
			syncLayoutClasses( jQuery( draggable ) );
		}
	}

	/**
	 * Remove an empty field group, but don't remove an empty section.
	 *
	 * @param {Element} previousFieldContainer
	 * @return {void}
	 */
	function maybeDeleteAnEmptyFieldGroup( previousFieldContainer ) {
		const closestFieldBox = previousFieldContainer.closest( 'li.frm_field_box' );
		if ( closestFieldBox && ! closestFieldBox.classList.contains( 'edit_field_type_divider' ) ) {
			closestFieldBox.remove();
		}
	}

	function handleDragOverYAxis( { droppable, y, placeholder } ) {
		const $list = jQuery( droppable );

		let top;

		const children = getRowsInList( $list );
		if ( 0 === children.length ) {
			$list.prepend( placeholder );
			top = 0;
		} else {
			const insertAtIndex = determineIndexBasedOffOfMousePositionInList( $list, y, children );

			if ( insertAtIndex === children.length ) {
				const $lastChild = jQuery( children[ insertAtIndex - 1 ] );
				top = $lastChild.offset().top + $lastChild.outerHeight();
				$list.append( placeholder );

				// Make sure nothing gets inserted after the end divider.
				const $endDivider = $list.children( '.edit_field_type_end_divider' );
				if ( $endDivider.length ) {
					$list.append( $endDivider );
				}
			} else {
				top = jQuery( children[ insertAtIndex ] ).offset().top;
				jQuery( children[ insertAtIndex ] ).before( placeholder );
			}
		}

		top -= $list.offset().top;
		placeholder.style.top = `${ top }px`;
	}

	/**
	 * Get the rows of a sortable list as a plain array, skipping the end divider.
	 *
	 * @param {jQuery} $list The list to read the rows from.
	 * @return {Array.<Element>} The rows that can accept a drop.
	 */
	function getRowsInList( $list ) {
		const list = $list.get( 0 );
		if ( ! list ) {
			return [];
		}
		return Array.from( list.children ).filter( row => ! row.classList.contains( 'edit_field_type_end_divider' ) );
	}

	/**
	 * Work out where in a list the dragged field would be dropped.
	 *
	 * This runs on every drag event, and reading an item's position forces the browser to lay the
	 * page out again. Walking the list one item at a time therefore costs a layout per item, and on
	 * a long form dragging near the top of the list meant reading nearly every field before finding
	 * the answer. The items are stacked down the page, so their tops only ever increase, and a
	 * binary search finds the same item after a handful of reads instead of hundreds.
	 *
	 * @param {jQuery}          $list  The list being dragged over.
	 * @param {number}          y      Mouse position, relative to the viewport.
	 * @param {Array.<Element>} [rows] The rows of the list, when the caller has already read them.
	 * @return {number} The index to insert at.
	 */
	function determineIndexBasedOffOfMousePositionInList( $list, y, rows ) {
		if ( ! document.querySelector( '.frm-has-fields .frm_no_fields' ) ) {
			// Always return 0 when there are no fields.
			return 0;
		}

		const items = rows || getRowsInList( $list );

		// Find the last item that starts above the cursor.
		let low = 0;
		let high = items.length - 1;
		let match = -1;

		while ( low <= high ) {
			const middle = Math.floor( ( low + high ) / 2 );

			if ( y > items[ middle ].getBoundingClientRect().top ) {
				match = middle;
				low = middle + 1;
			} else {
				high = middle - 1;
			}
		}

		if ( -1 === match ) {
			return 0;
		}

		const rect = items[ match ].getBoundingClientRect();

		// Past the halfway point of that item means the field belongs after it.
		return y > rect.top + ( rect.height / 2 ) ? match + 1 : match;
	}

	function handleDragOverFieldGroup( { droppable, x, placeholder } ) {
		const $row = jQuery( droppable );
		const $children = getFieldsInRow( $row );

		if ( ! $children.length ) {
			return;
		}

		let left;
		const insertAtIndex = determineIndexBasedOffOfMousePositionInRow( $row, x );

		if ( insertAtIndex === $children.length ) {
			const $lastChild = jQuery( $children.get( insertAtIndex - 1 ) );
			left = $lastChild.offset().left + $lastChild.outerWidth();
			$row.append( placeholder );
		} else {
			left = jQuery( $children.get( insertAtIndex ) ).offset().left;
			jQuery( $children.get( insertAtIndex ) ).before( placeholder );

			const amountToOffsetLeftBy = 0 === insertAtIndex ? 4 : 8; // Offset by 8 in between rows, but only 4 for the first item in a group.
			left -= amountToOffsetLeftBy; // Offset the placeholder slightly so it appears between two fields.
		}

		left -= $row.offset().left;

		placeholder.style.left = `${ left }px`;
	}

	function syncAfterDragAndDrop() {
		fixUnwrappedListItems();
		toggleSectionHolder();
		maybeFixEndDividers();
		maybeDeleteEmptyFieldGroups();
		builder.fieldEditor.updateFieldOrder();

		const event = new Event( 'frm_sync_after_drag_and_drop', { bubbles: false } );
		document.dispatchEvent( event );
	}

	function maybeFixEndDividers() {
		document.querySelectorAll( '.edit_field_type_end_divider' ).forEach(
			endDivider => endDivider.parentNode.append( endDivider )
		);
	}

	function maybeDeleteEmptyFieldGroups() {
		document.querySelectorAll( 'li.form_field_box:not(.form-field)' ).forEach(
			fieldGroup => ! fieldGroup.children.length && fieldGroup.remove()
		);
	}

	function fixUnwrappedListItems() {
		const lists = document.querySelectorAll( 'ul#frm-show-fields, ul.start_divider' );
		lists.forEach(
			list => {
				list.childNodes.forEach(
					child => {
						if ( child.classList === undefined ) {
							return;
						}

						if ( child.classList.contains( 'edit_field_type_end_divider' ) ) {
							// Never wrap end divider in place.
							return;
						}

						if ( child.classList !== undefined && child.classList.contains( 'form-field' ) ) {
							builder.fieldActions.wrapFieldLiInPlace( child );
						}
					}
				);
			}
		);
	}

	function deleteEmptyDividerWrappers() {
		const dividers = document.querySelectorAll( 'ul.start_divider' );
		if ( ! dividers.length ) {
			return;
		}
		dividers.forEach(
			function( divider ) {
				const children = [].slice.call( divider.children );
				children.forEach(
					function( child ) {
						if ( 0 === child.children.length ) {
							child.remove();
						} else if ( 1 === child.children.length && 'ul' === child.firstElementChild.nodeName.toLowerCase() && 0 === child.firstElementChild.children.length ) {
							child.remove();
						}
					}
				);
			}
		);
	}

	function determineIndexBasedOffOfMousePositionInRow( $row, x ) {
		const $inputs = getFieldsInRow( $row );
		const { length } = $inputs;
		let index;
		let input;
		let inputLeft;
		let returnIndex;

		returnIndex = 0;
		for ( index = length - 1; index >= 0; --index ) {
			input = $inputs.get( index );
			inputLeft = jQuery( input ).offset().left;
			if ( x > inputLeft ) {
				returnIndex = index;
				if ( x > inputLeft + ( jQuery( input ).outerWidth() / 2 ) ) {
					returnIndex = index + 1;
				}
				break;
			}
		}

		return returnIndex;
	}

	// Scope sortable init to the one field whose panel just opened, instead of the whole
	// builder, so jQuery UI's mousedown item scan only covers that field's own option list.
	// Sortable's `items` option is matched against the whole document then filtered to
	// descendants of the element passed to .sortable() - that element itself must be an
	// ancestor of the '.frm_sortable_field_opts li' matches, not the list, so this takes
	// fieldSettingsEl rather than the field's own option list. Guards against double-init
	// since sortable's own item list is refreshed lazily, not just at setup time.
	function setupFieldOptionSorting( fieldSettingsEl ) {
		if ( fieldSettingsEl.classList.contains( 'ui-sortable' ) || ! fieldSettingsEl.querySelector( '.frm_sortable_field_opts' ) ) {
			return;
		}
		const opts = {
			items: '.frm_sortable_field_opts li',
			axis: 'y',
			opacity: 0.65,
			forcePlaceholderSize: false,
			handle: '.frm-drag',
			helper( e, li ) {
				copyHelper = li.clone().insertAfter( li );
				return li.clone();
			},
			stop( e, ui ) {
				if ( copyHelper ) {
					copyHelper.remove();
				}
				const fieldId = ui.item.attr( 'id' ).replace( 'frm_delete_field_', '' ).replace( `-${ ui.item.data( 'optkey' ) }_container`, '' );
				builder.fieldPreview.resetDisplayedOpts( fieldId );
				fieldUpdated();
			}
		};
		jQuery( fieldSettingsEl ).sortable( opts );
	}

	// Get the section where a field is dropped
	function getSectionForFieldPlacement( currentItem ) {
		let section = '';
		if ( currentItem !== undefined && ! currentItem.hasClass( 'edit_field_type_divider' ) ) {
			section = currentItem.closest( '.edit_field_type_divider' );
		}
		return section;
	}

	// Get the form ID where a field is dropped
	function getFormIdForFieldPlacement( section ) {
		let formId = '';

		if ( section[ 0 ] !== undefined ) {
			const sDivide = section.children( '.start_divider' );
			sDivide.children( '.edit_field_type_end_divider' ).appendTo( sDivide );
			if ( section.attr( 'data-formid' ) !== undefined ) {
				const fieldId = section.attr( 'data-fid' );
				formId = jQuery( `input[name="field_options[form_select_${ fieldId }]"]` ).val();
			}
		}

		if ( formId === undefined || formId === '' ) {
			formId = builder.state.thisFormId;
		}

		return formId;
	}

	// Get the section ID where a field is dropped
	function getSectionIdForFieldPlacement( section ) {
		let sectionId = 0;
		if ( section[ 0 ] !== undefined ) {
			sectionId = section.attr( 'id' ).replace( 'frm_field_id_', '' );
		}

		return sectionId;
	}

	/**
	 * Update a field after it is dragged and dropped into, out of, or between sections
	 *
	 * @param {Object} currentItem
	 * @param {Object} previousSection
	 * @return {void}
	 */
	function updateFieldAfterMovingBetweenSections( currentItem, previousSection ) {
		if ( ! currentItem.hasClass( 'form-field' ) ) {
			// currentItem is a field group. Call for children recursively.
			getFieldsInRow( jQuery( currentItem.get( 0 ).firstChild ) ).each(
				function() {
					updateFieldAfterMovingBetweenSections( jQuery( this ), previousSection );
				}
			);
			return;
		}

		const fieldId = currentItem.attr( 'id' ).replace( 'frm_field_id_', '' );
		const section = getSectionForFieldPlacement( currentItem );
		const formId = getFormIdForFieldPlacement( section );
		const sectionId = getSectionIdForFieldPlacement( section );
		const previousFormId = previousSection ? getFormIdForFieldPlacement( jQuery( previousSection.parentNode ) ) : 0;

		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_update_field_after_move',
				form_id: formId,
				field: fieldId,
				section_id: sectionId,
				previous_form_id: previousFormId,
				nonce: frmGlobal.nonce
			},
			success() {
				toggleSectionHolder();
				updateInSectionValue( fieldId, sectionId );
			}
		} );
	}

	// Update the in_section field value
	function updateInSectionValue( fieldId, sectionId ) {
		// The input may still be in a cached settings panel, which would otherwise keep the old section.
		ensureFieldSettings( fieldId );
		const inSectionInput = document.getElementById( `frm_in_section_${ fieldId }` );
		if ( inSectionInput ) {
			inSectionInput.value = sectionId;
			return;
		}
		// The settings are still loading. Apply the section once the panel is inserted.
		setPendingFieldSetting( fieldId, 'in_section', `frm_in_section_${ fieldId }`, sectionId );
	}

	/**
	 * Get the arguments for inserting a new field.
	 *
	 * @since 6.23
	 *
	 * @param {string} fieldType The type of field to insert.
	 * @param {string} sectionId The section ID to insert into.
	 * @param {string} formId    The form ID.
	 * @param {number} hasBreak  Whether the field has a page break.
	 *
	 * @return {Object} Data object for the new field AJAX request.
	 */
	function getInsertNewFieldArgs( fieldType, sectionId, formId, hasBreak ) {
		const fieldArgs = {
			action: 'frm_insert_field',
			form_id: formId,
			field_type: fieldType,
			section_id: sectionId,
			nonce: frmGlobal.nonce,
			has_break: hasBreak
		};

		// Only send last row field IDs to update their order if this field isn't added to a repeater.
		const isInRepeater = sectionId > 0 && document.getElementById( 'form_id' ).value !== formId;
		if ( ! isInRepeater ) {
			fieldArgs.last_row_field_ids = getFieldIdsInSubmitRow();
		}

		return fieldArgs;
	}

	/**
	 * Returns true if it's a range field type and slider type is not selected.
	 *
	 * @since 6.23
	 *
	 * @param {string} fieldType The field type to check.
	 * @return {boolean} True if range field without slider type selected.
	 */
	function shouldStopInsertingField( fieldType ) {
		return wp.hooks.applyFilters( 'frm_should_stop_inserting_field', false, fieldType );
	}

	/**
	 * Add a new field by dragging and dropping it from the Fields sidebar
	 *
	 * @param {string} fieldType
	 */
	function insertNewFieldByDragging( fieldType ) {
		if ( shouldStopInsertingField( fieldType ) ) {
			wp.hooks.doAction( 'frm_stopped_inserting_by_dragging', fieldType );
			return;
		}

		const placeholder = document.getElementById( 'frm_drag_placeholder' );
		const loadingID = `${ fieldType.replace( '|', '-' ) }_${ getAutoId() }`;
		const loading = tag(
			'li',
			{
				id: loadingID,
				className: 'frm-wait frmbutton_loadingnow'
			}
		);
		const $placeholder = jQuery( loading );
		const currentItem = jQuery( placeholder );
		const section = getSectionForFieldPlacement( currentItem );
		const formId = getFormIdForFieldPlacement( section );
		const sectionId = getSectionIdForFieldPlacement( section );

		placeholder.parentNode.insertBefore( loading, placeholder );
		placeholder.remove();
		syncLayoutClasses( $placeholder );

		let hasBreak = 0;
		if ( 'summary' === fieldType ) {
			// see if we need to insert a page break before this newly-added summary field. Check for at least 1 page break
			hasBreak = jQuery( `.frmbutton_loadingnow#${ loadingID }` ).prevAll( 'li[data-type="break"]' ).length ? 1 : 0;
		}

		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: getInsertNewFieldArgs( fieldType, sectionId, formId, hasBreak ),
			success( msg ) {
				handleInsertFieldByDraggingResponse( msg, $placeholder );

				const fieldId = checkMsgForFieldId( msg );
				if ( fieldId ) {
					/**
					 * Fires after a field is added.
					 *
					 * @since 6.23
					 *
					 * @param {Object} fieldData            The field data.
					 * @param {string} fieldData.field      The field HTML.
					 * @param {string} fieldData.field_type The field type.
					 * @param {string} fieldData.form_id    The form ID.
					 */
					wp.hooks.doAction( 'frm_after_field_added_in_form_builder', {
						field: msg,
						fieldId,
						fieldType,
						form_id: formId,
					} );
				}
			},
			error: handleInsertFieldError
		} );
	}

	/**
	 * @param {string} msg
	 * @param {Object} $placeholder jQuery object.
	 */
	function handleInsertFieldByDraggingResponse( msg, $placeholder ) {
		$placeholder = jQuery( $placeholder );
		let replaceWith;
		builder.state.formEditorContainer.classList.add( 'frm-has-fields' );
		const $siblings = $placeholder.siblings( 'li.form-field' ).not( '.edit_field_type_end_divider' );

		if ( ! $siblings.length ) {
			// if dragging into a new row, we need to wrap the li first.
			replaceWith = builder.fieldActions.wrapFieldLi( msg );
		} else {
			replaceWith = msgAsjQueryObject( msg );
			if ( ! $placeholder.get( 0 ).parentNode.parentNode.classList.contains( 'ui-draggable' ) ) {
				// If a field group wasn't draggable because it only had a single field, make it draggable.
				makeDraggable( $placeholder.get( 0 ).parentNode.parentNode, '.frm-move' );
			}
		}
		$placeholder.replaceWith( replaceWith );
		builder.fieldActions.afterAddField( msg, false );
		if ( $siblings.length ) {
			syncLayoutClasses( $siblings.first() );
		}
		toggleSectionHolder();

		if ( ! $siblings.length ) {
			makeDroppable( replaceWith.get( 0 ).querySelector( 'ul.frm_sorting' ) );
			makeDraggable( replaceWith.get( 0 ).querySelector( 'li.form-field' ), '.frm-move' );
		} else {
			makeDraggable( replaceWith.get( 0 ), '.frm-move' );
		}
	}

	/**
	 * Get the field ID from the response message.
	 *
	 * @since 6.23
	 *
	 * @param {string} msg The response message containing the field ID.
	 * @return {number} The extracted field ID.
	 */
	function checkMsgForFieldId( msg ) {
		const result = msg.match( /data-fid="(\d+)"/ );
		return result ? parseInt( result[ 1 ] ) : 0;
	}

	function getFieldIdsInSubmitRow() {
		const submitField = document.querySelector( '.edit_field_type_submit' );
		if ( ! submitField ) {
			return [];
		}

		const lastRowFields = submitField.parentNode.children;
		const ids = [];
		for ( let i = 0; i < lastRowFields.length; i++ ) {
			ids.push( lastRowFields[ i ].dataset.fid );
		}

		return ids;
	}

	function moveFieldThatAlreadyExists( draggable, placeholder ) {
		placeholder.parentNode.insertBefore( draggable, placeholder );
	}

	function msgAsjQueryObject( msg ) {
		const element = div();
		element.innerHTML = msg;
		return jQuery( element.firstChild );
	}

	function handleInsertFieldError( jqXHR, _, errorThrown ) {
		maybeShowInsertFieldError( errorThrown, jqXHR );
	}

	function maybeShowInsertFieldError( errorThrown, jqXHR ) {
		if ( ! jqXHRAborted( jqXHR ) ) {
			infoModal( `${ errorThrown }. Please try again.` );
		}
	}

	function jqXHRAborted( jqXHR ) {
		return jqXHR.status === 0 || jqXHR.readyState === 0;
	}

	/**
	 * Get a unique id that automatically increments with every function call.
	 * Can be used for any UI that requires a unique id.
	 * Not to be used in data.
	 *
	 * @return {number} A unique auto-incremented ID.
	 */
	function getAutoId() {
		return ++autoId;
	}

	/**
	 * Get the top level row that holds the submit button.
	 *
	 * The row is remembered between calls and only looked up again once it leaves the list.
	 * allowDrop runs several times per drag event, and searching the whole field list every
	 * time is a full DOM walk on a long form.
	 *
	 * @param {HTMLElement} list The top level field list.
	 * @return {HTMLElement|null} The row holding the submit button, or null when the form has none.
	 */
	function getSubmitButtonRow( list ) {
		if ( builder.state.dragState.submitButtonRow && builder.state.dragState.submitButtonRow.parentNode === list ) {
			return builder.state.dragState.submitButtonRow;
		}

		const submitButton = list.querySelector( '.edit_field_type_submit' );
		builder.state.dragState.submitButtonRow = submitButton ? submitButton.closest( '#frm-show-fields > li' ) : null;

		return builder.state.dragState.submitButtonRow;
	}

	/**
	 * Determine if a draggable element can be droppable into a droppable element.
	 *
	 * Don't allow page break, embed form, or section inside section field
	 * Don't allow page breaks inside of field groups.
	 * Don't allow field groups with sections inside of sections.
	 * Don't allow field groups in field groups.
	 * Don't allow hidden fields inside of field groups but allow them in sections.
	 * Don't allow any fields below the submit button field.
	 * Don't allow submit button field above any fields.
	 * Don't allow GDPR fields in repeaters.
	 *
	 * @param {HTMLElement} draggable
	 * @param {HTMLElement} droppable
	 * @param {Event}       event
	 * @return {boolean} Whether the drag operation is allowed.
	 */
	function allowDrop( draggable, droppable, event ) {
		if ( false === droppable ) {
			// Don't show drop placeholder if dragging somewhere off of the droppable area.
			return false;
		}

		if ( droppable.closest( '.frm-sortable-helper' ) ) {
			// Do not allow drop into draggable.
			return false;
		}

		if ( ! allowContainerDrop( draggable, droppable ) ) {
			return false;
		}

		const customDrop = wp.hooks.applyFilters( 'frm_builder_allow_drop', null, draggable, droppable );
		if ( null !== customDrop ) {
			return customDrop;
		}

		const isSubmitBtn = draggable.classList.contains( 'edit_field_type_submit' );
		const containSubmitBtn = ! draggable.classList.contains( 'form_field' ) && Boolean( draggable.querySelector( '.edit_field_type_submit' ) );

		if ( 'frm-show-fields' === droppable.id ) {
			const draggableIndex = determineIndexBasedOffOfMousePositionInList( jQuery( droppable ), event.clientY );

			if ( isSubmitBtn || containSubmitBtn ) {
				// Do not allow dropping submit button to above position.
				const lastRowIndex = droppable.childElementCount - 1;
				return draggableIndex > lastRowIndex;
			}

			// Do not allow dropping other fields to below submit button.
			const submitButtonRow = getSubmitButtonRow( droppable );
			if ( ! submitButtonRow ) {
				return true;
			}

			return draggableIndex <= Array.prototype.indexOf.call( droppable.children, submitButtonRow );
		}

		if ( isSubmitBtn ) {
			if ( droppable.classList.contains( 'start_divider' ) ) {
				// Don't allow dropping submit button into a repeater.
				return false;
			}

			if ( isLastRow( droppable.parentElement ) ) {
				// Allow dropping submit button into the last row.
				return true;
			}

			if ( ! isLastRow( droppable.parentElement.nextElementSibling ) ) {
				// Don't a dropping submit button into the row that isn't the second one from bottom.
				return false;
			}

			// Allow dropping submit button into the second row from bottom if there is only submit button in the last row.
			return ! draggable.parentElement.querySelector( 'li.frm_field_box:not(.edit_field_type_submit)' );
		}

		const droppableIsARepeater = droppable.classList.contains( 'start_divider' ) && null !== droppable.closest( '.repeat_section' );

		if ( ! droppableIsARepeater ) {
			const $fieldsInRow = getFieldsInRow( jQuery( droppable ) );
			if ( ! groupCanFitAnotherField( $fieldsInRow, jQuery( draggable ) ) ) {
				// Field group is full and cannot accept another field.
				return false;
			}

			if ( isSectionFieldType( draggable.id ) && droppable.closest( '.start_divider' ) ) {
				return false;
			}
		}

		const isNewField = draggable.classList.contains( 'frm-new-field' );
		if ( isNewField ) {
			return allowNewFieldDrop( draggable, droppable );
		}

		return allowMoveField( draggable, droppable );
	}

	/**
	 * Checks if given element is the last row in form builder.
	 *
	 * @param {HTMLElement} element Element.
	 * @return {boolean} True if the element is the last row.
	 */
	function isLastRow( element ) {
		return element?.matches( '#frm-show-fields > li:last-child' );
	}

	/**
	 * Check if a field type from the field list adds a section.
	 * Repeater (divider|repeat) is a section too, and sections cannot be nested.
	 *
	 * @param {string} fieldType The field type, the id of its button in the field list.
	 * @return {boolean} True for Section and its variants.
	 */
	function isSectionFieldType( fieldType ) {
		return 'divider' === fieldType || fieldType.startsWith( 'divider|' );
	}

	// Don't allow a new page break or hidden field in a field group.
	// Don't allow a new field into a field group that includes a page break or hidden field.
	// Don't allow a new section inside of a section.
	// Don't allow an embedded form in a section.
	function allowNewFieldDrop( draggable, droppable ) {
		const classes = draggable.classList;
		const newPageBreakField = classes.contains( 'frm_tbreak' );
		const newHiddenField = classes.contains( 'frm_thidden' );
		const newSectionField = isSectionFieldType( draggable.id );
		const newEmbedField = classes.contains( 'frm_tform' );
		const newUserIdField = classes.contains( 'frm_tuser_id' );

		const newFieldWillBeAddedToAGroup = ! ( 'frm-show-fields' === droppable.id || droppable.classList.contains( 'start_divider' ) );
		if ( newFieldWillBeAddedToAGroup ) {
			if ( groupIncludesBreakOrHiddenOrUserId( droppable ) ) {
				// Never allow any field beside a page break or a hidden field.
				return false;
			}

			return ! newHiddenField && ! newPageBreakField && ! newUserIdField;
		}

		const fieldTypeIsAlwaysAllowed = ! newPageBreakField && ! newHiddenField && ! newSectionField && ! newEmbedField;
		if ( fieldTypeIsAlwaysAllowed ) {
			return true;
		}

		const newFieldWillBeAddedToASection = droppable.classList.contains( 'start_divider' ) || null !== droppable.closest( '.start_divider' );
		if ( newFieldWillBeAddedToASection ) {
			// Don't allow a section or an embedded form in a section.
			return ! newPageBreakField && ! newEmbedField && ! newSectionField;
		}

		return true;
	}

	function allowMoveField( draggable, droppable ) {
		if ( isFieldGroup( draggable ) ) {
			return allowMoveFieldGroup( draggable, droppable );
		}

		const isPageBreak = draggable.classList.contains( 'edit_field_type_break' );
		if ( isPageBreak ) {
			// Page breaks are only allowed in the main list of fields, not in sections or in field groups.
			return false;
		}

		if ( droppable.classList.contains( 'start_divider' ) ) {
			return allowMoveFieldToSection( draggable );
		}

		const isHiddenField = draggable.classList.contains( 'edit_field_type_hidden' );
		const isUserIdField = draggable.classList.contains( 'edit_field_type_user_id' );
		if ( isHiddenField || isUserIdField ) {
			// Hidden fields and user id fields should not be added to field groups since they're not shown
			// and don't make sense with the grid distribution.
			return false;
		}

		return allowMoveFieldToGroup( draggable, droppable );
	}

	function isFieldGroup( draggable ) {
		return draggable.classList.contains( 'frm_field_box' ) && ! draggable.classList.contains( 'form-field' );
	}

	function allowMoveFieldGroup( fieldGroup, droppable ) {
		if ( droppable.classList.contains( 'start_divider' ) && null === fieldGroup.querySelector( '.start_divider' ) ) {
			// Allow a field group with no section inside of a section.
			return allowMoveFieldToSection( fieldGroup );
		}
		return false;
	}

	function allowMoveFieldToSection( draggable ) {
		const draggableIncludeEmbedForm = draggable.classList.contains( 'edit_field_type_form' ) || draggable.querySelector( '.edit_field_type_form' );
		if ( draggableIncludeEmbedForm ) {
			// Do not allow an embedded form inside of a section.
			return false;
		}

		// Do not allow a section inside of a section.
		const draggableIncludesSection = draggable.classList.contains( 'edit_field_type_divider' ) || draggable.querySelector( '.edit_field_type_divider' );
		return ! draggableIncludesSection;
	}

	function allowMoveFieldToGroup( draggable, group ) {
		if ( groupIncludesBreakOrHiddenOrUserId( group ) ) {
			// Never allow any field beside a page break or a hidden field.
			return false;
		}

		const isFieldGroup = jQuery( draggable ).children( 'ul.frm_sorting' ).not( '.start_divider' ).length > 0;
		if ( isFieldGroup ) {
			// Do not allow a field group directly inside of a field group unless it's in a section.
			return false;
		}

		// Do not allow a section or an embed field inside of a section.
		const draggableIncludesASection = draggable.classList.contains( 'edit_field_type_divider' ) || draggable.querySelector( '.edit_field_type_divider' );
		const draggableIsEmbedField = draggable.classList.contains( 'edit_field_type_form' );
		const groupIsInASection = null !== group.closest( '.start_divider' );
		return ! ( groupIsInASection && ( draggableIncludesASection || draggableIsEmbedField ) );
	}

	function groupIncludesBreakOrHiddenOrUserId( group ) {
		return null !== group.querySelector( '.edit_field_type_break, .edit_field_type_hidden, .edit_field_type_user_id' );
	}

	function groupCanFitAnotherField( fieldsInRow, $field ) {
		if ( fieldsInRow.length < builder.state.MAX_FIELD_GROUP_SIZE ) {
			return true;
		}
		if ( fieldsInRow.length > builder.state.MAX_FIELD_GROUP_SIZE ) {
			return false;
		}
		const fieldId = $field.attr( 'data-fid' );
		// Allow the maximum number if we're not changing field groups.
		return 1 === jQuery( fieldsInRow ).filter( `[data-fid="${ fieldId }"]` ).length;
	}

	return {
		addClass,
		checkMsgForFieldId,
		empty,
		getAutoId,
		getInsertNewFieldArgs,
		handleInsertFieldByDraggingResponse,
		handleInsertFieldError,
		isFieldGroup,
		lazyMakeDraggable,
		makeDraggable,
		makeDroppable,
		msgAsjQueryObject,
		refreshDroppableOffsets,
		setupFieldOptionSorting,
		setupSortable,
		setupSortableList,
		shouldStopInsertingField,
		syncAfterDragAndDrop,
	};
};
