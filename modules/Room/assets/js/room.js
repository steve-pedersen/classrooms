(function ($) {
  $(function () {
    $('.room-pills a').on('click', function(e) {
      window.history.replaceState({}, null, ($(e.target).attr('href')));
    });

    // AUTO-COMPLETE
	var autoCompleteUrl = document.baseURI + 'rooms/autocomplete';

	var filterRooms = function (data) {
		var ids = new Array;
		for (const id in data) {
			ids.push(id);
		}
		console.log(ids);
		$('.room-card').each(function (i, item) {
			if (ids.includes($(item).attr('id'))) {
				$(this).show(500);
			} else {
				$(this).hide(500);
			}
		});
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
								response(filterRooms(o.data));
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
				console.log('deleted query');
				$('.room-card').show(500);				
			}
		}
	});
  });
})(jQuery);