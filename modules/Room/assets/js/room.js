(function ($) {
  $(function () {
    $('.room-pills a').on('click', function(e) {
      window.history.replaceState({}, null, ($(e.target).attr('href')));
    });

    // AUTO-COMPLETE
	var autoCompleteUrl = document.baseURI + 'rooms/autocomplete';

	var filterRooms = function (data, users = null) {
		var ids = new Array;
		for (const id in data) {
			ids.push(id);
		}
		// console.log(ids);
		$('.room-card').each(function (i, item) {
			if (ids.includes($(item).attr('id'))) {
				$(this).show(500);
			} else {
				$(this).hide(500);
			}
		});

		if (users && users.length && $('#searchBox').val()) {
			var message = '';
			for (var i = 0; i < users.length; i++) {
				message += users[i];
				if (i < users.length - 1) {
					message += ', ';
				}
			}

			$('#userResultList').text(message);
			$('#userResultMessage').show();
		} else {
			$('#userResultList').text('');
			$('#userResultMessage').hide();		
		}
	}

	$('.autocomplete').autocomplete({
		delay: 50,
		minLength: 2,
		// appendTo: ".search-container",
		source: function (request, response) {
			var term = request.term;

			if (term.length > 1) {
				$.ajax(autoCompleteUrl + '?s=' + term, {
					type: 'get',
					dataType: 'json',
					success: function (o) {
						switch (o.status) {
							case 'success':
								response(filterRooms(o.data, o.users));
								break;
							case 'error':
								response(filterRooms(o.data));
								break;
							default:
								console.log('unknown error');
								break;
						}
					}
				});
			}
			else
			{
				// console.log('deleted query');
				$('.room-card').show(500);
			}
		}
	});

	$('.autocomplete').keyup(function(e){
	    if (e.keyCode == 8) {
	        if ($(this).val() === "") {
	        	$('.room-card').show(500);
	        } else {
				$('#userResultList').text('');
				$('#userResultMessage').hide();		        	
	        }
	    }
	});



    var autoCompleteAccountsUrl = $('base').attr('href') + 'schedules/autocomplete';

    var transformAccounts = function (data) {
      var results = [];
      // console.log(data);
      for (var id in data) {
        var info = data[id];
        results.push({
          value: id,
          username: info.username,
          // label: info.username
          label: info.firstName + ' ' + info.lastName + ' (' + info.username + ')' 
        });
      }

      return results;
    };

    $('.account-autocomplete').autocomplete({
      delay: 200,
      minLength: 3,
      appendTo: ".search-container",
      source: function (request, response) {
        var term = request.term;
        // console.log(autoCompleteAccountsUrl + '?s=' + term);
        if (term.length > 2) {
          $.ajax(`${autoCompleteAccountsUrl}?s=${term}`, {
            type: 'get',
            dataType: 'json',
            success: function (o) {
              switch (o.status) {
                case 'success':
                  // console.log('success!', o.data);
                  response(transformAccounts(o.data));
                  break;
                case 'error':
                  //console.log(o.message);
                  break;
                default:
                  //console.log('unknown error');
                  break;
              }
            }
          });
        }
      },
      select: function (event, ui) {
        event.stopPropagation();
        event.preventDefault();
        var item = ui.item;
        var $self = $(this);
        var shadowId = this.id + '-shadow';
        var $shadow = $('#' + shadowId);

        // console.log(item, $self, shadowId, $shadow);

        if ($shadow.length === 0) {
          $shadow = $('<input type="hidden" name="u">');
          $shadow.attr('id', shadowId);
          $('#autcompleteContainer').prepend($shadow);
        }

        $shadow.attr('value', item.username); // search by username
        this.value = item.label;
      }
    });



  });
})(jQuery);