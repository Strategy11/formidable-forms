/* global formidable_form_selector */

import FormidableIcon from '../common/components/icon';

( function() {
	'use strict';

	if ( formidable_form_selector.chartsAddon.installed ) {
		return;
	}

	const { __ } = wp.i18n;
	const { registerBlockType } = wp.blocks;
	const { Notice } = wp.components;

	const UpgradeNotice = ( { text, buttonText, link } ) => (
		<Notice status="warning" isDismissible={ false }>
			{ text }
			<br/>
			<a href={ link } target="_blank">
				{ buttonText }
			</a>
		</Notice>
	);

	const blockName = __( 'Formidable Chart', 'formidable' );

	registerBlockType( 'frm-charts/graph', {
		title: blockName,
		description: __( 'Display a chart or graph', 'formidable' ),
		icon: FormidableIcon,
		category: 'design',

		edit: ( { setAttributes, attributes } ) => {
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
							{ formidable_form_selector.chartsAddon.hasAccess ? (
								<UpgradeNotice
									text={ __( 'This site does not have Formidable Charts active.', 'formidable' ) }
									buttonText={ __( 'Install Formidable Charts', 'formidable' ) }
									link={ formidable_form_selector.modalAddon.link }
								/>
							) : (
								<UpgradeNotice
									text={ __( 'This site does not have Formidable Charts installed.', 'formidable' ) }
									buttonText={ __( 'Upgrade Formidable Forms', 'formidable' ) }
									link={ formidable_form_selector.link }
								/>
							) }

							<div style={ imageWrapperStyles }>
								<img src={ formidable_form_selector.url + '/images/demo-graph.svg' } alt={ blockName } />
							</div>
						</div>
					</div>
				</div>
			);
		}
	} );
}() );
