/**
 * Pricing Fields Modal JavaScript
 *
 * @package Formidable
 */

(function($) {
    'use strict';

    // Function to show the pricing fields modal
    function showPricingFieldsModal() {
        var $modal = $('#frm-pricing-fields-modal');
        if ($modal.length === 0) {
            return;
        }

        // Initialize jQuery UI dialog if not already initialized
        if (!$modal.hasClass('ui-dialog-content')) {
            $modal.dialog({
                autoOpen: false,
                modal: true,
                width: 500,
                height: 'auto',
                resizable: false,
                draggable: false,
                position: { my: 'center', at: 'center', of: window },
                create: function() {
                    // Move dialog to the end of body to ensure proper stacking
                    $('.ui-dialog').appendTo('body');
                },
                open: function() {
                    // Add overlay
                    $('.ui-widget-overlay').css({
                        'z-index': 100001,
                        'opacity': 0.7
                    });
                    $('.ui-dialog').css('z-index', 100002);

                    // Prevent body scroll
                    $('body').css('overflow', 'hidden');
                },
                close: function() {
                    // Restore body scroll
                    $('body').css('overflow', '');
                }
            });
        }

        // Open the modal
        $modal.dialog('open');
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Check if we should show the modal
        if (typeof frmGlobal !== 'undefined' && window.frm_show_pricing_modal) {
            // Delay showing modal to allow page to load
            setTimeout(function() {
                showPricingFieldsModal();
            }, 1000);
        }

        // Handle cancel button click
        $(document).on('click', '.frm-cancel-modal', function(e) {
            e.preventDefault();
            $('#frm-pricing-fields-modal').dialog('close');
        });

        // Handle escape key
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // Escape key
                var $modal = $('#frm-pricing-fields-modal');
                if ($modal.length && $modal.dialog('isOpen')) {
                    $modal.dialog('close');
                }
            }
        });
    });

    // Make functions globally accessible
    window.frmPricingModal = {
        show: showPricingFieldsModal
    };

})(jQuery);
