$(document).ready(function() {
  $('#reload_captcha').click(function(e) {
    e.preventDefault();
    $.ajax({
      url: ajaxurl,  // The URL for the request. This is defined by WordPress and is used to process requests from the front-end.
      type: 'POST',  // The type of request to make (POST, GET, etc.).
      data: {
        action: 'my_action',  // The name of the action to be executed by the WordPress backend.
        data: 'some data'  // Any data to be sent to the server.
      },
      success: function(response) {  // The function to run if the request is successful.
        // Do something with the response here.
      },
      error: function(error) {  // The function to run if the request fails.
        // Do something with the error here.
      }
    });
  });
});