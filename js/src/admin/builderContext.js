/**
 * Orientation and editor-only section controls for the form builder.
 *
 * @since x.x
 */
import { __, _n, sprintf } from '@wordpress/i18n';

const FIELD_DETAILS_STORAGE_KEY = 'frm-builder-field-details';
const FIELD_SELECTOR = 'li.form-field[data-fid]';
const SECTION_SELECTOR = 'li.edit_field_type_divider';
const SKIPPED_TYPES = new Set( [ 'end_divider', 'submit' ] );
const ARRAY_OPTIONS = new Set( [ 'hide_field', 'hide_field_cond', 'hide_opt', 'hide_group', 'hide_compare_field', 'hide_compare_measure' ] );
const OPTION_KEYS = [ 'hide_field', 'hide_field_cond', 'hide_opt', 'hide_group', 'hide_compare_field', 'hide_compare_measure', 'enable_conditional_logic', 'show_hide', 'any_all', 'calc', 'calc_type', 'admin_only' ];

/**
 * Initialize builder feedback without changing saved form settings or field markup order.
 *
 * @since x.x
 * @param {HTMLElement} canvas The builder field list.
 * @return {void}
 */
export function initBuilderContext( canvas ) {
	if ( ! canvas || canvas.classList.contains( 'frm-builder-context' ) ) {
		return;
	}
	canvas.classList.add( 'frm-builder-context' );
	const { tag, div, span } = frmDom;
	const manifest = document.getElementById( 'frm-builder-context' );
	const context = manifest ? JSON.parse( manifest.textContent ) : {};
	manifest?.remove();
	const storageKey = `frm-builder-sections-${ document.getElementById( 'form_id' ).value }`;
	const collapsed = new Set( readPreference( storageKey ) );
	const initialized = new WeakSet();
	const badgeSignatures = new WeakMap();
	const panel = document.getElementById( 'frm-options-panel' );
	const location = tag( 'nav', { className: 'frm-builder-location' } );
	location.setAttribute( 'aria-label', __( 'Selected field location', 'formidable' ) );
	location.hidden = true;
	panel.prepend( location );
	const collapseAll = tag( 'button', { type: 'button', text: __( 'Collapse all', 'formidable' ) } );
	const expandAll = tag( 'button', { type: 'button', text: __( 'Expand all', 'formidable' ) } );
	collapseAll.type = 'button';
	expandAll.type = 'button';
	const showDetails = tag( 'input', { id: 'frm-builder-field-details-toggle' } );
	showDetails.type = 'checkbox';
	showDetails.checked = ! readPreference( FIELD_DETAILS_STORAGE_KEY ).includes( 'hidden' );
	canvas.classList.toggle( 'frm-builder-hide-details', ! showDetails.checked );
	const fieldDetailsOption = tag( 'label', {
		className: 'frm-builder-details-setting',
		children: [ showDetails, span( { text: __( 'Show field details', 'formidable' ) } ) ],
	} );
	const menuToggle = tag( 'summary', {
		className: 'frm-builder-layout-toggle',
		children: [
			frmDom.svg( { href: '#frm_thick_more_vert_icon' } ),
			span( { className: 'screen-reader-text', text: __( 'Builder display options', 'formidable' ) } ),
		],
	} );
	const layoutMenu = tag( 'details', {
		className: 'frm-builder-layout-menu',
		children: [
			menuToggle,
			div( { className: 'frm-builder-layout-actions', children: [ collapseAll, expandAll, fieldDetailsOption ] } ),
		],
	} );
	const publishing = document.getElementById( 'frm-publishing' );
	publishing.prepend( layoutMenu );
	const rootline = document.getElementById( 'frm-backend-rootline-wrapper' );
	const rootlineSetup = document.querySelector( '.frm-show-rootline-wrapper' );
	let displayArea;
	if ( rootline && rootlineSetup && rootline.parentElement === rootlineSetup.parentElement ) {
		displayArea = div( { className: 'frm-builder-display-area' } );
		const progressContent = div( { className: 'frm-builder-progress-content' } );
		rootline.before( displayArea );
		progressContent.append( rootline, rootlineSetup );
		displayArea.append( progressContent );
	}
	const destination = div( { className: 'frm-builder-destination' } );
	destination.setAttribute( 'role', 'status' );
	destination.setAttribute( 'aria-live', 'polite' );
	canvas.before( destination );
	let frame;
	let previousSelection;

	const observer = new MutationObserver( records => {
		if ( records.some( record => record.type === 'childList' || record.target.matches( FIELD_SELECTOR ) ) ) {
			scheduleRefresh();
		}
	} );

	function scheduleRefresh() {
		if ( ! frame ) {
			frame = requestAnimationFrame( refresh );
		}
	}

	function getName( field ) {
		const name = document.getElementById( `frm_name_${ field.dataset.fid }` );
		return name?.value || context[ field.dataset.fid ]?.name ||
			field.querySelector( '.frm_builder_field_name, .frm-label-text' )?.textContent.trim() ||
			__( 'Untitled field', 'formidable' );
	}

	function setCollapsed( section, shouldCollapse ) {
		section.classList.toggle( 'frm-section-collapsed', shouldCollapse );
		const children = section.querySelector( ':scope > ul.start_divider' );
		const control = section.querySelector( '.js-frm-builder-collapse' );
		if ( children && control ) {
			children.hidden = shouldCollapse;
			control.setAttribute( 'aria-expanded', String( ! shouldCollapse ) );
			const label = shouldCollapse ? sprintf(
				/* translators: %s: Section name. */
				__( 'Expand %s in builder', 'formidable' ), getName( section )
			) : sprintf(
				/* translators: %s: Section name. */
				__( 'Collapse %s in builder', 'formidable' ), getName( section )
			);
			control.setAttribute( 'aria-label', label );
		}
		if ( shouldCollapse ) {
			collapsed.add( section.dataset.fid );
		} else {
			collapsed.delete( section.dataset.fid );
		}
		writePreference( storageKey, [ ...collapsed ] );
	}

	function prepareSection( section ) {
		const children = section.querySelector( ':scope > ul.start_divider' );
		const label = section.querySelector( ':scope > .divider_section_only .frm_primary_label' );
		if ( ! children || ! label ) {
			return;
		}
		if ( ! initialized.has( section ) ) {
			section.classList.add( 'frm-builder-section' );
			const oldControl = section.querySelector( '.frm-collapse-section' );
			if ( oldControl ) {
				const control = tag( 'button', {
					type: 'button',
					className: 'frm-collapse-section js-frm-builder-collapse',
					child: frmDom.svg( { href: '#frm_arrowdown6_icon' } ),
				} );
				control.type = 'button';
				children.id = `frm-builder-section-${ section.dataset.fid }`;
				control.setAttribute( 'aria-controls', children.id );
				oldControl.remove();
				label.before( control );
			}
			label.append( span( { className: 'frm-builder-section-count' } ) );
			initialized.add( section );
			setCollapsed( section, collapsed.has( section.dataset.fid ) );
		}
		const isCollapsed = section.classList.contains( 'frm-section-collapsed' );
		if ( children.hidden !== isCollapsed || collapsed.has( section.dataset.fid ) !== isCollapsed ) {
			setCollapsed( section, isCollapsed );
		}
		const count = [ ...children.querySelectorAll( FIELD_SELECTOR ) ].filter(
			field => ! SKIPPED_TYPES.has( field.dataset.type )
		).length;
		let countElement = label.querySelector( '.frm-builder-section-count' );
		if ( ! countElement ) {
			countElement = span( { className: 'frm-builder-section-count' } );
			label.append( countElement );
		}
		countElement.textContent = sprintf(
			/* translators: %d: Number of fields in this section. */
			_n( '%d field', '%d fields', count, 'formidable' ), count
		);
	}

	function updateBadges( field ) {
		if ( SKIPPED_TYPES.has( field.dataset.type ) || field.matches( SECTION_SELECTOR ) ) {
			return;
		}
		const id = field.dataset.fid;
		const options = { ...context[ id ]?.options, has_rules: context[ id ]?.has_rules };
		const settings = document.getElementById( `frm-single-settings-${ id }` );
		if ( settings ) {
			OPTION_KEYS.forEach( key => {
				const controls = settings.querySelectorAll( `[name^="field_options[${ key }_${ id }]"]` );
				if ( controls.length ) {
					const control = [ ...controls ].find( input => ! [ 'radio', 'checkbox' ].includes( input.type ) || input.checked );
					options[ key ] = ARRAY_OPTIONS.has( key ) ? [ ...controls ].map( input => input.value ) : control?.value || '';
				}
			} );
		}
		const additionalRules = settings?.querySelector( `[name="field_options[adv_logic_rules_${ id }]"]` );
		if ( additionalRules ) {
			options.has_rules = ! [ '', '[]' ].includes( additionalRules.value.trim() );
		}
		const badges = getBadges( field, options, context );
		const signature = JSON.stringify( badges );
		if ( badgeSignatures.get( field ) === signature ) {
			return;
		}
		const inner = document.getElementById( `field_${ id }_inner_container` );
		if ( ! inner ) {
			return;
		}
		inner.querySelector( ':scope > .frm-builder-badges' )?.remove();
		badgeSignatures.set( field, signature );
		if ( ! badges.length ) {
			return;
		}
		inner.append( div( {
			className: 'frm-builder-badges',
			children: badges.map( ( [ label, description ] ) => tag( 'details', {
				className: 'frm-builder-badge',
				children: [ tag( 'summary', { text: label } ), span( { text: description } ) ],
			} ) ),
		} ) );
	}

	function updateLocation() {
		const selected = canvas.querySelector( `${ FIELD_SELECTOR }.selected` );
		location.hidden = ! selected;
		if ( ! selected ) {
			previousSelection = null;
			return;
		}
		const ancestors = [];
		let section = selected.parentElement.closest( SECTION_SELECTOR );
		while ( section ) {
			ancestors.unshift( section );
			if ( selected !== previousSelection ) {
				setCollapsed( section, false );
			}
			section = section.parentElement.closest( SECTION_SELECTOR );
		}
		previousSelection = selected;
		location.hidden = ! ancestors.length;
		if ( location.hidden ) {
			return;
		}
		const path = [ ...ancestors, selected ];
		const signature = JSON.stringify( path.map( field => [ field.dataset.fid, getName( field ) ] ) );
		if ( location.dataset.path === signature ) {
			return;
		}
		location.dataset.path = signature;
		const crumbs = path.flatMap( ( field, index ) => {
			const crumb = tag( 'button', { className: 'frm-builder-breadcrumb', text: getName( field ) } );
			crumb.type = 'button';
			crumb.dataset.fieldId = field.dataset.fid;
			if ( field === selected ) {
				crumb.setAttribute( 'aria-current', 'location' );
			}
			if ( ! index ) {
				return [ crumb ];
			}
			const separator = span( { text: '›', className: 'frm-builder-breadcrumb-separator' } );
			separator.setAttribute( 'aria-hidden', 'true' );
			return [ separator, crumb ];
		} );
		location.replaceChildren( ...crumbs );
	}

	function updateDestination() {
		const placeholder = canvas.querySelector( 'li.sortable-placeholder' );
		const section = placeholder?.closest( SECTION_SELECTOR );
		canvas.querySelectorAll( '.frm-builder-drop-target' ).forEach( target => {
			if ( target !== section ) {
				target.classList.remove( 'frm-builder-drop-target' );
			}
		} );
		section?.classList.add( 'frm-builder-drop-target' );
		const text = placeholder ? sprintf(
			/* translators: %s: Destination section or form. */
			__( 'Add to %s', 'formidable' ), section ? getName( section ) : __( 'form', 'formidable' )
		) : '';
		if ( destination.textContent !== text ) {
			destination.textContent = text;
		}
	}

	function refresh() {
		frame = null;
		observer.disconnect();
		const fields = [ ...canvas.querySelectorAll( FIELD_SELECTOR ) ];
		const sections = fields.filter( field => field.matches( SECTION_SELECTOR ) );
		sections.forEach( prepareSection );
		fields.forEach( field => {
			const id = field.dataset.fid;
			context[ id ] = { ...context[ id ], name: getName( field ) };
		} );
		fields.forEach( updateBadges );
		updateLocation();
		const pages = getPages();
		const hasStructure = Boolean( sections.length || pages.length );
		const hasFieldDetails = Boolean( canvas.querySelector( '.frm-builder-badges' ) );
		layoutMenu.hidden = ! hasStructure && ! hasFieldDetails;
		collapseAll.hidden = ! hasStructure;
		expandAll.hidden = ! hasStructure;
		fieldDetailsOption.hidden = ! hasFieldDetails;
		const menuParent = pages.length && displayArea ? displayArea : publishing;
		if ( layoutMenu.parentElement !== menuParent ) {
			menuParent.append( layoutMenu );
		}
		if ( layoutMenu.hidden ) {
			layoutMenu.open = false;
		}
		const states = [
			...sections.map( section => section.classList.contains( 'frm-section-collapsed' ) ),
			...pages.map( ( { container } ) => container.classList.contains( 'frm-page-collapsed' ) ),
		];
		collapseAll.disabled = states.every( Boolean );
		expandAll.disabled = states.every( state => ! state );
		updateDestination();
		observer.observe( canvas, { childList: true, subtree: true, attributes: true, attributeFilter: [ 'class' ] } );
	}

	function getPages() {
		if ( ! canvas.querySelector( '.edit_field_type_break' ) ) {
			return [];
		}
		return [ ...document.getElementById( 'frm_form_editor_container' ).querySelectorAll( '.frm-collapse-page' ) ].map( control => ( {
			control,
			container: control.closest( '.frm_field_box[data-ftype="break"], #frm-fake-page' ),
		} ) ).filter( ( { container } ) => container );
	}

	function toggleAll( shouldCollapse ) {
		canvas.querySelectorAll( SECTION_SELECTOR ).forEach( section => setCollapsed( section, shouldCollapse ) );
		getPages().forEach( ( { control, container } ) => {
			if ( container.classList.contains( 'frm-page-collapsed' ) !== shouldCollapse ) {
				control.click();
			}
		} );
		layoutMenu.open = false;
		menuToggle.focus();
		scheduleRefresh();
	}
	collapseAll.addEventListener( 'click', () => toggleAll( true ) );
	expandAll.addEventListener( 'click', () => toggleAll( false ) );
	canvas.addEventListener( 'click', event => {
		const control = event.target.closest( '.js-frm-builder-collapse' );
		if ( control ) {
			event.preventDefault();
			event.stopPropagation();
			const section = control.closest( SECTION_SELECTOR );
			setCollapsed( section, ! section.classList.contains( 'frm-section-collapsed' ) );
			scheduleRefresh();
		} else if ( event.target.closest( '.frm-builder-badges' ) ) {
			event.stopPropagation();
		}
	}, true );
	location.addEventListener( 'click', event => {
		const crumb = event.target.closest( '.frm-builder-breadcrumb' );
		const field = crumb && document.getElementById( `frm_field_id_${ crumb.dataset.fieldId }` );
		if ( ! field ) {
			return;
		}
		let section = field.parentElement.closest( SECTION_SELECTOR );
		while ( section ) {
			setCollapsed( section, false );
			section = section.parentElement.closest( SECTION_SELECTOR );
		}
		if ( ! field.classList.contains( 'selected' ) ) {
			const preview = field.querySelector( ':scope > .divider_section_only .frm_inner_field_container, :scope > .frm_inner_field_container' );
			preview?.click();
		}
		field.scrollIntoView( { block: 'center' } );
		field.querySelector( '.frm-move' )?.focus( { preventScroll: true } );
	} );
	document.addEventListener( 'input', scheduleRefresh );
	document.addEventListener( 'change', scheduleRefresh );
	panel.addEventListener( 'click', scheduleRefresh );
	document.getElementById( 'frm_form_editor_container' ).addEventListener( 'click', scheduleRefresh );
	document.addEventListener( 'click', event => {
		if ( ! layoutMenu.contains( event.target ) ) {
			layoutMenu.open = false;
		}
	} );
	layoutMenu.addEventListener( 'keydown', event => {
		if ( event.key === 'Escape' && layoutMenu.open ) {
			layoutMenu.open = false;
			menuToggle.focus();
		}
	} );
	showDetails.addEventListener( 'change', event => {
		event.stopPropagation();
		canvas.classList.toggle( 'frm-builder-hide-details', ! showDetails.checked );
		writePreference( FIELD_DETAILS_STORAGE_KEY, showDetails.checked ? [] : [ 'hidden' ] );
		scheduleRefresh();
	} );
	wp.hooks.addAction( 'frm_renumber_page_breaks', 'formidable/builder-context', scheduleRefresh );
	wp.hooks.addAction( 'frm_field_settings_loaded', 'formidable/builder-context', scheduleRefresh );
	refresh();
}

