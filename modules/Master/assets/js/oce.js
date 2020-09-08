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


(function ($) {
    $(function () {
      $(document.body).on('click', '.disabled :input', function (e) {
        e.stopPropagation();
        e.preventDefault();
      });
      $('.panel-body button').click(function(){
        if ($('.save-dialog').hasClass('hidden')) {
          $('.save-dialog').removeClass('hidden').addClass('animated pulse');
        }
        return true;
      });

      $('.save-dialog button.dismiss').click(function(){
        $('.save-dialog').addClass('hidden');
      });

      $('.group a.edit').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleSections(true, $(this));
        $('.group a.edit').addClass('hidden');
        var $form = $(this).parents('form');
        if ($('#add-courses').hasClass('hidden')) {
          $('#add-courses').removeClass('hidden fadeOutLeft').addClass('animated fadeInLeft').appendTo($form);
          $form.find('button.remove').removeClass('hidden').addClass('animated fadeIn');
          $form.find('button.action').removeClass('hidden').addClass('animated fadeIn');
          $('.save-dialog').appendTo($form);
          $('div.footer').removeClass('hidden').addClass('animated fadeInDown').appendTo($form);
        }
      });

      $('.footer button.cancel').click(function() {
        var $groups = $(document.getElementById('manage-groups'));
        var $this = $(this);

        toggleSections(false, $this);
        var $form = $this.parents('form');
        resetForm($form);
        $('.group a.edit').removeClass('hidden');
        $('#add-courses').addClass('hidden animated fadeOutLeft').appendTo($groups);
        $form.find('button.remove').addClass('hidden');
        $form.find('button.action').addClass('hidden');
        $('.save-dialog').addClass('hidden').appendTo($groups);
        $('div.footer').addClass('hidden').appendTo($groups);
      });

      $('li.not-owner').tooltip();

      $('form').on('click', 'button.add', function (e) {
        e.stopPropagation();
        e.preventDefault();
        var $this = $(this);
        var $item = $this.parents('.course');
        var $group = $this.parents('form');
        var $checkbox = $this.find('input');
        var coursename = $this.find('.coursename').html();
        var courseid = $checkbox.data('courseid');
        
        var data = {
          shortname: coursename,
          courseid: courseid
        }

        var $original = $item.data('original');
        if ($original)
        {
          $item.remove();
          $original.removeClass('removed');
          $original.find('.remove input').attr('checked', false);
        }
        else
        {
          $item.addClass('added');
          $checkbox.attr('checked', true);
          $(tmpl("new-course", data)).appendTo($group.find('.existing-courses'))
            .data('original', $item);
        }
      });


      $('.group').on('click', 'button.remove', function (e) {
        e.stopPropagation();
        e.preventDefault();

        var $this = $(this);
        var $item = $this.parents('li.course');
        var $group = $this.parents('form');

        var $checkbox = $this.find('input');
        $checkbox.attr('checked', true);
        var coursename = $this.find('.coursename').html();
        var courseid = $checkbox.data('courseid');
          
        var $original = $item.data('original');
        if ($original)
        {
          $item.remove();
          $original.removeClass('added');
          $original.find('.add input').attr('checked', false);
        }
        else
        {
          $item.addClass('removed');

          var data = {
            shortname: coursename,
            courseid: courseid
          }

          $('#add-courses ul .no-courses').remove();
          $(tmpl("removed-course", data)).appendTo('#add-courses ul').data('original', $item);
        }
      });

      $('.group').on('click', 'button.hiding', function (e) {
        e.stopPropagation();
        e.preventDefault();

        hideCourse($(this));
      });

      $('#independent-course').on('click', 'button.hiding', function (e) {
        e.stopPropagation();
        e.preventDefault();

        if (!$('#independent-course').hasClass('disabled'))
        { 
          var $this = $(this);
          toggleSections(true, $this);
          hideCourse($this);
        }
      });

      var hideCourse = function ($button)
      {
        var $checkbox = $button.find('input');
        var $item = $button.parents('li.course');

        if ($button.hasClass('shown'))
        {
          $item.addClass('no-show').removeClass('yes-show');
          $checkbox.attr('checked', true);
          $button.removeClass('shown').addClass('not-shown');
          $('span.state',$button).text('Hidden');
        }
        else
        {
          $item.addClass('yes-show').removeClass('no-show');
          $checkbox.attr('checked', false);
          $button.removeClass('not-shown').addClass('shown');
          $('span.state',$button).text('Shown');
        }
      }

      var toggleSections = function (disabled, $excludeSection)
      {
        if (!$excludeSection.data('disableon'))
        {
          $excludeSection = $excludeSection.parents('[data-disableon="edit"]');
        }

        $('[data-disableon="edit"]').not($excludeSection).toggleClass('disabled', disabled);
      }

      var resetForm = function ($form)
      {
        $form.find('li.course').each(function () {
          var $this = $(this);
          var $original = $this.data('original');

          if ($original)
          {
            $original.removeClass('removed').removeClass('added');
            $original.find('.remove input').attr('checked', false);
            $original.find('.add input').attr('checked', false);
            $this.remove();
          }
          else
          {
            if ($this.data('existing') === '1')
            {
              $this.removeClass('removed').removeClass('added');
              $this.find('.remove input').attr('checked', false);
              $this.find('.add input').attr('checked', false);
            }
            if ($this.data('shown') === '1')
            {
              $this.find('.hiding input').attr('checked', false);
              $this.removeClass('no-show').addClass('yes-show');
              $this.find('.hiding').removeClass('not-shown').addClass('shown');
              $('span.state',this).text('Shown');
            }
            else
            {
              $this.find('.hiding input').attr('checked', true);
              $this.removeClass('yes-show').addClass('no-show');
              $this.find('.hiding').removeClass('shown').addClass('not-shown');
              $('span.state',this).text('Hidden');
            }
          }
        });
      }
    });
})(jQuery);