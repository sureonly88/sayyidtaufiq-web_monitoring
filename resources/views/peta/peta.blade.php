@extends('menu')
@section('header')

@endsection
@section('modal')
<div class="modal fade" id="modalPeta">
	<div class="modal-dialog">
		<div class="modal-content">		
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Input Loket</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" role="form" method="post" id="formPeta"> 
					<div class="form-group">
						<label  class="control-label  col-md-3">Nama</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="text" class="form-control" name="nama" id="nama" >	
							<input type="hidden" name="id" id="id">
						</div>
					</div>
					<div class="form-group">
						<label  class="control-label  col-md-3">Alamat</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="text" class="form-control" name="alamat" id="alamat" >
						</div>
					</div>
					<div class="form-group">	
						<label  class="control-label  col-md-3">Latitude</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="text" class="form-control" name="latitude" id="latitude" readonly> 	
						</div>
					</div>
					<div class="form-group">	
						<label  class="control-label  col-md-3">Longitude</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="text" class="form-control" name="longitude" id="longitude" readonly>	
						</div>
					</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-white" data-dismiss="modal">Batal</button>
				<button type="button" id="simpan" class="btn btn-info" >Simpan</button>
			</div>
			</form>
		</div>
	</div>
</div>
@endsection

@section('body')
<h3 class="box-title">Peta Loket</h3>
<div class="row">
<div class="col-md-12">
	<div class="box box-primary">
		<div class="box-body" id="map" style="width:100%;height:400px;">
		</div>
	</div>
</div>
</div>	
@endsection

@section('plugins')
<!--localhost-->
<!--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAsp5METMC6SjtMIfY8qKJ8gsYrX2LmgQY&callback=initMap" async defer></script>-->
<!--Produksi-->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDgVKVsHehJNIvqNcDmlpGX-XHMuCOc1Pg&callback=initMap" async defer></script>
@endsection

@section('footer')
<script>	

function initMap(){
  $.get("{{ url('peta/ajaxPeta') }}", function(data)  {
  	var map;
	var pdam = {lat: -3.325482, lng: 114.605567};
		map = new google.maps.Map(document.getElementById('map'), {
		  center: pdam,
		  zoom: 15
		});

		var lokasi;
		var infowindow = null;

		infowindow = new google.maps.InfoWindow({
			content: "holding..."
		});

        for (var x=0; x<data.length; x++) { 
       
            var siteLatLng = new google.maps.LatLng(data[x]['latitude'], data[x]['longitude']);
            var marker = new google.maps.Marker({
                position: siteLatLng,
                map: map,
                label:"P",
                html: '<div class="marker-info-win">'+
				      	'<div class="marker-inner-win"><span class="info-content">'+
				        '<h4 class="marker-heading">'+data[x]['nama']+'</h4>'+
				        data[x]['alamat']+
				        '</span>'+
				        '</div></div>'
            });

            google.maps.event.addListener(marker, "click", function () {
                infowindow.setContent(this.html);
                infowindow.open(map, this);
            });
        }


		//add marker
		google.maps.event.addListener(map, 'dblclick', function(event) {
		    var position = {lat: event.latLng.lat(), lng: event.latLng.lng()};
		  place_Marker(map, position);

		});

		var markerBaru;
		function place_Marker(map, location) {
		    if(markerBaru){
		          markerBaru.setPosition(location);
		    }else{
		      markerBaru = new google.maps.Marker({
			    draggable:true,
			    position: location,
			    label:"N",
			    animation: google.maps.Animation.DROP,
			    map: map
		      });
		  	}

		  var infowindow2 = new google.maps.InfoWindow({
		      content: "Input Loket Baru"
		  });

		  infowindow2.open(map,markerBaru);
		  google.maps.event.addListener(markerBaru, 'click', function(event) {
		  	$('#latitude').val(event.latLng.lat());
		  	$('#longitude').val(event.latLng.lng());
		  	$('#modalPeta').modal('show');
		  });  
		}  

		//current
		addYourLocationButton(map);  

  });
}     
 
//tombol current
function addYourLocationButton(map) 
{
    var controlDiv = document.createElement('div');

    var firstChild = document.createElement('button');
    firstChild.style.backgroundColor = '#fff';
    firstChild.style.border = 'none';
    firstChild.style.outline = 'none';
    firstChild.style.width = '28px';
    firstChild.style.height = '28px';
    firstChild.style.borderRadius = '2px';
    firstChild.style.boxShadow = '0 1px 4px rgba(0,0,0,0.3)';
    firstChild.style.cursor = 'pointer';
    firstChild.style.marginRight = '10px';
    firstChild.style.padding = '0px';
    firstChild.title = 'Your Location';
    controlDiv.appendChild(firstChild);

    var secondChild = document.createElement('div');
    secondChild.style.margin = '5px';
    secondChild.style.width = '18px';
    secondChild.style.height = '18px';
    secondChild.style.backgroundImage = "url({{URL::asset('image/mylocation-sprite-1x.png')}})";
    secondChild.style.backgroundSize = '180px 18px';
    secondChild.style.backgroundPosition = '0px 0px';
    secondChild.style.backgroundRepeat = 'no-repeat';
    secondChild.id = 'you_location_img';
    firstChild.appendChild(secondChild);

    google.maps.event.addListener(map, 'dragend', function() {
        $('#you_location_img').css('background-position', '0px 0px');
    });

    infoWindowCurrent = new google.maps.InfoWindow;

    firstChild.addEventListener('click', function() {
        var imgX = '0';
        var animationInterval = setInterval(function(){
            if(imgX == '-18') imgX = '0';
            else imgX = '-18';
            $('#you_location_img').css('background-position', imgX+'px 0px');
        }, 500);
        if(navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                infoWindowCurrent.setPosition(latlng);
	            infoWindowCurrent.setContent('Pian Disini');
	            infoWindowCurrent.open(map);
                map.setCenter(latlng);
                clearInterval(animationInterval);
                $('#you_location_img').css('background-position', '-144px 0px');
            });
        }
        else{
            clearInterval(animationInterval);
            $('#you_location_img').css('background-position', '0px 0px');
        }
    });

    controlDiv.index = 1;
    map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(controlDiv);
} 

//simpan
$("#simpan").on('click',function(){
if ($("#nama").val()==''||$("#alamat").val()==''){
	alert("Lengkapi Pengisian Data");
}else{	
  var data = $('#formPeta').serialize();
  $.ajax({
    type: 'POST',
    url: "{{ url('peta/simpanPeta') }}",
    data: data,
    success: function() {
    	initMap();
    	$('#modalPeta').modal('hide');
    }
  });
}  
}); 
</script>
@endsection