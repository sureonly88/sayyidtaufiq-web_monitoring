@extends('menu')
@section('header')
    <link href="{{URL::asset('adminlte/plugins/datatables/dataTables.bootstrap.css')}}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{URL::asset('adminlte/plugins/select2/select2.min.css')}}">
@endsection
@section('modal')
<div class="modal fade" id="modaledit">
	<div class="modal-dialog">
		<div class="modal-content">		
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">User</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" role="form" method="post" id="formuser"> 
					<div class="form-group">
						<label  class="control-label  col-md-3">Nama</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="text" class="form-control" name="nama" id="nama" >	
							<input type="hidden" name="id" id="id">
						</div>
					</div>
					<div class="form-group">
						<label  class="control-label  col-md-3">Email</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="text" class="form-control" name="email" id="email" >
						</div>
					</div>
					<div class="form-group">	
						<label  class="control-label  col-md-3">User Name</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="text" class="form-control" name="user" id="user" >	
						</div>
					</div>
					<div class="form-group">	
						<label  class="control-label  col-md-3">Password</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="password" class="form-control" name="password" id="password">	
						</div>
					</div>
					<div class="form-group">	
						<label  class="control-label  col-md-3">Level</label>	
						<div class="col-md-9">
							<select name="level" id="level" class="form-control select2" style="width: 100%;">
								<option value="1">Administrator</option>
								<option value="2">User</option>
								<option value="3">Keuangan</option>
								<option value="4">PDAM</option>
								<option value="5">Multi Loket</option>
								<option value="6">User II</option>
							</select>	
						</div>
					</div>
					<div class="form-group" id="divloket">	
						<label  class="control-label  col-md-3">Loket</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="text" class="form-control" name="loket" id="loket">	
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
<h3 class="box-title">Setup User</h3>
<div class="row">
<div class="col-md-12">
	<div class="box box-solid box-primary">
		<div class="box-header">
			<h3 class="btn btn disabled box-title">
			<i class="fa fa-user-md"></i>Data User</h3>
			<button class="btn btn-default pull-right" id="tambah" type="button">
			<i class="fa fa-plus"></i>Tambah Data</button>
		</div>	

		<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr class="text-blue">
					<th class="center">Nomor</th>
					<th>Nama</th>
					<th>Email</th>
					<th>User</th>
					<th>Level</th>
					<th>Loket</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
			@foreach($list as $no => $r)
				<?php $level='';
					if ($r->level==1){$level='Admin';}
					else if ($r->level==2){$level='User';}
					else if ($r->level==3){$level='Keuangan';}
					else if ($r->level==4){$level='PDAM';}
					else if ($r->level==5){$level='Multi Loket';}
					else if ($r->level==6){$level='User II';}
				?>
				<tr>
					<td class="text-center">{{++$no}}</td> 
					<td>{{$r->name }}</td>
					<td>{{$r->email }}</td>
					<td>{{$r->username }}</td>
					<td>{{$level }}</td>
					<td>{{$r->nama or '-'}}</td>
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

	$('.select2').select2();

	var data = <?= json_encode($lokets) ?>;
	$('#loket').select2({
		data: data,
		placeholder: 'Pilih Loket',
		allowClear: true,
		minimumInputLength: 0,
		multiple: true,
		formatSelection: function(data) { return data.text },
		formatResult: function(data) {
			return  '<span class="label label-info">'+data.id+'</span>'+
						'<strong style="margin-left:5px">'+data.text+'</strong>';

		}
	});

	$('#divloket').hide();
	$('#level').change( function (){
		if($( this ).val() == 2||$( this ).val() == 5||$( this ).val() == 6){
			$('#divloket').show();
		}
		else {
			$('#divloket').hide();
		}
	});

	function tampil(){
		$('#example1').dataTable( {
        "ajax": "{{ url('setting/listUser') }}",
        "destroy": true,
        "columns": [
            { "data": "nomor" },
            { "data": "name" },
            { "data": "email" },
            { "data": "username" },
            { "data": "level" },
            { "data": "nama" },
            { "data": "aksi" }
        	],
		    "aoColumnDefs": [ 
		     {
		      "aTargets": [ 4 ],
		      "mRender": function (data, type, full) {
		        if (full.level==1){var level='Admin';}
				else if (full.level==2){var level='User';}
				else if (full.level==3){var level='Keuangan';}
				else if (full.level==4){var level='PDAM';}
				else if (full.level==5){var level='Multi Loket';}
				else if (full.level==6){var level='User II';}
		        return level;
		        }
		      },
		      {
		      "aTargets": [ 5 ],
		      "mRender": function (data, type, full) {
		        if (!full.nama){var nama='-';}
				else {var nama=full.nama;}

		        return nama;
		        }
		      },
		      {
		      "aTargets": [ 6 ],
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
        $.getJSON("{{ url('setting/setupUser') }}/"+$(this).val(), function(data) {
			$('#id').val(data.id);
			$('#nama').val(data.name);
			$('#email').val(data.email);
			$('#user').val(data.user);
			$('#loket').val(data.id_loket).trigger('change');
			$('#level').val(data.level).trigger('change');
			$('#password').val('');

				if(data.level== 2||data.level == 5||data.level == 6){
					$('#divloket').show();
				}
				else {
					$('#divloket').hide();
				}

			$("#modaledit").modal('show');
		});
    });
    //tambah
    $("#tambah").on('click',function(){
		$('#id,#nama,#email,#user,#password').val('');
		$('#divloket').hide();
		$('#loket').val('').trigger('change');
		$('#level').val('1').change();
		$("#modaledit").modal('show');
    });
    //simpan
    $("#simpan").on('click',function(){
    if ($("#nama").val()==''||$("#email").val()==''||$("#user").val()==''){
		alert("Lengkapi Pengisian Data");
	}else{	
      var data = $('#formuser').serialize();
	  $.ajax({
	    type: 'POST',
	    url: "{{ url('setting/setupUser/simpanUser') }}",
	    data: data,
	    success: function(data) {
	    	if (data.status=="Failed"){
	    		alert("Username Sudah Ada");
	    		$("#user").val('');
	    	}else{
	    		 tampil();
		  		$("#modaledit").modal('hide');
	    	}
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
        $.post("{{ url('setting/setupUser/hapusUser') }}/"+id, function(data) {
			tampil();
			$("#modalhapus").modal('hide');
		});
    });
</script>
@endsection