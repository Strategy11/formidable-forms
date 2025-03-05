<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-review-notice frm-dismissible frm-card-item frm-compact-card-item frm-box-shadow-xxl">
	<div class="frm-satisfied">
		<?php
		FrmAppHelper::icon_by_class(
			'frmfont frm-flag frm_svg40 frm-review-notice-icon frm-text-success-500',
			array(
				'echo'        => true,
				'aria-hidden' => 'true',
			)
		);
		?>
		<h3 class="frm-review-notice-title"><?php esc_html_e( 'Congratulations!', 'formidable' ); ?></h3>
		<p class="frm-review-notice-text"><span><?php echo esc_html( $title ); ?></span> <span><?php esc_html_e( 'Are you enjoying Formidable Forms?', 'formidable' ); ?></span></p>
		<div class="frm-flex frm-flex-center frm-gap-sm">
			<a href="#" class="show-frm-feedback button frm-button-secondary" data-link="feedback">
				<?php esc_html_e( 'Not Really', 'formidable' ); ?>
			</a>
			<a href="#" class="show-frm-feedback button frm-button-primary" data-link="review">
				<?php esc_html_e( 'Yes, I do!', 'formidable' ); ?>
			</a>
		</div>
	</div>

	<div class="frm-review-request frm_hidden">
		<p><?php esc_html_e( 'Awesome! Could you do me a BIG favor and give Formidable Forms a review to help me grow my little business and boost our motivation?', 'formidable' ); ?></p>
		<p>- Steph Wells<br/>
			<span><?php esc_html_e( 'Co-Founder and CTO of Formidable Forms', 'formidable' ); ?><span>
		</p>
		<a href="#" class="frm-dismiss-review-notice button frm-button-secondary" data-link="no" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'No thanks, maybe later', 'formidable' ); ?>
		</a>
		<a href="https://wordpress.org/support/plugin/formidable/reviews/?filter=5#new-post" class="frm-dismiss-review-notice frm-review-out button frm-button-primary" data-link="yes" target="_blank" rel="noopener">
			<?php esc_html_e( 'Ok, you deserve it', 'formidable' ); ?>
		</a>
		<br/>
		<a href="#" class="frm-dismiss-review-notice" data-link="done" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'I already did', 'formidable' ); ?>
		</a>
	</div>

	<div class="frm-feedback-request frm_hidden">
		<p><?php esc_html_e( 'Sorry to hear you aren\'t enjoying building with Formidable. We would love a chance to improve. Could you take a minute and let us know what we can do better?', 'formidable' ); ?></p>

		<div id="frmapi-feedback" class="frmapi-form" data-url="https://sandbox.formidableforms.com/api/wp-json/frm/v2/forms/feedback?return=html&exclude_script=jquery&exclude_style=formidable-css">
			<span class="frm-wait frm_visible_spinner"></span>
		</div>
	</div>

	<a class="dismiss frm-dismiss-review-notice" aria-label="<?php esc_attr_e( 'Dismiss this notice', 'formidable' ); ?>" role="button">
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon' ); ?>
	</a>
</div>

<script type="text/javascript">
	jQuery( document ).ready( function( $ ) {
		$( document ).on(
			'click',
			'.frm-dismiss-review-notice',
			function( event ) {
				if ( ! $( this ).hasClass( 'frm-review-out' ) ) {
					event.preventDefault();
				}
				let link = $( this ).data( 'link' );
				if ( typeof link === 'undefined' ) {
					link = 'no';
				}

				frmDismissReview( link );
				$( '.frm-review-notice' ).remove();
			}
		);

		$( document ).on( 'click', '.frm-feedback-request button', () => frmDismissReview( 'done' ) );

		$( '.show-frm-feedback' ).click(
			function( e ) {
				e.preventDefault();
				const link      = $( this ).data( 'link' );
				const className = '.frm-' + link + '-request';
				jQuery( '.frm-satisfied' ).hide();
				jQuery( className ).show();

				if ( className === '.frm-feedback-request' ) {
					frmapiGetData( $( '#frmapi-feedback' ) );
				}
			}
		);
	} );

	function frmDismissReview( link ) {
		jQuery.post(
			ajaxurl,
			{
				action: 'frm_dismiss_review',
				link,
				nonce: '<?php echo esc_html( wp_create_nonce( 'frm_ajax' ) ); ?>'
			}
		);
	}

	function frmapiGetData( frmcont ) {
		jQuery.ajax({
			dataType: 'json',
			url: frmcont.data( 'url' ),
			success: ( json ) => {
				const form = json.renderedHtml.replace( /<link\b[^>]*(formidableforms.css|action=frmpro_css)[^>]*>/gi, '' );
				frmcont.html( form );
			}
		});
	}
</script>
