jQuery(document).ready(function($) {
    jQuery("#wp360-invoice_printinvoice").on("click", function(e) {
        // var originalTitle = document.title; 
        // let invoiceid = jQuery(this).data('id');
        // document.title = "invoice_#"+invoiceid;
        // window.print();
        // document.title = originalTitle;
        e.preventDefault();
        $.ajax({
            url: wp360_pdf_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'generate_invoice_pdf',
                nonce: wp360_pdf_ajax.nonce,
                invoice_data: $(this).data('id') // Get the invoice ID from the URL
            },
            xhrFields: {
                responseType: 'blob' // Ensures the response is treated as a file (binary)
            },
            success: function(response, status, xhr) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = `invoice-${$('input[name="wp360invoice_id"]').val()}.pdf`;
                link.click(); // Programmatically trigger the download
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error generating PDF: ' + errorThrown);
            }
        });
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
    $('.view_receipt').on('click', function(e) {
        e.preventDefault();
        var imageSrc = $(this).data('image');
        $('#modalImage').attr('src', imageSrc);
        $('#wp360_invoice_receipt_modal').fadeIn();
    });

    $('#wp360_invoice_receipt_modal .close').on('click', function() {
        $('#wp360_invoice_receipt_modal').fadeOut();
    });

    $(window).on('click', function(event) {
        if ($(event.target).is('#wp360_invoice_receipt_modal')) {
            $('#wp360_invoice_receipt_modal').fadeOut();
        }
    });
});