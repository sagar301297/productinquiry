define([
  'jquery',
  'mage/url',
  'mage/translate'
], function ($, urlBuilder, $t) {
  $('#product-inquiry-form').on('submit', function (e) {
      e.preventDefault();

      let form = $(this);
      let formData = form.serialize();
      var actionUrl = urlBuilder.build('productinquiry/index/submit');
      $.ajax({
          url: actionUrl,
          type: 'POST',
          dataType: 'json',
          data: formData,
          showLoader: true, // Show Magento loader
          success: function (response) {
              let messageContainer = $('#product-inquiry-messages');
              messageContainer.empty().show();

              if (response.success) {
                  messageContainer.html('<div class="message success">' + $t(response.message) + '</div>');
                  form[0].reset(); // Reset the form on success
              } else {
                  messageContainer.html('<div class="message error">' + $t(response.message) + '</div>');
              }
          },
          error: function () {
              let messageContainer = $('#product-inquiry-messages');
              messageContainer.empty().show();
              messageContainer.html('<div class="message error">' + $t('An error occurred while submitting the form. Please try again later.') + '</div>');
          }
      });
  });
});