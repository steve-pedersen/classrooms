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
#require js/bootstrap-multiselect.min.js

#require js/bootstrap-toggle.min.js

#require js/blueimp/js/vendor/jquery.ui.widget.js
#require js/blueimp/js/jquery.iframe-transport.js
#require js/blueimp/js/jquery.fileupload.js
#require js/chart.min.js

#require js/room.js
#require js/software.js


(function ($) {
    $(function () {
      function LightenDarkenColor(col, amt) {
        
          var usePound = false;
        
          if (col[0] == "#") {
              col = col.slice(1);
              usePound = true;
          }
       
          var num = parseInt(col,16);
       
          var r = (num >> 16) + amt;
       
          if (r > 255) r = 255;
          else if  (r < 0) r = 0;
       
          var b = ((num >> 8) & 0x00FF) + amt;
       
          if (b > 255) b = 255;
          else if  (b < 0) b = 0;
       
          var g = (num & 0x0000FF) + amt;
       
          if (g > 255) g = 255;
          else if (g < 0) g = 0;
       
          return (usePound?"#":"") + (g | (b << 8) | (r << 16)).toString(16);
        
      }
      if ($('.software-table').length) {
        const rgba2hex = (rgba) => `#${rgba.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+\.{0,1}\d*))?\)$/).slice(1).map((n, i) => (i === 3 ? Math.round(parseFloat(n) * 255) : parseFloat(n)).toString(16).padStart(2, '0').replace('NaN', '')).join('')}`;
      
        var interval = 0;
        var increment = 6;
        var first = $('.software-table tbody tr').first();
        var masterColor = rgba2hex(first.css('background-color'));
        
        $('.software-table tbody tr').each((i, em) => {
          if (i > 0) {
            interval = ($(em).attr('data-index') % increment) * increment;
            $(em).css('background-color', LightenDarkenColor(masterColor, interval));
          }
        });
      }
      

      $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
      });
      
      $(document.body).on('click', '.disabled :input', function (e) {
        e.stopPropagation();
        e.preventDefault();
      });

      var imageWrapper = '';
      var viewImage = true;
      if ($('.image-gallery').length) {
        imageWrapper = '<a href="#" class="view-image-modal" data-toggle="modal" data-target="#viewImageModal" data-image-src="" data-filename="" data-id="">';
      } else {
        viewImage = false;
        imageWrapper = '<div data-src="null" class="copy-image-btn"><img src="null" class="img-responsive"></div>';
      }

      $('#fileupload').fileupload({
        dataType: "json",
        add: function(e, data) {
          data.context = $('<div class="col-xs-3 image-container"></div>')
            .append($(imageWrapper))
            .appendTo($('#image-gallery .row:last-child'));
          data.submit();
        },
        fail: function(e, data) {
          console.log('failed', data);
        },
        progress: function(e, data) {
          var progress = parseInt((data.loaded / data.total) * 100, 10);
          data.context.css("background-position-x", 100 - progress + "%");
        },
        done: function(e, data) {
          $('#image-gallery').show();
          var id = data._response.result.file.id;
          var filename = data._response.result.file.name;
          var imageSrc = data._response.result.file.fullUrl;
          var roomId = data._response.result.roomId;

          if (viewImage) {
            var deleteLink = `
              <a href="files/${id}/delete?returnTo=rooms/${roomId}/edit&room=${roomId}" id="${id}" class="delete-image pull-left" onclick="return confirm('Are you sure you want to delete this image? Be sure to save any edits to this page first.')">
                <span class="text-danger">Delete <i class="glyphicon glyphicon-remove"></i></span>
              </a>
            `;

            data.context
              .addClass("done")
              .find(".view-image-modal")
              .attr('data-image-src', imageSrc)
              .attr('data-filename', filename)
              .attr('data-id', id)
              .on("click", function (e) {
                $('#viewImageModal .modal-title').text(filename);
                $('#viewImageModal .modal-body img').attr('src', imageSrc);
                $('#viewImageModal .modal-footer').prepend(deleteLink);
              })
              .append(`<img src="${imageSrc}" class="img-responsive">`);
              data.context.append(deleteLink);
          } else {
            data.context
              .addClass("done")
              .find(".copy-image-btn")
              .attr('data-src', imageSrc)
              .on("click", function (e) {
                e.preventDefault();
                $('#copy-message').animate({opacity:1}, 10).animate({opacity:0}, 1000);
                navigator.clipboard.writeText(imageSrc).then(function() {
                  console.log('clipboard successfully set');
                }, function() {
                  console.log('clipboard write failed');
                });
              })
              .find('img')
              .attr('src', imageSrc);
          }

          $('#detailsForm').append(`<input name="newfiles[]" value="${id}" type="hidden">`);
        }
      });
   
      if ($('.image-container').length) {
        $('#image-gallery').show();
      }

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

      $('.view-image-modal').on('click', function (e) {
        var filename = $(this).attr('data-filename');
        var imageSrc = $(this).attr('data-image-src');
        var fileId = $(this).attr('data-id');
        var deleteLink = $('.delete-image#'+fileId).clone().addClass('pull-left');
        $('#viewImageModal .modal-title').text(filename);
        $('#viewImageModal .modal-body img').attr('src', imageSrc);
        $('#viewImageModal .modal-footer').prepend(deleteLink);
      });

      autosize($('textarea.autosize'));

      $('.wysiwyg').wysihtml5({'html': true, 'stylesheets': []});

      $('.datepicker').each(function () {
        var $self = $(this);

        $self.datepicker({
        });
      });




      // // ROOM FILTERS
      $(".multiselect").multiselect({
        enableClickableOptGroups: true,
        includeSelectAllOption: true,
        // nonSelectedText: "Buildings",
        //enableCollapsibleOptGroups: true,
        selectAllText: "Select all",
        maxHeight: 400,
        // button text test code
        buttonText: function(options, select) {
          const label = $(select).attr('label');
          if (
            options.length === 0 ||
            options.length === $(".multiselect option").length
          ) {
            return label;
          } else {
            return label + " (" + options.length + ")";
          }
        }
      });

      $('.singleselect').multiselect({
        buttonText: function(options, select) {
          const label = $(select).attr('label');
          var text = '';
          var val = '';
          $(options).each(function(i, option) {
            if ($(option).is(':checked')) {
              text = $(option).text();
              val = $(option).val();
            }
          });
          
          return label + (val == '' ? " " : " within ") + text;
        }
      });

      // // Reset all
      // $("#clearFilters").on("click", function(e) {
      //   e.preventDefault();
      //   // remove selected options
      //   $('.multiselect').each(function (e) {
      //     $(e).find("option").each(function(element) {
      //       $(element).removeAttr("selected").prop("selected", false);
      //     });
      //   })
        
      //   //refresh multiselect
      //   $(".multiselect").multiselect("refresh");
      // });

    });
})(jQuery);