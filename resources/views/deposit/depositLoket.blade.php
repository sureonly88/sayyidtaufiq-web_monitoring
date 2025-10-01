@extends('menu')
@section('header')
    <link href="{{URL::asset('adminlte/plugins/datatables/dataTables.bootstrap.css')}}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{URL::asset('adminlte/plugins/select2/select2.min.css')}}">
@endsection

@section('body')
<h3 class="box-title">Deposit</h3>
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
				<label class="control-label" for="input-status">Jenis Loket</label>			
			</div>
			<div class="col-sm-5">	
				<select style="width: 100%;"  name="tipe" id="tipe" class="form-control select2">
					<option value="2">Loket Luar</option>
					<option value="6">Switching</option>
				</select>				
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
		<i class="fa fa-laptop"></i>Deposit</h3>
	</div>		
<div class="box-body">
<table id="example2" class="table table-bordered table-striped">
<thead>
	<tr>
		<th class="center">Nomor</th>
		<th>Nama</th>
		<th>Sisa Saldo</th> 	
	</tr>
</thead>
<tbody>
	<tr>
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
<script src="{{URL::asset('adminlte/plugins/datatables/jquery.dataTables.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('adminlte/plugins/datatables/dataTables.bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('adminlte/plugins/select2/select2.full.min.js')}}"></script>	

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


	$('.select2').select2();

	function tampil(tipe){
		var nomor=0;
		$('#example2').dataTable( {
	    "ajax": "{{ url('/deposit/loket/') }}/"+tipe,
	    "destroy": true,
	    "columns": [
	        { "data": "nama" },
	        { "data": "nama" },
	        { "data": "pulsa",render: $.fn.dataTable.render.number(',', '.', 0, '')}
	    	],
		    "aoColumnDefs": [ 
		     {
		      "aTargets": [ 0 ],
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
      var tipe = $('#tipe').val();
      tampil(tipe); 
	}); 

</script>
@endsection