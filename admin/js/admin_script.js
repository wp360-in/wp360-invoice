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

