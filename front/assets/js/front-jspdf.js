jQuery(document).ready(function($) {
    jQuery("#wp360-invoice_printinvoice").on("click", function() {
        var originalTitle = document.title; 
        let invoiceid = jQuery(this).data('id');
        document.title = "invoice_#"+invoiceid;
        window.print();
        document.title = originalTitle;
    });
    function wp360invoice_openPopup() {
        $('#receiptPopup').fadeIn();
    }
    // Function to close the modal
    function wp360invoice_closePopup() {
        $('#receiptPopup').fadeOut();
    }
    $('.wp360_invoice_status_update').click(function(){
        wp360invoice_openPopup();
    });
    $('.closeReceiptModal').click(function(){
        wp360invoice_closePopup();
    });
    // Optional: Close the modal if clicking outside of it
    $(window).click(function(event) {
        if ($(event.target).is('#receiptPopup')) {
            wp360invoice_closePopup();
        }
    });
});