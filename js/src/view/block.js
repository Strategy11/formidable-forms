/**
 * BLOCK: formidable/simple-view
 *
 * Gutenberg block to display selected Formidable View with limited settings
 */

import ViewShortcode from './viewshortcode';
import ViewSelect from './viewselect';
import Icon from './icon';
import Inspector from './inspector';

const { ServerSideRender, Notice } = wp.components;

const { Fragment } = wp.element;
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

if ( formidable_form_selector.pro ) {
	registerBlockType( 'formidable/simple-view', {
		title: __( 'Formidable View', 'formidable' ),
		description: __( 'Display a Formidable View', 'formidable' ),
		icon: Icon,
		category: 'widgets',
		keywords: [
			__( 'data display', 'formidable' ),
			__( 'show entries', 'formidable' ),
		],

		edit: function( { setAttributes, attributes } ) {
			const {
				viewId,
				useDefaultLimit,
			} = attributes;

			const views = formidable_form_selector.views;

			if ( views.length === 0 ) {
				return (
					<Notice status={ 'warning' } isDismissible={ false }>
						{ __( 'This site does not have any Formidable Views.', 'formidable' ) }
					</Notice>
				);
			}

			if ( ! viewId ) {
				return (
					<div className={ 'frm-block-intro-screen' }>
						<div className={ 'frm-block-intro-content' }>
							<Icon></Icon>
							<div className={ 'frm-block-title' }>{ __( 'Formidable View', 'formidable' ) }</div>
							<div className={ 'frm-block-selector-screen' }>
								<ViewSelect
									viewId={ viewId }
									setAttributes={ setAttributes }
									views={ views }
								/>
							</div>
						</div>
					</div>
				);
			}

			return (
				<Fragment>
					<Inspector
						attributes={ attributes }
						setAttributes={ setAttributes }
						views={ views }
					/>
					{ useDefaultLimit &&
					<Notice status={ 'success' } isDismissible={ false }>
						{ __( 'The View block displays up to 20 entries. You can preview the page to see all your entries.', 'formidable' ) }
					</Notice>
					}
					<ServerSideRender
						block="formidable/simple-view"
						attributes={ attributes }
					></ServerSideRender>
				</Fragment>
			);
		},

		save: function( props ) {
			const {
				attributes,
			} = props;
			const { viewId } = attributes;
			return (
				( viewId === undefined ) ?
					'' :
					<Fragment>
						<ViewShortcode { ...attributes } />
					</Fragment>
			);
		},
	} );
}
