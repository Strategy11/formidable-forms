module.exports = function createFieldLayout( { UNRENDERED_FIELD_SELECTOR, adminState, builderPage, ensureFieldSettings, makeTabbable, moveFieldSettings } ) {
	const { __ } = wp.i18n;
	const { a, div, span, svg } = frmDom;

	function getFieldsInRow( $row ) {
		let $fields = jQuery();

		const row = $row.get( 0 );
		if ( ! row.children ) {
			return $fields;
		}

		Array.from( row.children ).forEach(
			child => {
				if ( 'none' === child.style.display ) {
					return;
				}

				const classes = child.classList;
				if ( ! classes.contains( 'form-field' ) || classes.contains( 'edit_field_type_end_divider' ) || classes.contains( 'frm-sortable-helper' ) ) {
					return;
				}

				$fields = $fields.add( child );
			}
		);
		return $fields;
	}

	function syncLayoutClasses( $item, type ) {
		let classToAddFunction;

		if ( type === undefined ) {
			type = 'even';
		}

		const $fields = $item.parent().children( 'li.form-field, li.frmbutton_loadingnow' ).not( '.edit_field_type_end_divider' );
		const size = $fields.length;
		const layoutClasses = getLayoutClasses();

		if ( 'even' === type && 5 !== size ) {
			$fields.each( getSyncLayoutClass( layoutClasses, getEvenClassForSize( size ) ) );
		} else if ( 'clear' === type ) {
			$fields.each( getSyncLayoutClass( layoutClasses, '' ) );
		} else {
			if ( [ 'left', 'right', 'middle', 'even' ].includes( type ) ) {
				classToAddFunction = function( index ) {
					return getClassForBlock( size, type, index );
				};
			} else {
				classToAddFunction = function( index ) {
					const size = type[ index ];
					return getLayoutClassForSize( size );
				};
			}

			$fields.each( getSyncLayoutClass( layoutClasses, classToAddFunction ) );
		}

		updateFieldGroupControls( $item.parent(), $fields.length );
	}

	function updateFieldGroupControls( $row, count ) {
		const row = $row.get( 0 );
		if ( ! row || row.querySelector( `:scope > :is(${ UNRENDERED_FIELD_SELECTOR })` ) ) {
			return;
		}

		const shouldShowControls = count >= 2;

		let controls = adminState.fieldGroupControls;
		if ( ! controls?.isConnected ) {
			controls = document.getElementById( 'frm_field_group_controls' );
		}
		if ( ! controls ) {
			if ( ! shouldShowControls ) {
				// exit early. if we do not need controls and they do not exist, do nothing.
				return;
			}

			controls = div();
			controls.id = 'frm_field_group_controls';
			controls.setAttribute( 'role', 'group' );
			controls.setAttribute( 'tabindex', 0 );
			setFieldControlsHtml( controls );
			builderPage.append( controls );
		}

		adminState.fieldGroupControls = controls;
		if ( controls.parentElement !== row ) {
			row.append( controls );
		}
		controls.style.display = shouldShowControls ? 'block' : 'none';
	}

	function setFieldControlsHtml( controls ) {
		const layoutOption = document.createElement( 'span' );
		layoutOption.innerHTML = '<svg class="frmsvg"><use href="#frm_field_group_layout_icon"></use></svg>';
		const layoutOptionLabel = __( 'Set Row Layout', 'formidable' );
		addTooltip( layoutOption, layoutOptionLabel );
		makeTabbable( layoutOption, layoutOptionLabel );

		const moveOption = document.createElement( 'span' );
		moveOption.innerHTML = '<svg class="frmsvg"><use href="#frm_thick_move_icon"></use></svg>';
		moveOption.classList.add( 'frm-move' );
		const moveOptionLabel = __( 'Move Field Group', 'formidable' );
		addTooltip( moveOption, moveOptionLabel );
		makeTabbable( moveOption, moveOptionLabel );

		controls.innerHTML = '';
		controls.append( layoutOption );
		controls.append( moveOption );
		controls.append( getFieldControlsDropdown() );
	}

	function addTooltip( element, title ) {
		element.setAttribute( 'data-bs-toggle', 'tooltip' );
		element.setAttribute( 'data-bs-container', 'body' );
		element.setAttribute( 'title', title );
		element.addEventListener(
			'mouseover',
			function() {
				if ( ! element.__bootstrapTooltip ) {
					element.__bootstrapTooltip = new bootstrap.Tooltip( element );
				}
			}
		);
	}

	function getFieldControlsDropdown() {
		const dropdown = span( { className: 'dropdown' } );
		const trigger = a( {
			className: 'frm_bstooltip frm-hover-icon frm-dropdown-toggle dropdown-toggle',
			children: [
				span( {
					child: svg( { href: '#frm_thick_more_vert_icon' } )
				} ),
				span( {
					className: 'screen-reader-text',
					text: __( 'Toggle More Options Dropdown', 'formidable' )
				} )
			]
		} );

		frmDom.setAttributes(
			trigger,
			{
				title: __( 'More Options', 'formidable' ),
				'data-bs-toggle': 'dropdown',
				'data-bs-container': 'body',
				'data-bs-display': 'static'
			}
		);
		makeTabbable( trigger, __( 'More Options', 'formidable' ) );
		dropdown.append( trigger );

		const ul = div( {
			className: 'frm-dropdown-menu dropdown-menu dropdown-menu-right'
		} );
		ul.setAttribute( 'role', 'menu' );
		dropdown.append( ul );

		return dropdown;
	}

	function getSyncLayoutClass( layoutClasses, classToAdd ) {
		return function( itemIndex ) {
			const currentClassToAdd = 'function' === typeof classToAdd ? classToAdd( itemIndex ) : classToAdd;
			const { length } = layoutClasses;
			let activeLayoutClass = false;
			for ( let layoutClassIndex = 0; layoutClassIndex < length; ++layoutClassIndex ) {
				const currentClass = layoutClasses[ layoutClassIndex ];
				if ( this.classList.contains( currentClass ) ) {
					activeLayoutClass = currentClass;
					break;
				}
			}

			const fieldId = this.dataset.fid;

			if ( fieldId === undefined ) {
				// we are syncing the drag/drop placeholder before the actual field has loaded.
				// this will get called again afterward and the input will exist then.
				this.classList.add( currentClassToAdd );
				return;
			}

			moveFieldSettings( document.getElementById( `frm-single-settings-${ fieldId }` ) );
			let layoutClassesInput = document.getElementById( `frm_classes_${ fieldId }` );

			if ( ! layoutClassesInput ) {
				// not every field type has a layout class input.
				return;
			}

			const previousClasses = layoutClassesInput.value;
			if ( false === activeLayoutClass ) {
				if ( '' !== currentClassToAdd ) {
					layoutClassesInput.value = layoutClassesInput.value.concat( ` ${ currentClassToAdd }` );
				}
			} else {
				this.classList.remove( activeLayoutClass );
				layoutClassesInput.value = layoutClassesInput.value.replace( activeLayoutClass, currentClassToAdd );
			}

			if ( this.classList.contains( 'frm_first' ) ) {
				this.classList.remove( 'frm_first' );
				layoutClassesInput.value = layoutClassesInput.value.replace( 'frm_first', '' ).trim();
			}

			if ( 0 === itemIndex ) {
				this.classList.add( 'frm_first' );
				layoutClassesInput.value = layoutClassesInput.value.concat( ' frm_first' );
			}

			if ( layoutClassesInput.closest( '.frm-deferred-settings-meta' ) ) {
				if ( previousClasses === layoutClassesInput.value ) {
					return;
				}
				moveFieldSettings( ensureFieldSettings( fieldId ) );
				layoutClassesInput = document.getElementById( `frm_classes_${ fieldId }` );
			}
			jQuery( layoutClassesInput ).trigger( 'change' );
		};
	}

	function getLayoutClasses() {
		return [ 'frm_full', 'frm_half', 'frm_third', 'frm_fourth', 'frm_sixth', 'frm_two_thirds', 'frm_three_fourths', 'frm1', 'frm2', 'frm3', 'frm4', 'frm5', 'frm6', 'frm7', 'frm8', 'frm9', 'frm10', 'frm11', 'frm12' ];
	}

	/**
	 * @param {number} size  2-12.
	 * @param {string} type  even, middle, left, or right.
	 * @param {number} index 0-5.
	 * @return {string} The class name.
	 */
	function getClassForBlock( size, type, index ) {
		if ( 'even' === type ) {
			return getEvenClassForSize( size, index );
		} else if ( 'middle' === type ) {
			if ( 3 === size ) {
				return 1 === index ? 'frm6' : 'frm3';
			}
			if ( 5 === size ) {
				return 2 === index ? 'frm4' : 'frm2';
			}
		} else if ( 'left' === type ) {
			return 0 === index ? getLargeClassForSize( size ) : getSmallClassForSize( size );
		} else if ( 'right' === type ) {
			return index === size - 1 ? getLargeClassForSize( size ) : getSmallClassForSize( size );
		}
		return 'frm12';
	}

	/**
	 * @param {number}           size  2-12.
	 * @param {number|undefined} index 0-5.
	 * @return {string} The class name.
	 */
	function getEvenClassForSize( size, index ) {
		if ( size > 6 ) {
			return 'frm1';
		}
		if ( [ 2, 3, 4, 6 ].includes( size ) ) {
			return getLayoutClassForSize( 12 / size );
		}
		if ( 5 === size && index !== undefined ) {
			return 0 === index ? 'frm4' : 'frm2';
		}
		return 'frm12';
	}

	function getSmallClassForSize( size ) {
		switch ( size ) {
			case 2: case 3:
				return 'frm3';
			case 4:
				return 'frm2';
			case 5:
				return 'frm2';
			case 6:
				return 'frm1';
			default:
				return 'frm12';
		}
	}

	function getLargeClassForSize( size ) {
		switch ( size ) {
			case 2:
				return 'frm9';
			case 3: case 4:
				return 'frm6';
			case 5:
				return 'frm4';
			case 6:
				return 'frm7';
			default:
				return 'frm12';
		}
	}

	function getLayoutClassForSize( size ) {
		return `frm${ size }`;
	}

	return {
		getClassForBlock,
		getEvenClassForSize,
		getFieldsInRow,
		getLayoutClasses,
		syncLayoutClasses,
		updateFieldGroupControls,
	};
};
