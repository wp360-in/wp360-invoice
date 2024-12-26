// let pluginSlugname = wp360_admin_data.wp360_plugin_slug;
// jQuery('tr[data-slug="'+pluginSlugname+'"] .update-message').hide();
// var viewHrefVersion = jQuery("#"+pluginSlugname+"-update a").attr("href");
// jQuery(".wp360-invoice-view-details").attr('href' , viewHrefVersion)
function wp360toggleCustomFun(elm){
    jQuery(elm).toggle()
}
jQuery(document).ready(function($) {
var currentIndex = 0;
function wp360invoice_addNewItem() {
  currentIndex++; // Increment the item index
  var newItem = $('.wp360_invoiceItem:first').clone(); // Clone the first invoiceItem
  newItem.find('input').val('');
  newItem.find('input').attr('name', function(index, attr) {
    return attr.replace(/\[0\]/g, '[' + currentIndex + ']');
  });
  newItem.insertBefore('.wp360_invoice_addInvoiceItemCon');
  $('.wp360_invoice_removeInvoiceItem').toggle(currentIndex > 0);
}
function wp360invoice_removeLastItem() {
  if (currentIndex > 0) {
    $('.wp360_invoiceItem:last').remove();
    currentIndex--;
    $('.wp360_invoice_removeInvoiceItem').toggle(currentIndex > 0);
  }
}
$('.wp360_invoice_addItem').on('click', function() {
    wp360invoice_addNewItem();
});
$('.wp360_invoice_removeInvoiceItem').on('click', function() {
    wp360invoice_removeLastItem();
});
$(document).on('change keydown keyup', '.wp360_invoice_itemsCon input', function(){
  let qty = 0;
  let unitPrice = 0;
  let itemPrice = 0;
  let totalPrice = 0;
  $('.wp360_invoice_itemsCon .wp360_invoiceItem').each(function(index){
      qty = $(this).find('.qtyField').val()
      unitPrice = $(this).find('.unitPriceField').val()
      itemPrice = qty * unitPrice
      totalPrice = totalPrice + itemPrice;
      qty = 0;
      unitPrice = 0;
      itemPrice = 0;
  })
  $('#totalAmountField').val(totalPrice)
})
}); 


jQuery(document).ready(function($) {
  // Function to handle dynamic addition and removal of fields
  function setupDynamicFields(tableSelector, addButtonSelector, removeButtonSelector) {
      // Add field
      $(tableSelector).on('click', addButtonSelector, function() {
          let $template = $(this).parents('fieldset').find('.dynamic-field-template').first().clone();
          $template.removeClass('dynamic-field-template');
          $template.addClass('is_removable_field');
          $template.find('textarea').val('');
          $template.show();
          console.log($(this));
          $(this).parent('div').before($template);

          var button = $('#wp360-firm-details-fields').find('.upload-logo-button').last();
          var inputField = button.prev('.logo-url-field');
          var previewImg = button.next('.logo-preview');
          handleMediaUploader(button, inputField, previewImg);
      });

      // Remove field
      $(tableSelector).on('click', removeButtonSelector, function() {
          $(this).closest('.is_removable_field').remove();
      });
  }
  // Setup for Invoice Addresses
  setupDynamicFields('#wp360_invoice-settings-form', '.add-dynamic-field', '.remove-dynamic-field');

  // User Profile Extra Fields
  const fieldTemplate = `
      <div class="wp360-invoice-field">
          <input type="text" name="wp360_invoice_field_names[]" placeholder="Field Name" />
          <input type="text" name="wp360_invoice_field_values[]" placeholder="Value" />
          <button type="button" class="remove-field">Remove</button>
      </div>
  `;

    // Add field button click handler
    $('#wp360_invoice_user_extra_add').on('click', function() {
        $('#wp360_invoice_extra_fields #wp360-invoice-fields-container').append(fieldTemplate);
    });

    // Remove field button click handler
    $('#wp360_invoice_extra_fields #wp360-invoice-fields-container').on('click', '.remove-field', function() {
        $(this).closest('.wp360-invoice-field').remove();
    });

    function handleMediaUploader(button, inputField, previewImg) {
      var mediaUploader;
      button.click(function(e){
          e.preventDefault();
          if (mediaUploader) {
              mediaUploader.open();
              return;
          }
          mediaUploader = wp.media.frames.file_frame = wp.media({
              title: 'Choose Logo',
              button: {
                  text: 'Choose Logo'
              },
              multiple: false
          });
          mediaUploader.on('select', function(){
              var attachment = mediaUploader.state().get('selection').first().toJSON();
              inputField.val(attachment.url);
              previewImg.attr('src', attachment.url).show();
          });
          mediaUploader.open();
      });
    }

    // Toggle between Firm Name and Logo based on radio selection
    function toggleFirmDetails(fieldset) {
        var firmType = fieldset.find('input.toggle-input-type:checked').val();
        var firmNameField = fieldset.find('.firm-name-field');
        var firmLogoField = fieldset.find('.firm-logo-field');

        if (firmType === 'name') {
            firmNameField.show();
            firmLogoField.hide();
            firmLogoField.find('.logo-url-field').val('');
            firmLogoField.find('.logo-preview').hide();
        } else {
            firmNameField.hide();
            firmNameField.find('input').val('');
            firmLogoField.show();
        }
    }

    // Initial toggle for all existing firm rows
    $('.firm-details-row').each(function() {
        var row = $(this);
        toggleFirmDetails(row);
        handleMediaUploader(row.find('.upload-logo-button'), row.find('.logo-url-field'), row.find('.logo-preview'));
    });

    // Toggle inputs on radio button change
    $(document).on('change', '.toggle-input-type', function() {
        var row = $(this).closest('.firm-details-row');
        toggleFirmDetails(row);
    });
    var selectElement = document.getElementById('wp360_invoice_firm');
    selectElement.addEventListener('focus', updateFirmDetails);
    selectElement.addEventListener('change', updateFirmDetails);
    
    function updateFirmDetails() {
        var selectedOption = this.options[this.selectedIndex];
        // Update the values of the relevant fields based on the selected option's data attributes
        document.getElementById('firm_logo').value = selectedOption.getAttribute('data-logo') || '';
        document.getElementById('firm_tagline').value = selectedOption.getAttribute('data-tagline') || '';
        document.getElementById('firm_text_logo').value = selectedOption.getAttribute('data-text-logo') || '';
        document.getElementById('firm_id').value = selectedOption.getAttribute('data-firm-id') || '';
    }
    
    $(".admin-wp360invoice_download").on("click", function(e) {        
        e.preventDefault();
        var inv_id = $(e.target).data('invoice-id');
        var inv_name = $(e.target).data('invoice-name');
        $.ajax({
            url: wp360_pdf_ajax_admin.ajax_url,
            method: 'POST',
            data: {
                action: 'generate_invoice_pdf',
                nonce: wp360_pdf_ajax_admin.nonce,
                invoice_data: inv_id
            },
            xhrFields: {
                responseType: 'blob'
            },
            beforeSend: function(){
                $(".admin-wp360invoice_download").addClass('disabled').css("pointer-events", "none");
            },
            success: function(response, status, xhr) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = `invoice-${inv_name}.pdf`;
                link.click(); // Programmatically trigger the download
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error generating PDF: ' + errorThrown);
            },
            complete: function(){
                setTimeout(function() {
                    $(".admin-wp360invoice_download").removeClass('disabled').css("pointer-events", "auto");
                }, 2000);
            }
        });
    });
    $('td.invoice_receipt a').on('click', function(e) {
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
