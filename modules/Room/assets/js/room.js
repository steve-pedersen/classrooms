(function ($) {
  $(function () {
    $('.room-pills a').on('click', function(e) {
      window.history.replaceState({}, null, ($(e.target).attr('href')));
    });
  });
})(jQuery);