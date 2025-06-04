var map;

$(document).on('click', '[data-modal-map]', function() {
	$('#overlay').fadeIn();
	$(this).parent().find('#inpostModalMap').fadeIn();
});
$('#overlay').on('click', function() {
	$('#overlay').fadeOut();
	$(document).find('#inpostModalMap').fadeOut();
});

$(document).on('click', '[data-inpost-map]', function() {
	$('body').append(loader);
	$.ajax({
		url: 'index.php?route=extension/shipping/inpost/inpost',
		dataType: 'json',
		success: function(json) {
			$('#loader').remove();
			
			console.log(json);
		},
		error: function(data) {
			$('#loader').remove();
			console.log(data.responseText);
		}
	});
});

function getInpostPoits(zip) {
	$.ajax({
		url: '/index.php?route=extension/shipping/inpost/inpost/getData',
		type: 'POST',
		data: { postcode: zip, html: 1 },
		success: function(data) {
			//if(data) $(document).find('[data-modal-map]').text(id+' - '+address);
			$('#inpostlist').html(data);
			// console.log(data);
		},
		error: function(data) {
			console.log(data.responseText);
		}
	});
}

function initMap() {
	var lat = 51.919437, 
		lng = 19.145136, 
		zoom = 7;
	
	var mapOptions = {
		scrollwheel: false,
		navigationControl: true,
		mapTypeControl: false,
		scaleControl: true,
		draggable: true,
		zoomControl: true,
		zoom: parseInt(zoom),
		center: new google.maps.LatLng(lat, lng),
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	
	map = new google.maps.Map(
		document.getElementById("inpost-google-map"), mapOptions
	);

	var markers = [];
	var marker;
	const infowindow = new google.maps.InfoWindow();

	getData(null,null,null).done( function( data ) {
		    //console.log( data );
		$.each( data.data, function(k,v) {
			marker = new google.maps.Marker({
		        position: new google.maps.LatLng(v.lat, v.lng)
			});
			google.maps.event.addListener(marker, "click", (function(marker,v,infowindow) {
				var content = '<div class="iw-map-container text-left">' +
					'<b>' + v.description + '</b><p>' + v.street + ' ' + v.postcode + ' ' + v.city +'</p></div>' +
					'<button type="button" onclick="btnSelectPoint(\'' + v.id + '\', \'' + v.street + ', ' + v.city + '\');" class="btn-sm">wybierz</button>';
				return function() {
					infowindow.setContent(content);
					infowindow.open(map, marker);
			     };
			})(marker,v,infowindow));
			markers.push(marker);
	    });

        new markerClusterer.MarkerClusterer({ markers, map });
	});
	
	google.maps.event.trigger(map, 'resize');
}
	
function getData( postcode, id ) {
	var def = $.Deferred();
    $.getJSON( '/index.php?route=extension/shipping/inpost/inpost/index', { postcode: postcode, id: id } ).done( function( data ) {
		def.resolve({
			data: data.data
		});
    });
    return def;
}

function btnSelectPoint(id, address) {
    $(document).find('#inpostModalMap').fadeOut();
	$('#overlay').fadeOut();
	$.ajax({
		url: '/index.php?route=extension/shipping/inpost/inpost/setData',
		type: 'POST',
		data: { id: id },
		success: function(data) {
			if(data) {
				$(document).find('[data-modal-map]').text(id+' - '+address);
				$("#inpostPointSelected").val(id);
				console.log(data);
			}
		},
		error: function(data) {
			console.log(data.responseText);
		}
		
	});
}