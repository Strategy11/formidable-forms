<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="notice notice-info is-dismissible frm-review-notice">
	<div class="frm-satisfied">
		<p>
			<?php echo esc_html( $title ); ?>
			<br/>
			<?php esc_html_e( 'Are you enjoying Formidable Forms?', 'formidable' ); ?>
		</p>
		<a href="#" class="frm_reverse_button frm_animate_bg show-frm-feedback frm-button-secondary" data-link="feedback">
			<?php esc_html_e( 'Not Really', 'formidable' ); ?>
		</a>
		<a href="#" class="frm_animate_bg show-frm-feedback frm-button-primary" data-link="review">
			<?php esc_html_e( 'Yes!', 'formidable' ); ?>
		</a>
	</div>
	<div class="frm-review-request frm_hidden">
		<p><?php esc_html_e( 'Awesome! Could you do me a BIG favor and give Formidable Forms a review to help me grow my little business and boost our motivation?', 'formidable' ); ?></p>
		<p>- Steph Wells<br/>
			<span><?php esc_html_e( 'Co-Founder and CTO of Formidable Forms', 'formidable' ); ?><span>
		</p>
		<a href="#" class="frm-dismiss-review-notice frm_reverse_button frm-button-secondary" data-link="no" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'No thanks, maybe later', 'formidable' ); ?>
		</a>
		<a href="https://wordpress.org/support/plugin/formidable/reviews/?filter=5#new-post" class="frm-dismiss-review-notice frm-review-out frm-button-primary" data-link="yes" target="_blank" rel="noopener">
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
</div>
<script type="text/javascript">
	jQuery( document ).ready( function( $ ) {
		$(document).on( 'click', '.frm-dismiss-review-notice, .frm-review-notice .notice-dismiss', function( event ) {

			if ( ! $( this ).hasClass( 'frm-review-out' ) ) {
				event.preventDefault();
			}
			var link = $( this ).data('link');
			if ( typeof link === 'undefined' ) {
				link = 'no';
			}

			frmDismissReview( link );
			$( '.frm-review-notice' ).remove();
		} );

		$(document).on('click', '.frm-feedback-request button', function() {
			frmDismissReview( 'done' );
		} );

		$('.show-frm-feedback').click( function( e ){
			e.preventDefault();
			var link = $(this).data('link');
			var className = '.frm-' + link + '-request';
			jQuery('.frm-satisfied').hide();
			jQuery(className).show();

			if ( className === '.frm-feedback-request' ) {
				var frmapi = $('#frmapi-feedback');
				frmapiGetData( frmapi );
			}
		});
	} );

	function frmDismissReview( link ) {
		jQuery.post( ajaxurl, {
			action: 'frm_dismiss_review',
			link: link,
			nonce: '<?php echo esc_html( wp_create_nonce( 'frm_ajax' ) ); ?>'
		} );
	}

	function frmapiGetData( frmcont ) {
		jQuery.ajax({
			dataType:'json',
			url:frmcont.data('url'),
			success:function(json){
				var form = json.renderedHtml;
				form = form.replace(/<link\b[^>]*(formidableforms.css|action=frmpro_css)[^>]*>/gi, '' );
				frmcont.html(form);
			}
		});
	}
</script>
