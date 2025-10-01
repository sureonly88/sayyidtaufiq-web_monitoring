@extends('menu')
@section('header')
    <link href="{{secure_asset('adminlte/plugins/datatables/dataTables.bootstrap.css')}}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{secure_asset('adminlte/plugins/select2/select2.min.css')}}">
@endsection
@section('modal')
<div class="modal fade" id="modalmenu">
	<div class="modal-dialog">
		<div class="modal-content">		
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">User</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" role="form" method="post" id="formMenu"> 
					<div class="form-group">
						<label  class="control-label  col-md-3">Nama</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="text" class="form-control" name="nama" id="nama" >	
							<input type="hidden" name="id" id="id">
						</div>
					</div>
					<div class="form-group">
						<label  class="control-label  col-md-3">Parent</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="text" class="form-control" name="parent" id="parent" >
						</div>
					</div>
					<div class="form-group">	
						<label  class="control-label  col-md-3">Url</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="text" class="form-control" name="url" id="url" >	
						</div>
					</div>
					<div class="form-group">	
						<label  class="control-label  col-md-3">Class</label>	
						<div class="col-md-9">
							<input style="width: 100%;" type="text" class="form-control" name="class" id="class">	
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
<h3 class="box-title">Setup Menu</h3>
<div class="row">
<div class="col-md-12">
	<div class="box box-solid box-primary">
		<div class="box-header">
			<h3 class="btn btn disabled box-title">
			<i class="fa fa-user-md"></i>Data Menu</h3>
			<button class="btn btn-default pull-right" id="tambah" type="button">
			<i class="fa fa-plus"></i>Tambah Data</button>
		</div>	

		<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr class="text-blue">
					<th class="center">Nomor</th>
					<th>Nama</th>
					<th>Url</th>
					<th>Class</th>
					<th>Parent</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
			@foreach($list as $no => $r)
				<?php $parent='';
					if ($r->parent_id=='0'){$parent='Parent';}
					else {$parent=$r->parent;}
				?>
				<tr>
					<td  class="text-center">{{++$no}}</td>
					<td>{{$r->name }}</td>
					<td>{{$r->url }}</td>
					<td>{{$r->class }}</td>
					<td>{{$parent }}</td>
					<td>
						<button type='button'  class='btn btn-sm btn-success edit' value='{{$r->id}}'><i class='glyphicon glyphicon-pencil'></i></button>
		           		<button type='button' class='btn btn-sm btn-danger hapus' value='{{$r->id}}'> <i class='glyphicon glyphicon-trash'></i></button>				
						</a>
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
<script src="{{secure_asset('adminlte/plugins/datatables/jquery.dataTables.min.js')}}" type="text/javascript"></script>
<script src="{{secure_asset('adminlte/plugins/datatables/dataTables.bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{secure_asset('adminlte/plugins/select2/select2.full.min.js')}}"></script>
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

	var data = <?= json_encode($paren) ?>;
	$('#parent').select2({
		data: data,
		placeholder: 'Pilih Parent',
		allowClear: true,
		minimumInputLength: 0,
		formatSelection: function(data) { return data.text },
		formatResult: function(data) {
			return  '<span class="label label-info">'+data.id+'</span>'+
						'<strong style="margin-left:5px">'+data.text+'</strong>';

		}
	});

	function tampil(){
		$('#example1').dataTable( {
        "ajax": "{{ url('setting/listMenu') }}",
        "destroy": true,
        "columns": [
            { "data": "nomor" },
            { "data": "name" },
            { "data": "url" },
            { "data": "class" },
            { "data": "parent_id" },
            { "data": "aksi" }
        	],
		    "aoColumnDefs": [ 
		     {
		      "aTargets": [ 4 ],
		      "mRender": function (data, type, full) {
		       if (full.parent_id=='0'){var parent='Parent';}
					else {var parent=full.parent;}

		        return parent;
		        }
		      },
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
        $.getJSON("{{ url('setting/setupMenu') }}/"+$(this).val(), function(data) {
			$('#id').val(data.id);
			$('#nama').val(data.name);
			$('#class').val(data.class);
			$('#url').val(data.url);
			if (data.parent_id=='0'){$('#loket').val('').trigger('change');}
			else {$('#parent').val(data.parent_id).trigger('change');}
			$("#modalmenu").modal('show');
		});
    });
    //tambah
    $("#tambah").on('click',function(){
		$('#id,#nama,#url,#class').val('');
		$('#parent').val('').trigger('change');
		$("#modalmenu").modal('show');
    });
    //simpan
    $("#simpan").on('click',function(){
    if ($("#nama").val()==''||$("#url").val()==''||$("#class").val()==''){
		alert("Lengkapi Pengisian Data");
	}else{	
      var data = $('#formMenu').serialize();
	  $.ajax({
	    type: 'POST',
	    url: "{{ url('setting/setupMenu/simpanMenu') }}",
	    data: data,
	    success: function(data) {
			tampil();
	  		$("#modalmenu").modal('hide');
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
        $.post("{{ url('setting/setupMenu/hapusMenu') }}/"+id, function(data) {
			tampil();
			$("#modalhapus").modal('hide');
		});
    });
</script>
@endsection