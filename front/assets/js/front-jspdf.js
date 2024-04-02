jQuery(document).ready(function() {
    jQuery("#wp360-invoice_printinvoice").on("click", function() {
        var originalTitle = document.title; 
        let invoiceid = jQuery(this).data('id');
        document.title = "invoice_#"+invoiceid;
        window.print();
        document.title = originalTitle;
    });
});