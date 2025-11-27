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
	const { useBlockProps } = wp.blockEditor;

	const UpgradeNotice = ( { text, buttonText, link } ) => (
		<Notice status="warning" isDismissible={ false }>
			{ text }
			<br />
			<a href={ link } target="_blank">
				{ buttonText }
			</a>
		</Notice>
	);

	const blockName = __( 'Formidable Chart', 'formidable' );

	registerBlockType( 'frm-charts/graph', {
		apiVersion: 3,
		title: blockName,
		description: __( 'Display a chart or graph', 'formidable' ),
		icon: (
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 25 23">
				<path stroke="currentColor" strokeLinecap="round" strokeWidth="2.5" d="M23 2v19m-7-7.5V21M9 10.8V21m-7-2.9V21" />
			</svg>
		),
		category: 'design',

		edit: () => {
			const imageWrapperStyles = {
				padding: '38px',
				margin: '0 auto',
				maxWidth: '600px'
			};

			const blockProps = useBlockProps();

			return (
				<div { ...blockProps }>
					<div className="frm-block-intro-screen">
						<div className="frm-block-intro-content">
							<FormidableIcon></FormidableIcon>
							<div className="frm-block-title">{ blockName }</div>
							<div className="frm-block-selector-screen frm_pro_tip" style={ { alignSelf: 'stretch' } }>
								{ formidable_form_selector.chartsAddon.hasAccess ? (
									<UpgradeNotice
										text={ __( 'This site does not have Formidable Charts active.', 'formidable' ) }
										buttonText={ __( 'Install Formidable Charts', 'formidable' ) }
										link={ formidable_form_selector.chartsAddon.link }
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
				</div>
			);
		}
	} );
}() );
