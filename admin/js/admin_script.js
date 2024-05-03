let pluginSlugname = wp360_admin_data.wp360_plugin_slug;
jQuery('tr[data-slug="'+pluginSlugname+'"] .update-message').hide();
var viewHrefVersion = jQuery("#"+pluginSlugname+"-update a").attr("href");
jQuery(".wp360-invoice-view-details").attr('href' , viewHrefVersion)
function wp360toggleCustomFun(elm){
    jQuery(elm).toggle()
}
jQuery(document).ready(function($) {
var currentIndex = 0;
function wp360_invoice_addNewItem() {
  currentIndex++; // Increment the item index
  var newItem = $('.wp360_invoiceItem:first').clone(); // Clone the first invoiceItem
  newItem.find('input').val('');
  newItem.find('input').attr('name', function(index, attr) {
    return attr.replace(/\[0\]/g, '[' + currentIndex + ']');
  });
  newItem.insertBefore('.wp360_invoice_addInvoiceItemCon');
  $('.wp360_invoice_removeInvoiceItem').toggle(currentIndex > 0);
}
function wp360_invoice_removeLastItem() {
  if (currentIndex > 0) {
    $('.wp360_invoiceItem:last').remove();
    currentIndex--;
    $('.wp360_invoice_removeInvoiceItem').toggle(currentIndex > 0);
  }
}
$('.wp360_invoice_addItem').on('click', function() {
    wp360_invoice_addNewItem();
});
$('.wp360_invoice_removeInvoiceItem').on('click', function() {
    wp360_invoice_removeLastItem();
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

jQuery(document).on('click','.wp360-invoice-update-click',function(e){
  e.preventDefault();
  $this =  jQuery(this);
  var data = {
      'action': 'update_wp360_invoice' // Action to handle in PHP
  };
  jQuery.ajax({
      url: wp360_admin_data.ajax_url, // WordPress AJAX URL
      type: 'POST',
      data: data,
      beforeSend: function() {
        $this.closest('.update-message').append('<span class="updating-message">Updating...</span>');
        $this.closest('.update-message').addClass('updating-message');
      },
      success: function(response) {
          $this.closest('.update-message').removeClass('updating-message');
          let responseData = response.data;
          var trElement    = jQuery('tr[data-slug="wp360-invoice"]');
          var divElement   = trElement.find('.plugin-version-author-uri');
          divElement.html('Version ' + responseData.aviliableVersion + ' | By <a href="https://wp360.in/">wp360</a>');
           $this.parent().find('.update-message').remove();
           var pluginCountElement = jQuery('.plugin-count');
          if (pluginCountElement.length) {
              var currentCount = parseInt(pluginCountElement.html());
              var newCount = currentCount - 1;
              pluginCountElement.text(newCount);
          }
          var updatemesageHtml = '<div class="update-message notice inline notice-alt updated-message notice-success"><p aria-label="wp360">'+responseData.message+'</p></div>';
          jQuery(".wp360_success_update .plugin-update").html(updatemesageHtml);
      },
      error: function(xhr, status, error) {
          console.error(error);
          jQuery('.updating-message').remove();
      }
  });
});

