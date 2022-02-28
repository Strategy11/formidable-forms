( function() {
	/** globals ajaxurl, wp */

	if ( 'undefined' === typeof ajaxurl || 'undefined' === typeof wp ) {
		return;
	}

	const __ = wp.i18n.__;

	const container = document.getElementById( 'frm_applications_container' );
	if ( ! container ) {
		return;
	}

	doJsonFetch( 'get_applications_data' ).then(
		data => renderApplications( data.applications )
	);

	async function doJsonFetch( action ) {
		const response = await fetch( ajaxurl + '?action=frm_' + action );
		const json = await response.json();
		if ( ! json.success ) {
			return Promise.reject( 'JSON result is not successful' );
		}
		return Promise.resolve( json.data );
	}

	function renderApplications( applications ) {
		const templatesNav = getTemplatesNav();

		const templatesGrid = div({ className: 'frm_grid_container frm-application-templates-grid' });
		applications.forEach(
			application => templatesGrid.appendChild( createApplicationCard( application ) )
		);

		const contentWrapper = div({
			className: 'frm-applications-index-content',
			children: [ templatesNav, templatesGrid ]
		});

		container.innerHTML = '';
		container.appendChild( contentWrapper );
	}

	function getTemplatesNav() {
		const nav = div({ className: 'frm-application-templates-nav' });
		// TODO __ i18n.
		const title = document.createElement( 'h3' );
		title.textContent = 'Formidable templates';
		nav.appendChild( title );
		return nav;
	}

	function createApplicationCard( data ) {
		const card = div({
			className: 'frm-application-card',
			children: [
				getCardHeader(),
				div({ className: 'frm-flex' }),
				document.createElement( 'hr' ),
				getCardContent()
			]
		});

		function getCardHeader() {
			const title = document.createElement( 'span' );
			title.textContent = data.name;
			const header = div({
				children: [
					title,
					getUseThisTemplateControl( data ),
					div({ text: data.description })
				]
			});
			return header;
		}

		function getCardContent() {
			const image = document.createElement( 'img' );
			image.setAttribute( 'src', data.icon );
			const content = div({
				children: [ image ]
			});
			return content;
		}

		return card;
	}

	function getUseThisTemplateControl( data ) {
		const control = document.createElement( 'a' );
		// TODO __ i18n.
		control.setAttribute( 'href', '#' );
		control.setAttribute( 'role', 'button' );
		control.textContent = 'Use this template';

		onClickPreventDefault(
			control,
			() => openViewApplicationModal( data )
		);

		return control;
	}

	function openViewApplicationModal( data ) {
		const modal = maybeCreateModal( 'frm_view_application_modal' );

		const title = modal.querySelector( '.frm-modal-title' );
		title.textContent = data.name;

		const content = modal.querySelector( '.frm_modal_content' );
		content.innerHTML = '';
		content.appendChild( getViewApplicationModalContent( data ) );

		const $modal = jQuery( modal );
		if ( ! $modal.hasClass( 'frm-dialog' ) ) {
			initModal( $modal );
		}

		$modal.dialog( 'open' );
	}

	function initModal( $modal ) {
		$modal.dialog(
			{
				dialogClass: 'frm-dialog',
				modal: true,
				autoOpen: false,
				closeOnEscape: true,
				width: '550px',
				resizable: false,
				draggable: false,
				open: function() {
					jQuery( '.ui-dialog-titlebar' ).addClass( 'frm_hidden' ).removeClass( 'ui-helper-clearfix' );
					jQuery( '#wpwrap' ).addClass( 'frm_overlay' );
					jQuery( '.frm-dialog' ).removeClass( 'ui-widget ui-widget-content ui-corner-all' );
					$modal.removeClass( 'ui-dialog-content ui-widget-content' );
					bindClickForDialogClose( $modal );
				},
				close: function() {
					jQuery( '#wpwrap' ).removeClass( 'frm_overlay' );
					jQuery( '.spinner' ).css( 'visibility', 'hidden' );
				}
			}
		);
	}

	function bindClickForDialogClose( $modal ) {
		const closeModal = function() {
			$modal.dialog( 'close' );
		};
		jQuery( '.ui-widget-overlay' ).on( 'click', closeModal );
		$modal.on( 'click', 'a.dismiss', closeModal );
	}

	function getViewApplicationModalContent( data ) {
		const img = document.createElement( 'img' );
		img.src = data.icon;
		return div({
			children: [
				img,
				div({
					text: data.description
				})
			]
		});
	}

	function onClickPreventDefault( element, callback ) {
		element.addEventListener(
			'click',
			function( event ) {
				event.preventDefault();
				callback( event );
			}
		);
	}

	function div({ id, className, children, child, text } = {}) {
		const output = document.createElement( 'div' );
		if ( id ) {
			output.id = id;
		}
		if ( className ) {
			output.className = className;
		}
		if ( children ) {
			children.forEach( child => output.appendChild( child ) );
		} else if ( child ) {
			output.appendChild( child );
		} else if ( text ) {
			output.textContent = text;
		}
		return output;
	}

	function maybeCreateModal( id ) {
		let modal = document.getElementById( id );
		if ( ! modal ) {
			modal = createEmptyModal( id );
			modal.classList.add( 'frm_common_modal' );

			const title = div({ className: 'frm-modal-title' });

			const a = document.createElement( 'a' );
			a.textContent = __( 'Cancel', 'formidable' );
			a.className = 'dismiss';

			const postbox = modal.querySelector( '.postbox' );

			postbox.appendChild(
				div({
					className: 'frm_modal_top',
					children: [
						title,
						div({ child: a })
					]
				})
			);
			postbox.appendChild(
				div({ className: 'frm_modal_content' })
			);
			postbox.appendChild(
				div({ className: 'frm_modal_footer' })
			);
		}
		return modal;
	}

	function createEmptyModal( id ) {
		const modal = div({ id: id, className: 'frm-modal' });
		const postbox = div({ className: 'postbox' });
		const metaboxHolder = div({ className: 'metabox-holder', child: postbox });
		modal.appendChild( metaboxHolder );
		document.body.appendChild( modal );
		return modal;
	}
}() );
