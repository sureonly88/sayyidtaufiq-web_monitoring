@extends('menu')
@section('header')
    <link href="{{secure_url('adminlte/plugins/datatables/dataTables.bootstrap.css')}}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{secure_url('adminlte/plugins/select2-3.5.1/select2.css')}}">
    <link rel="stylesheet" href="{{secure_url('adminlte/plugins/select2-3.5.1/select2-bootstrap.css')}}">
@endsection

@section('body')
<h3 class="box-title">Setup Loket</h3>
<form class="form-horizontal" role="form" method="post" id="formLoket"> 
<div class="box box-solid box-primary">
  <div class="box-header">
    <h3 class="btn btn disabled box-title">
    <i class="fa fa-search"></i> Setup Loket </h3>
  	<a class="btn btn-default btn-sm pull-right" data-widget='collapse' data-toggle="tooltip" title="Collapse" style="margin-right: 5px;">
  	<i class="fa fa-minus"></i></a>
  </div>	
	<div class="box-body">
    	<div class="form-group">
    		<div class="col-sm-4">	
				<label class="control-label" for="input-status">Loket</label>
				<input  name="loket" id="loket" type="text" class="form-control">				
			</div>
			<div class="col-sm-4">	
				<label class="control-label" for="input-tipe">Jenis</label>
				<select name="tipe" id="tipe" class="form-control select2">
					<option value="1">Loket Pedami</option>
					<option value="2">Loket Luar</option>
					<option value="3">Loket Petugas Lapangan Pedami</option>
					<option value="4">Switching</option>
					<option value="5">Loket Luar (H+1)</option>
					<option value="6">Switching Deposit</option>
				</select>				
			</div>
			<div class="col-sm-4">
		      	<label>Limit</label>
		        <input type="text" class="form-control" required="required" name="limit" id="limit" placeholder="0" >
	      	</div>
	    </div>
	    <div class="form-group">
	    <hr/>	
	    	<div class="col-sm-3">
		      	<label>Share Koperasi</label>
	      	</div>
	    </div>
	    <div class="form-group">  	
			<div class="col-sm-3">
		      	<label>PDAM</label>
		        <input type="text" class="form-control" required="required" name="pdam" id="pdam" placeholder="0" >
	      	</div>
		     <div class="col-sm-3">
		      	 <label >PLN POSTPAID</label>
		        <input type="text" class="form-control" name="pln_postpaid" id="pln_postpaid" placeholder="0">
		    </div>
		    <div class="col-sm-3">
		      	<label>PLN PREPAID</label>
		        <input type="text" class="form-control" name="pln_prepaid" id="pln_prepaid" placeholder="0" >
	      	</div>
		      <div class="col-sm-3">
		      	 <label >PLN NONTAGLIS</label>
		        <input type="text" class="form-control" name="pln_nontaglis" id="pln_nontaglis"  placeholder="0">
		    </div>
    	</div>
    	<div class="form-group">  	
		     <div class="col-sm-3">
		      	 <label >PLN POSTPAID NEW</label>
		        <input type="text" class="form-control" name="pln_postpaid_n" id="pln_postpaid_n" placeholder="0">
		    </div>
		    <div class="col-sm-3">
		      	<label>PLN PREPAID NEW</label>
		        <input type="text" class="form-control" name="pln_prepaid_n" id="pln_prepaid_n" placeholder="0" >
	      	</div>
		      <div class="col-sm-3">
		      	 <label >PLN NONTAGLIS NEW</label>
		        <input type="text" class="form-control" name="pln_nontaglis_n" id="pln_nontaglis_n"  placeholder="0">
		    </div>
    	</div>
    	<div class="form-group">
    		<div class="col-sm-5">
		        <button type="button" name="simpan" id="simpan" class="btn btn-primary"><i class="glyphicon glyphicon-search"></i> Simpan</button>		 
		    </div>
		</div>    
  </div>
</div>
</form>

<div class="box box-solid box-primary">
	<div class="box-header">
		<h3 class="btn disabled box-title">
		<i class="fa fa-laptop"></i> Data Loket</h3>
	</div>		
	<div class="box-body">
	<table id="example2" class="table table-bordered table-striped">
	<thead>
		<tr>
			<th class="center"  rowspan="2">Nomor</th>
			<th  rowspan="2">Nama</th>
			<th  rowspan="2">Kode Loket</th>
			<th  rowspan="2">Jenis</th>
			<th  rowspan="2">Limit</th>
			<th colspan="7" class="text-center">Share Koperasi</th> 	
		</tr>
		<tr>
			<th>PDAM</th>
			<th>PLN POSTPAID</th>
			<th>PLN PREPAID</th>
			<th>PLN NONTAGLIS</th>
			<th>PLN POSTPAID NEW</th>
			<th>PLN PREPAID NEW</th>
			<th>PLN NONTAGLIS NEW</th>
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

