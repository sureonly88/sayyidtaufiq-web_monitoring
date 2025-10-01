@extends('menu')
@section('header')
    <link href="{{secure_url('adminlte/plugins/datatables/dataTables.bootstrap.css')}}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{secure_url('adminlte/plugins/select2/select2.min.css')}}">
    <link href="{{secure_url('adminlte/plugins/datepicker/datepicker3.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('modal')
<div class="modal fade" id="modalStatus">
	<div class="modal-dialog">
		<div class="modal-content">		
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Ubah Status</h4>
			</div>
			<div class="modal-body">
			<form class="form-horizontal" role="form" method="post" id="formstatus"> 
				<div class="form-group">
					<label  class="control-label  col-md-3">Status</label>	
					<div class="col-md-9">	
						<select style="width: 100%;"  name="status" id="status" class="form-control select2">
							<option value="0">Belum Diperiksa</option>
							<option value="1">Belum Fix</option>
							<option value="2">Fix</option>
						</select>
						<input type="hidden" name="tanggal" id="tanggal">
						<input type="hidden" name="jenis" id="jenis">
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
<h3 class="box-title">Rekonsiliasi</h3>
<form class="form-horizontal" role="form" method="post" > 
<div class="box box-solid box-primary">
  <div class="box-header">
    <h3 class="btn btn disabled box-title">
    <i class="fa fa-search"></i> Filter </h3>
  	<a class="btn btn-default btn-sm pull-right" data-widget='collapse' data-toggle="tooltip" title="Collapse" style="margin-right: 5px;">
  	<i class="fa fa-minus"></i></a>
  </div>	
	<div class="box-body">
    	<div class="form-group">	
    		<div class="col-sm-1">	
				<label class="control-label" for="input-status">Tanggal</label>			
			</div>
			<div class="col-sm-6">	
				<input  id="tgl"  name="tgl" type="text" class="form-control datepicker">				
			</div>
    		<div class="col-sm-5">
		        <button type="button" name="filter" id="filter"  class="btn btn-primary">
		        <i class="glyphicon glyphicon-search"></i> Filter</button>		 
		    </div>
		</div>    
  </div>
</div>
</form>

<div class="box box-solid box-primary">
	<div class="box-header">
		<h3 class="btn disabled box-title">
		<i class="fa fa-laptop"></i> Rekap Transaksi</h3>
	</div>		
<div class="box-body">
<table id="example2" class="table table-bordered table-striped">
<thead>
	<tr>
		<th class="text-center" style="min-width:100px">No</th>
		<th class="text-center" style="min-width:100px">Status</th>
		<th class="text-center" style="min-width:150px">Tanggal</th>
		<th class="text-center" style="min-width:150px">Jenis Loket</th>
		<th class="text-center" style="min-width:150px">Jumlah</th>
		<th class="text-center" style="min-width:150px">Rupiah</th>
		<th class="text-center" style="min-width:120px">Aksi</th> 	
	</tr>
</thead>
<tbody>
	<tr>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
</tbody>
</table>
</div>
</div>

<div class="box box-solid box-info">
	<div class="box-header">
		<h3 class="btn disabled box-title">
		<i class="fa fa-eye"></i> Rincian Transaksi</h3>
	</div>		
<div class="box-body">
<table id="example3" class="table table-bordered table-striped">
<thead>
	<tr>
		<th class="text-center" style="min-width:100px">Tanggal</th>
		<th class="text-center" style="min-width:100px">ID Pelanggan</th>
		<th class="text-center" style="min-width:100px">Nama</th>
		<th class="text-center" style="min-width:100px">Periode</th>
		<th class="text-center" style="min-width:100px">Tagihan</th>
		<th class="text-center" style="min-width:100px">Admin</th>
		<th class="text-center" style="min-width:100px">Total</th>
		<th class="text-center" style="min-width:100px">Nama Loket</th>
		<th class="text-center" style="min-width:100px">Jenis Loket</th>
		<th class="text-center" style="min-width:100px">User</th>
		<th class="text-center" style="min-width:100px">Jenis Transaksi</th> 	
	</tr>
</thead>
<tbody>
	<tr>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>
	</tr>
</tbody>
</table>
</div>
</div>

@endsection
@section('plugins')
<script src="{{secure_url('adminlte/plugins/datatables/jquery.dataTables.min.js')}}" type="text/javascript"></script>
<script src="{{secure_url('adminlte/plugins/datatables/dataTables.bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{secure_url('adminlte/plugins/select2/select2.full.min.js')}}"></script>	
<script src="{{secure_url('adminlte/plugins/datepicker/bootstrap-datepicker.js')}}" type="text/javascript"></script>

@endsection

