@extends('menu')
@section('header')
    <link href="{{URL::asset('adminlte/plugins/datatables/dataTables.bootstrap.css')}}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{URL::asset('adminlte/plugins/select2/select2.min.css')}}">
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
							<input style="width: 100%;" type="text" class="form-control" name="latitude" id="latitude"> 	
						</div>
					</div>
					<div class="form-group">	
						<label  class="control-label  col-md-3">Longitude</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="text" class="form-control" name="longitude" id="longitude">	
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

<div class="modal fade" id="modalhapus">
	<div class="modal-dialog">
		<div class="modal-content">		
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Hapus Data ?</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" name="idhapus" id="idhapus">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-white" data-dismiss="modal">Batal</button>
				<button type="button" id="hapusdata" class="btn btn-danger" >Hapus</button>
			</div>
		</div>
	</div>
</div>
@endsection

@section('body')
<h3 class="box-title">Setup Loket (Peta)</h3>
<div class="row">
<div class="col-md-12">
	<div class="box box-solid box-primary">
		<div class="box-header">
			<h3 class="btn btn disabled box-title">
			<i class="fa fa-user-md"></i>Data Loket (Peta)</h3>
			<button class="btn btn-default pull-right" id="tambah" type="button">
			<i class="fa fa-plus"></i>Tambah Data</button>
		</div>	

		<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr class="text-blue">
					<th class="center">Nomor</th>
					<th>Nama</th>
					<th>Alamat</th>
					<th>Latitude</th>
					<th>Longitude</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
			@foreach($list as $no => $r)
				<tr>
					<td  class="text-center">{{++$no}}</td>
					<td>{{$r->nama }}</td>
					<td>{{$r->alamat }}</td>
					<td>{{$r->latitude }}</td>
					<td>{{$r->longitude }}</td>
					<td>
						<button type='button'  class='btn btn-sm btn-success edit' value='{{$r->id}}'><i class='glyphicon glyphicon-pencil'></i></button>
		           		<button type='button' class='btn btn-sm btn-danger hapus' value='{{$r->id}}'> <i class='glyphicon glyphicon-trash'></i></button>				
					</td>
				</tr>
			@endforeach	
			</tbody>
		</table>
		</div>
	</div>
</div>
</div>	
@endsection

@section('plugins')
<script src="{{URL::asset('adminlte/plugins/datatables/jquery.dataTables.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('adminlte/plugins/datatables/dataTables.bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('adminlte/plugins/select2/select2.full.min.js')}}"></script>
@endsection

@section('footer')
<script>	
	$("#example1").dataTable({
	   	"scrollY": 1000,
	   	"scrollX": true,
	   	"scrollCollapse": true,
	   	"autoWidth": true,
	   	"ordering" : false,
	   	"info" :false,
	   	"lengthChange" :false
	   });

	function tampil(){
		$('#example1').dataTable( {
        "ajax": "{{ url('peta/ajaxListLoket') }}",
        "destroy": true,
        "columns": [
            { "data": "nomor" },
            { "data": "nama" },
            { "data": "alamat" },
            { "data": "latitude" },
            { "data": "longitude" },
            { "data": "aksi" }
        	],
		    "aoColumnDefs": [ 
		      {
		      "aTargets": [ 5 ],
		      "mRender": function (data, type, full) {
		          var formmatedvalue = "<button type='button'  class='btn btn-sm btn-success edit' ";
		          formmatedvalue = formmatedvalue+" value='"+full.id+"'> <i class='glyphicon glyphicon-pencil'></i></button>";
		          formmatedvalue = formmatedvalue+" <button type='button' class='btn btn-sm btn-danger hapus' value='"+full.id+"'>";
		          formmatedvalue = formmatedvalue+" <i class='glyphicon glyphicon-trash'></i></button>";
		          return formmatedvalue;
		        }
		      },
		    ],
	        "scrollY": 1000,
		   	"scrollX": true,
		   	"scrollCollapse": true,
		   	"autoWidth": true,
		   	"ordering" : false,
		   	"info" : false,
	   		"lengthChange" :false	
    	});
	}

	//edit
	$("#example1").on('click','.edit',function(){
        $.getJSON("{{ url('peta/listLoket') }}/"+$(this).val(), function(data) {
			$('#id').val(data.id);
			$('#nama').val(data.nama);
			$('#alamat').val(data.alamat);
			$('#latitude').val(data.latitude);
			$('#longitude').val(data.longitude);
			$("#modalPeta").modal('show');
		});
    });
    //tambah
    $("#tambah").on('click',function(){
		$('#id,#nama,#alamat,#latitude,#longitude').val('');
		$("#modalPeta").modal('show');
    });
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
	    	$('#modalPeta').modal('hide');
	    	tampil();
	    }
	  });
	}  
	});   
    //hapus
	$("#example1").on('click','.hapus',function(){
		$('#idhapus').val($(this).val());
		$("#modalhapus").modal('show');
    });

    $("#hapusdata").on('click',function(){
		var id = $("#idhapus").val();
        $.post("{{ url('peta/hapusLoketPeta') }}/"+id, function(data) {
			tampil();
			$("#modalhapus").modal('hide');
		});
    });
</script>
@endsection