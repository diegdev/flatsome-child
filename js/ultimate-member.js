jQuery(document).ready(function($) {

  $('.um-account-tab-welcome .live-chat-btn').on('click', function(event) {
    event.preventDefault();
    Beacon("open");
  });

  $('.um-tab-custom-link').on('click', function(event) {
    event.preventDefault();

    window.location = $(this).attr('href');
  });

  $('.um-account-link[data-tab="review"]').on('click', function(event) {
    event.preventDefault();

    window.dispatchEvent(new Event('resize'));
  });

});
