(function ($) {
  $(function () {

    $('#existingLicenses').on('change', function (e) {
      if (!!this.value) {
        $('.edit-license').attr('disabled', false);
        let link = $('.edit-license');
        link.attr('href', `${link.attr('data-baseurl')}${this.value}/edit`);
      } else {
        $('.edit-license').attr('disabled', true);
      }
    });

  });
})(jQuery);