/**
 * Build readable, keyboard-accessible summaries using saved or currently edited options.
 *
 * @since x.x
 * @param {HTMLElement} field   The field preview.
 * @param {Object}      options Field options.
 * @param {Object}      context Names and options indexed by field ID.
 * @return {Array} Labels and descriptions.
 */
function getBadges( field, options, context ) {
	const badges = [];
	const enabled = ! [ '0', 0, false ].includes( options.enable_conditional_logic );
	const rules = enabled ? Object.values( options.hide_field || {} ).filter( Boolean ) : [];
	const operators = {
		'==': __( 'is', 'formidable' ),
		'!=': __( 'is not', 'formidable' ),
		'>': __( 'is greater than', 'formidable' ),
		'<': __( 'is less than', 'formidable' ),
		'>=': __( 'is at least', 'formidable' ),
		'<=': __( 'is at most', 'formidable' ),
	};
	if ( enabled && ( options.has_rules || rules.length ) ) {
		const isGrouped = options.has_rules ||
			( options.any_all && ! [ 'any', 'all' ].includes( options.any_all ) ) ||
			( options.show_hide && ! [ 'show', 'hide' ].includes( options.show_hide ) ) ||
			Object.values( options.hide_group || {} ).some( group => group && group !== '0' ) ||
			Object.values( options.hide_compare_field || {} ).some( Boolean ) ||
			Object.values( options.hide_compare_measure || {} ).some( Boolean ) ||
			rules.some( ( id, index ) => ! /^\d+$/.test( id ) || ! operators[ options.hide_field_cond?.[ index ] || '==' ] );
		const conditions = rules.map( ( id, index ) => {
			const name = context[ id ]?.name || id;
			const operator = options.hide_field_cond?.[ index ] || '==';
			const value = options.hide_opt?.[ index ] ?? '';
			return `${ name } ${ operators[ operator ] || operator } ${ value }`;
		} );
		const conjunction = options.any_all === 'any' ? __( 'or', 'formidable' ) : __( 'and', 'formidable' );
		const conditionsText = conditions.join( ` ${ conjunction } ` );
		const description = options.show_hide === 'hide' ? sprintf(
			/* translators: %s: Field conditions. */
			__( 'Hidden when %s', 'formidable' ), conditionsText
		) : sprintf(
			/* translators: %s: Field conditions. */
			__( 'Shown when %s', 'formidable' ), conditionsText
		);
		badges.push( [ __( 'Conditional', 'formidable' ), isGrouped ? __( 'Conditional rules control this field. Open its conditional logic settings to review the effects and conditions.', 'formidable' ) : description ] );
	}
	if ( options.calc ) {
		const formula = String( options.calc ).replace( /\[(\d+)\]/g, ( match, id ) => `[${ context[ id ]?.name || id }]` );
		badges.push( [ __( 'Calculated', 'formidable' ), formula ] );
	}
	if ( field.dataset.type === 'hidden' ) {
		badges.push( [ __( 'Hidden', 'formidable' ), __( 'Stores a value without displaying an input on the published form.', 'formidable' ) ] );
	} else if ( [ options.admin_only ].flat().some( value => value && value !== '0' && value !== 'everyone' ) ) {
		badges.push( [ __( 'Restricted visibility', 'formidable' ), __( 'This field has restricted visibility. Check its visibility settings for the allowed roles.', 'formidable' ) ] );
	}
	return wp.hooks.applyFilters( 'frm_builder_context_badges', badges, field, options );
}

/**
 * Read editor preferences. Blocked browser storage must not interrupt the builder.
 *
 * @since x.x
 * @param {string} key The browser preference key.
 * @return {string[]} Stored preference values.
 */
function readPreference( key ) {
	let value;
	try {
		value = JSON.parse( localStorage.getItem( key ) || '[]' );
	} catch {
		return [];
	}
	return Array.isArray( value ) ? value.map( String ) : [];
}

/**
 * Save an editor preference without submitting or changing the form.
 *
 * @since x.x
 * @param {string}   key   The browser preference key.
 * @param {string[]} value Stored preference values.
 * @return {void}
 */
function writePreference( key, value ) {
	try {
		localStorage.setItem( key, JSON.stringify( value ) );
	} catch {
		// Section controls remain usable when storage is disabled or full.
	}
}
