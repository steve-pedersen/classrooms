#require js/jquery.js
#require js/jquery-ui.min.js
#require js/jquery.ui.touch-punch.min.js
#require js/blueimp/tmpl.js
#require js/blueimp/gallery.js
#require js/bootstrap/affix.js
#require js/bootstrap/alert.js
#require js/bootstrap/button.js
#require js/bootstrap/carousel.js
#require js/bootstrap/collapse.js
#require js/bootstrap/dropdown.js
#require js/bootstrap/modal.js
#require js/bootstrap/tooltip.js
#require js/bootstrap/popover.js
#require js/bootstrap/tab.js
#require js/bootstrap/transition.js
#require js/xing/wysihtml5-0.3.0.min.js
#require js/jhollingworth/bootstrap-wysihtml5.js
#require js/bootbox.js
#require js/jquery.autosize.min.js
#require js/jquery.dataTables.min.js
#require js/dataTables.bootstrap.min.js
#require js/dataTables.fixedHeader.min.js

#require js/bootstrap-toggle.min.js

#require js/blueimp/js/vendor/jquery.ui.widget.js
#require js/blueimp/js/jquery.iframe-transport.js
#require js/blueimp/js/jquery.fileupload.js


(function ($) {
    $(function () {
      $(document.body).on('click', '.disabled :input', function (e) {
        e.stopPropagation();
        e.preventDefault();
      });

      $('#fileupload').fileupload({
        dataType: "json",
        add: function(e, data) {
          data.context = $('<div class="col-xs-2"></div>')
            .append($('<div data-src="null" class="copy-image-btn"><img src="null" class="img-responsive"></div>'))
            .appendTo($('#image-gallery .row:last-child'));
          data.submit();
        },
        progress: function(e, data) {
          var progress = parseInt((data.loaded / data.total) * 100, 10);
          data.context.css("background-position-x", 100 - progress + "%");
        },
        done: function(e, data) {
          $('#image-gallery').show();
          data.context
            .addClass("done")
            .find(".copy-image-btn")
            .attr('data-src', data._response.result.file.fullUrl)
            .on("click", function (e) {
              e.preventDefault();
              $('#copy-message').animate({opacity:1}, 10).animate({opacity:0}, 1000);
              var imageUrl = data._response.result.file.fullUrl;
              navigator.clipboard.writeText(imageUrl).then(function() {
                console.log('clipboard successfully set');
              }, function() {
                console.log('clipboard write failed');
              });
            })
            .find('img')
            .attr('src', data._response.result.file.url);
        }
      });
      if ($('.copy-image-btn').length) {
        $('#image-gallery').show();
        $('.copy-image-btn').each(function (index, em) {
          $(em).on('click', function (e) {
            e.preventDefault();
            $('#copy-message').animate({opacity:1}, 10).animate({opacity:0}, 1000);

            var imageUrl = $(em).attr('data-src');
            navigator.clipboard.writeText(imageUrl).then(function() {
              console.log('clipboard successfully set');
            }, function() {
              console.log('clipboard write failed');
            });
          });
        });        
      }

      $('.existing-items select').on('change', function (e) {
        if ($(this).val()) {
          let link = $('.existing-items .edit > a');
          link.attr('href', `${link.attr('data-baseurl')}${$(this).val()}/edit`);
        }
      });


      autosize($('textarea.autosize'));

      $('.wysiwyg').wysihtml5({'html': true, 'stylesheets': []});

      $('.datepicker').each(function () {
        var $self = $(this);

        $self.datepicker({
        });
      });
    });
})(jQuery);