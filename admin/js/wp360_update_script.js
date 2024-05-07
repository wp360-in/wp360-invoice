let pluginSlugname = wp360_admin_data.wp360_plugin_slug;
//console.log("pluginSlugname" + pluginSlugname);
jQuery('tr[data-slug="'+pluginSlugname+'"] .update-message').hide();
var viewHrefVersion = jQuery("#"+pluginSlugname+"-update a").attr("href");
jQuery(".wp360-invoice-view-details").attr('href' , viewHrefVersion)

//console.log("Check Update file " + viewHrefVersion);
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