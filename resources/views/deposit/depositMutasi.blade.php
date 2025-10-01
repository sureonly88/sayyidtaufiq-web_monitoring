@extends('menu')
@section('header')
    <link href="{{secure_url('adminlte/plugins/datatables/dataTables.bootstrap.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{secure_url('adminlte/plugins/datepicker/datepicker3.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('body')
<h3 class="box-title">Histori Top Up</h3>
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
    		<div class="col-sm-2">	
				<h3><label class="control-label" for="input-status">Sisa Saldo : </label><h3>	
			</div>
			<div class="col-sm-5">				
				<h3><label class='label label-info'>Rp. {{number_format($saldo)}}</label></h3>				
			</div>
		</div> 
    	<div class="form-group">	
    		<div class="col-sm-2">	
				<label class="control-label" for="input-status">Bulan</label>			
			</div>
			<div class="col-sm-5">	
				<input  id="bulan"  name="bulan" type="text" class="form-control datepicker" >				
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
		<i class="fa fa-laptop"></i>Histori Top Up</h3>
	</div>		
<div class="box-body">
<table id="example2" class="table table-bordered table-striped">
<thead>
	<tr>
		<th class="center">Nomor</th>
		<th class="center">Date Time</th>
		<th class="center">Mitra</th>
		<th class="center">Saldo Top Up</th> 	
		<th class="center">Keterangan</th>
	</tr>
</thead>
<tbody>
	<tr>
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
	$(document).ready(function(){
	    $(".datepicker").datepicker( {
		    format: "yyyy-mm",
		    startView: "months", 
		    minViewMode: "months",  
	    	autoclose: true
		});
	 });

	function tampil(bulan){
		var nomor=0;
		$('#example2').dataTable( {
	    "ajax": "{{ url('/deposit/mutasi/') }}/"+bulan,
	    "destroy": true,
	    "columns": [
	        { "data": "nama" },
	        { "data": "topup_date" },
	        { "data": "nama" },
	        { "data": "topup_money",render: $.fn.dataTable.render.number(',', '.', 0, '')},
	        { "data": "note" }
	    	],
		    "aoColumnDefs": [ 
		     {
		      "aTargets": [ 0 ],
		      "className": "text-center",
		      "mRender": function (data, type, full) {
		          return nomor=nomor+1;
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
      var bulan = $('#bulan').val();
      tampil(bulan); 
	}); 

</script>
@endsection