@endsection
@section('plugins')
<script src="{{secure_url('adminlte/plugins/datatables/jquery.dataTables.min.js')}}" type="text/javascript"></script>
<script src="{{secure_url('adminlte/plugins/datatables/dataTables.bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{secure_url('adminlte/plugins/select2-3.5.1/select2.min.js')}}"></script>	
<script src="{{secure_url('js/jquery.number.min.js')}}"></script>
<script src="{{secure_url('js/sweetalert.min.js')}}"></script>

@endsection

@section('footer')
	<script>	
	$("#example2").dataTable({
	   	"scrollY": 1000,
	   	"scrollX": true,
	   	"scrollCollapse": true,
	   	"autoWidth": true,
	   	"ordering" : false,
	   	"info" : true
	});

	function tampil()
	{
		$('#example2').dataTable( {
		    "ajax": "{{ url('setting/ajaxLoket') }}",
		    "destroy": true,
		    "columns": [
		        { "data": "nomor" },
		        { "data": "nama" },
		        { "data": "loket_code" },
		        { "data": "tipe" },
		        { "data": "limit",render: $.fn.dataTable.render.number(',', '.', 0, '') },
		        { "data": "pdam",render: $.fn.dataTable.render.number(',', '.', 0, '') },
		        { "data": "pln_postpaid",render: $.fn.dataTable.render.number(',', '.', 0, '') },
		        { "data": "pln_prepaid",render: $.fn.dataTable.render.number(',', '.', 0, '') },
		        { "data": "pln_nontaglis",render: $.fn.dataTable.render.number(',', '.', 0, '') },
		        { "data": "pln_postpaid_n",render: $.fn.dataTable.render.number(',', '.', 0, '') },
		        { "data": "pln_prepaid_n",render: $.fn.dataTable.render.number(',', '.', 0, '') },
		        { "data": "pln_nontaglis_n",render: $.fn.dataTable.render.number(',', '.', 0, '') }
		    	],
			    "aoColumnDefs": [ 
			      {
			      "aTargets": [ 3 ],
			      "mRender": function (data, type, full, level) {
			            if (full.tipe=="1"){var jenis="Loket Pedami";}
						else if (full.tipe=="2"){var jenis="Loket Luar";}
						else if (full.tipe=="3"){var jenis="Loket Petugas Lapangan Pedami";}
						else if (full.tipe=="4"){var jenis="Switching";}
						else if (full.tipe=="5"){var jenis="Loket Luar (H+1)";}
						else if (full.tipe=="6"){var jenis="Switching Deposit";}
						else {var jenis='';}

			          return jenis;
				    }
			      }
			    ],
	        "scrollY": 1000,
		   	"scrollX": true,
		   	"scrollCollapse": true,
		   	"autoWidth": true,
		   	"ordering" : false,
		   	"info" : true		
		});	
	}

	tampil();

	$('.select2').select2();

	var data = <?= json_encode($lokets) ?>;
	var loket = $('#loket').select2({
		data: data,
		placeholder: 'Pilih Loket',
		allowClear: true,
		minimumInputLength: 0,
		formatSelection: function(data) { return data.text },
		formatResult: function(data) {
			return  '<span class="label label-info">'+data.id+'</span>'+
						'<strong style="margin-left:5px">'+data.text+'</strong>';

		}
	});

	$('#loket').change(function(){
		$.getJSON("{{ url('ajaxLokets')}}/"+$(this).val(), function(data) {
			$('#tipe').val(data.tipe).change();
			$('#limit').val(data.limit);
			$('#pdam').val(data.pdam);
			$('#pln_postpaid').val(data.pln_postpaid);
			$('#pln_prepaid').val(data.pln_prepaid);
			$('#pln_nontaglis').val(data.pln_nontaglis);
			$('#pln_postpaid_n').val(data.pln_postpaid_n);
			$('#pln_prepaid_n').val(data.pln_prepaid_n);
			$('#pln_nontaglis_n').val(data.pln_nontaglis_n);
			$('#limit,#pdam,#pln_postpaid,#pln_prepaid,#pln_nontaglis,#pln_postpaid_n,#pln_prepaid_n,#pln_nontaglis_n').number( true,0);
		});
	});

	//simpan
	$("#simpan").on('click',function(){	
      if ($("#loket").val()==''){
		swal("Warning","Pilih Loket", "warning");
	 }else{
	  var data = $('#formLoket').serialize();
	  $.ajax({
	    type: 'POST',
	    url: "{{ url('setting/simpanLoket') }}",
	    data: data,
	    success: function(data) {
	    	swal("Sukses", "Data Berhasil Diupdate", "success");
	    	tampil();
			$('#tipe').val('1').change();
			$("#loket").val("").trigger("change.select2");
			$('#limit').val('0');
			$('#pdam').val('0');
			$('#pln_postpaid').val('0');
			$('#pln_prepaid').val('0');
			$('#pln_nontaglis').val('0');
			$('#pln_postpaid_n').val('0');
			$('#pln_prepaid_n').val('0');
			$('#pln_nontaglis_n').val('0');
			$('#limit,#pdam,#pln_postpaid,#pln_prepaid,#pln_nontaglis,#pln_postpaid_n,#pln_prepaid_n,#pln_nontaglis_n').number( true,0);
	    }
	  });
	  } 
	}); 

	
</script>
@endsection