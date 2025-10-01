@extends('menu')
@section('header')
<link href="{{URL::asset('adminlte/plugins/datatables/dataTables.bootstrap.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{URL::asset('adminlte/plugins/select2/select2.min.css')}}"> 
<link rel="stylesheet" href="{{URL::asset('css/switch/bootstrap-switch.css')}}">
@endsection

@section('body')
<h3 class="box-title">Level Permission</h3>
<div class="row">
<div class="col-md-12">
    <form class="form-horizontal" role="form" method="post" id="formPermission">  
	<div class="box box-solid box-primary"> 
	  	<div class="box-header">
	    	<h3 class="btn btn disabled box-title">
	    	<i class="fa fa-user-md"></i> Level Permission</h3>
	  	</div>	
		<div class="box-body">
    	<div class="form-group"> 	
	      <label class="col-sm-1 control-label">Level</label>
	      <div class="col-sm-3">
        	<select name="level" id="level" class="form-control select2" >
				<option value="0"></option>
				<option value="1">Administrator</option>
				<option value="2">User</option>
				<option value="3">Keuangan</option>
				<option value="4">PDAM</option>
				<option value="5">Multi Loket</option>
				<option value="6">User II</option>
			</select>
      	  </div>
          <div class="col-sm-4">
             <button type="button" name="simpan" id="simpan" class="btn btn-primary">
             <i class="glyphicon glyphicon-save"></i> Submit</button>
          </div>    
    	</div>
	  	</div>       
    </div>

	<div class="box box-solid box-primary">
		<div class="box-header">
			<h3 class="btn btn disabled box-title">
			<i class="fa fa-user-md"></i>Level Permission</h3>
		</div>		
		<div class="box-body">
		<table id="example2" class="table table-bordered table-striped">
			<thead>
				<tr class="text-blue">
					<th class="col-sm-1">No</th>
					<th class="col-sm-4">Nama</th>
					<th class="col-sm-4">Parent</th>
					<th class="col-sm-3">Check</th>
				</tr>
			</thead>
			<tbody>	
				<tr>
					<td></td> 
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
		</table>
		</div>
	</div>
	</form>
</div>
</div>	
@endsection

@section('plugins')
<script src="{{URL::asset('adminlte/plugins/datatables/jquery.dataTables.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('adminlte/plugins/datatables/dataTables.bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('adminlte/plugins/select2/select2.full.min.js')}}"></script>  
<script src="{{URL::asset('js/bootstrap-switch.min.js')}}"></script>
@endsection

@section('footer')
<script>	
	var tabel= $("#example2").dataTable({
	   	"scrollY": 1000,
	   	"scrollX": true,
	   	"scrollCollapse": true,
	   	"autoWidth": true,
	   	"ordering" : false,
	   	"info" : false
	   });
	$('.select2').select2();

	function tampil(level){
		$('#example2').dataTable( {
        "ajax": "{{ url('setting/ajaxPermission') }}/"+level,
        "destroy": true,
        "columns": [
            { "data": "nomor" },
            { "data": "name" },
            { "data": "parent" },
            { "data": "cek" }
        	],
		    "aoColumnDefs": [ 
		     {
		      "aTargets": [ 3 ],
		      "mRender": function (data, type, full) {
		          if (!full.level){
		            var formmatedvalue = "<input type='checkbox' name='cek[]' value='"+full.id_menu+"' class='switch' >";
		          }else{
		            var formmatedvalue = "<input type='checkbox' name='cek[]' value='"+full.id_menu+"' class='switch' checked>";
		          }
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
			"fnDrawCallback": function () {
				$(".switch").bootstrapSwitch({
			   	"size": "small",
			   	"animate" :true,
			   	"onColor" : "success",
			   	"onText" : "YES",
			   	"offText" : "NO"
			 	});
			}		
    	});
	}
	//change level 
	$('#level').change(function(e){
		tampil($(this).val());
	});
	//simpan
    $("#simpan").on('click',function(){
    if ($("#level").val()=='0'){
		alert("Pilih Level");
	}else{	
      var level = $('#level').val();
      var data = tabel.$('input[type="checkbox"]').serialize();
	  $.ajax({
	    type: 'POST',
	    url: "{{ url('setting/simpanPermission') }}/"+level,
	    data: data,
	    success: function() {
	      tampil($("#level").val());
	    }
	  });
	}  
	}); 

</script>
@endsection