@section('footer')
	<script>	
	$("#example2").dataTable({
	   	"scrollY": 1000,
	   	"scrollX": true,
	   	"scrollCollapse": true,
	   	"autoWidth": true,
	   	"ordering" : false,
	   	"info" : false
	   });

	$("#example3").dataTable({
	   	"scrollY": 1000,
	   	"scrollX": true,
	   	"scrollCollapse": true,
	   	"autoWidth": true,
	   	"ordering" : false
	   });

	$('.select2').select2();
	$(document).ready(function(){
	    $('.datepicker').datepicker({
	      autoclose: true,
	      format :"yyyy-mm-dd"
	    });
	 });

	//	
	function tampil(tanggal){
		var nomor=0;
		$('#example2').dataTable( {
	    "ajax": "{{ url('/transaksi/rekonsiliasi') }}/"+tanggal,
	    "destroy": true,
	    "columns": [
	        { "data": "aksi" },
	        { "data": "status" },
	        { "data": "tanggal" },
	        { "data": "jenis_loket" },
	        { "data": "jumlah",render: $.fn.dataTable.render.number(',', '.', 0, '') },
	        { "data": "rupiah",render: $.fn.dataTable.render.number(',', '.', 0, '') },
	        { "data": "aksi" }
	    	],
		    "aoColumnDefs": [ 
		     {
		      "aTargets": [ 0 ],
		      "mRender": function (data, type, full) {
		          return nomor=nomor+1;
			    }
		      },
		     {
		      "aTargets": [ 1 ],
		      "mRender": function (data, type, full) {
		          if (!full.status||full.status=="0"){
		            var formmatedvalue = "<button value='0/"+full.tanggal+"/ "+full.jenis_loket+"' class='btn btn-xs button_status btn-warning tooltip-info' type='button' data-toggle='tooltip' data-placement='top'  data-original-title='CLick To Update Status'>Belum Diperiksa</button>";
		          }else if (full.status=="1"){
		            var formmatedvalue = "<button value='1/"+full.tanggal+"/ "+full.jenis_loket+"' class='btn btn-xs button_status btn-danger tooltip-info' type='button' data-toggle='tooltip' data-placement='top'  data-original-title='CLick To Update Status'>Belum Fix</button>";
		          }else{
		            var formmatedvalue = "<button value='2/"+full.tanggal+"/ "+full.jenis_loket+"' class='btn btn-xs button_status btn-success tooltip-info' type='button' data-toggle='tooltip' data-placement='top'  data-original-title='CLick To Update Status'>Fix</button>";
		          }
		          return formmatedvalue;
			    }
		      },
		      {
		      "aTargets": [ 6 ],
		      "mRender": function (data, type, full, level) {
		          var formmatedvalue="<button value='"+full.jenis_loket+"/ "+full.tanggal+"'";
		          formmatedvalue=formmatedvalue+" type='button' class='btn btn-primary btn-xs btn-detail'>Detail</button>";
			      formmatedvalue=formmatedvalue+" <a href='{{ url('transaksi/rekon/cetakDetail')}}/"+full.tanggal+"/"+full.jenis_loket+"'" ;
			      formmatedvalue=formmatedvalue+" class='btn btn-success btn-xs'>Excell</a>";
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
	   		"lengthChange" :true,		
		});	
	}
	//filter

    $("#filter").on('click',function(){
    if ($("#tgl").val()==''){
		alert("Pilih Tanggal");
	}else{	   
      var tgl = $('#tgl').val();
      tampil(tgl);
	}  
	}); 

	//ubahstatus	
	$("#example2").on('click','.button_status',function(){
		var a=$(this).val().split("/");
        $('#status').val(a[0]).change();
        $('#tanggal').val(a[1]);
        $('#jenis').val(a[2]);
        $('#modalStatus').modal('show');
    });


	//detail
	$("#example2").on('click','.btn-detail',function(){
		var alamat = $(this).val();
	    $('#example3').dataTable( {
        "ajax": "{{ url('transaksi/rekon/detail/') }}/"+alamat,
        "destroy": true,
        "columns": [
            { "data": "tanggal" },
            { "data": "idpel" },
            { "data": "nama" },
            { "data": "periode" },
            { "data": "tagihan",render: $.fn.dataTable.render.number(',', '.', 0, '') },
            { "data": "admin",render: $.fn.dataTable.render.number(',', '.', 0, '') },
            { "data": "total",render: $.fn.dataTable.render.number(',', '.', 0, '') },
            { "data": "loket_name" },
            { "data": "jenis_loket" },
            { "data": "user_" },
            { "data": "jenis_transaksi" }
        	],
        "scrollY": 1000,
	   	"scrollX": true,
	   	"scrollCollapse": true,
	   	"autoWidth": true,
	   	"ordering" : false,
   		"lengthChange" :true,	
    	});
	});

	//simpan
    $("#simpan").on('click',function(){	
      var data = $('#formstatus').serialize();
	  $.ajax({
	    type: 'POST',
	    url: "{{ url('transaksi/rekonsiliasi/simpanStatus') }}",
	    data: data,
	    success: function(data) {
	    	var tgl = $('#tgl').val();
      		tampil(tgl);
      		$('#modalStatus').modal('hide');
	    }
	  }); 
	}); 	
</script>
@endsection