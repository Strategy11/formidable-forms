( function( jQuery ) {
	'use strict';

	jQuery.widget( 'custom.frmCustomDropdown', jQuery.ui.selectmenu, {
		_renderItem: function( ul, item ) {
			console.log( item );
			console.log( ul );

			const li = frmDom.tag( 'li', {
				text: item.label,
				data: {
					value: item.value
				}
			});

			if ( item.disabled ) {
				li.classList.add( 'ui-state-disabled' );

				/*const useTag = frmDom.tag( 'use' );
				useTag.setAttribute( 'xlink:href', '#frm_lock_icon' );

				const proText = frmDom.span({
					children: [
						'(Pro ',
						frmDom.tag( 'svg', {
							className: 'frmsvg frm_lock_icon frm_svg14',
							child: useTag
						}),
						')'
					]
				});*/

				// Use jQuery instead of JS because the SVG icon doesn't show.
				jQuery( li ).append( '<span>(Pro <svg class="frmsvg frm_lock_icon frm_svg14"><use xlink:href="#frm_lock_icon"></use></svg>)</span>' );

				// li.appendChild( proText );
			}

			return jQuery( li ).appendTo( ul );
		}
	});

	const hookNamespace = 'formidable';

	const onFilledFormAction = function( inside ) {
		const instance = inside.find( '.frm-custom-dropdown' ).frmCustomDropdown({
			position: {
				my: 'left top',
				at: 'left bottom',
				collision: 'flip'
			}
		});
		instance.frmCustomDropdown( 'menuWidget' ).addClass( 'frm-custom-dropdown-menu' );
	};

	wp.hooks.addAction( 'frm_filled_form_action', hookNamespace, onFilledFormAction );
}( jQuery ) );
