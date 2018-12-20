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
const { data } = wp;
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

if ( formidable_form_selector.pro ) {
    registerBlockType( 'formidable/simple-view', {
        title: __( 'Formidable View' ),
        description: __( 'Display a Formidable View' ),
        icon: Icon,
        category: 'widgets',
        keywords: [
            __( 'data display' ),
            __( 'show entries' ),
        ],

        edit: function ( { className, isSelected, setAttributes, attributes } ) {
            const {
                view_id,
                use_default_limit,
            } = attributes;

            if ( formidable_form_selector.views.length === 0 ) {
                return (
					<Notice status={ 'warning' } isDismissible={ false }>
						{ __( "This site doesn't have any Formidable Views.", 'formidable' ) }
					</Notice>
                )
            }

            if ( ! view_id ) {
                return (
                    <div className={ "frm-block-intro-screen" }>
                        <div className={ "frm-block-intro-content" }>
                            <Icon></Icon>
                            <div className={ "frm-block-title" }>{ __( 'Formidable View', 'formidable' ) }</div>
                            <div className={ "frm-block-selector-screen" }>
                                <ViewSelect
                                    view_id={ view_id }
                                    setAttributes={ setAttributes }/>
                            </div>
                        </div>
                    </div>
                )
            }


            return (
                <Fragment>
                    <Inspector attributes={ attributes } setAttributes={ setAttributes }/>
                    { use_default_limit &&
					<Notice status={ 'success' } isDismissible={ false }>
						{ __( 'The View block displays up to 20 entries. You can preview the page to see all your entries.', 'formidable' ) }
					</Notice>
                    }
                    <ServerSideRender
                        block="formidable/simple-view"
                        attributes={ attributes }
                    ></ServerSideRender>
                </Fragment>
            )
        },

        save: function ( props ) {
            const {
                attributes
            } = props;
            const { view_id } = attributes;
            return (
                ( view_id === undefined ) ? '' :
                    <Fragment>
                        <ViewShortcode { ...attributes } />
                    </Fragment>
            );
        },
    } );
}