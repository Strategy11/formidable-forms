/* global formidable_form_selector */
import FormidableIcon from '../common/components/icon';
import { frmAddonAPI } from '../api/index';

import buttonStyles from './css/button.module.css';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Notice } = wp.components;
const { useState, useEffect } = wp.element;

const blockData = formidable_form_selector; // eslint-disable-line camelcase
const upgradeLink = blockData.viewsAddon.hasAccess ? blockData.viewsAddon.link : blockData.link;

registerBlockType( 'formidable/simple-view', {
	apiVersion: 3,
	title: __( 'Formidable Views', 'formidable' ),
	description: __( 'Display a Visual View', 'formidable' ),
	icon: FormidableIcon,
	category: 'widgets',
	keywords: [
		'views',
		'formidable'
	],

	edit: () => {
		const [ addonActivateButton, updateAddonActivateButton ] = useState( { // eslint-disable-line react-hooks/rules-of-hooks
			defaultClassname: 'frm-activate-addon frm-button-primary button button-primary',
			loadingClassname: buttonStyles[ 'frm-loading' ],
			classnames: 'frm-activate-addon frm-button-primary button button-primary',
			label: ! blockData.viewsAddon.installed && blockData.viewsAddon.hasAccess ? __( 'Install', 'formidable' ) : __( 'Activate', 'formidable' ),
			isLoading: false
		} );

		const activateViewsAddon = () => {
			if ( true === addonActivateButton.isLoading ) {
				return;
			}
			updateAddonActivateButton( { ...addonActivateButton, isLoading: true, classnames: addonActivateButton.defaultClassname + ' ' + addonActivateButton.loadingClassname } );
			if ( ! blockData.viewsAddon.installed && blockData.viewsAddon.hasAccess ) {
				frmAddonAPI.toggleAddonState( 'frm_install_addon', blockData.viewsAddon.url ).then( () => {
					window.location.reload();
				} );
				return;
			}
			frmAddonAPI.toggleAddonState( 'frm_activate_addon', 'formidable-views/formidable-views.php' ).then( () => {
				window.location.reload();
			} );
		};

		const blockName = __( 'Formidable Views', 'formidable' );
		const imageStyles = {
			maxWidth: '504px',
			height: 'auto',
			borderRadius: '12px'
		};
		const imageWrapperStyles = {
			padding: '38px',
			textAlign: 'center',
			backgroundColor: '#f2f4f7',
			marginTop: '24px'
		};

		return (
			<div className="frm-block-intro-screen">
				<div className="frm-block-intro-content">
					<FormidableIcon></FormidableIcon>
					<div className="frm-block-title">{ blockName }</div>
					<div className="frm-block-selector-screen frm_pro_tip" style={ { alignSelf: 'stretch' } }>
						{ ! blockData.viewsAddon.hasAccess &&
							<Notice status="warning" isDismissible={ false }>
								<div style={ { maxWidth: '500px', margin: 'auto' } }>
									{ __( 'Effortlessly transform form data into webpages with Views, the only integrated form & application builder.', 'formidable' ) }
								</div>
								<br />
								<a href={ upgradeLink } rel="noreferrer" target="_blank" >
									{ __( 'Upgrade Formidable Forms', 'formidable' ) }
								</a>
							</Notice>

						}
						{ blockData.viewsAddon.hasAccess &&
							<Notice status="warning" isDismissible={ false }>
								<div style={ { maxWidth: '500px', margin: 'auto' } }>
									{ __( 'Effortlessly transform form data into webpages with Views, the only integrated form & application builder.', 'formidable' ) }
								</div>
								<br />
								<button className={ addonActivateButton.classnames } onClick={ activateViewsAddon } type="button"> { addonActivateButton.label } </button>
							</Notice>
						}
						<div style={ imageWrapperStyles }>
							<img src={ blockData.url + '/images/blocks/views-block-placeholder.jpg' } alt={ blockName } style={ imageStyles } />
						</div>
					</div>
				</div>
			</div>
		);
	}
} );
