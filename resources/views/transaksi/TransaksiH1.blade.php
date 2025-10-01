@extends('menu')
@section('header')
    <link href="{{URL::asset('adminlte/plugins/datatables/dataTables.bootstrap.css')}}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{URL::asset('adminlte/plugins/select2/select2.min.css')}}">
@endsection

@section('body')
<h3 class="box-title">Transaksi H+1</h3>
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
					<option value="5">H+1</option>
					<option value="3">H+1 Petugas Lapangan Pedami</option>
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
		<i class="fa fa-laptop"></i>Transaksi</h3>
	</div>		
<div class="box-body">
<table id="example2" class="table table-bordered table-striped">
<thead>
	<tr>
		<th class="text-center" style="min-width:30px">No</th>
		<th class="text-center" style="min-width:60px">Kode Loket</th>
		<th class="text-center" style="min-width:160px">Nama Loket</th>
		<th class="text-center" style="min-width:80px">Open Balance</th>
		<th class="text-center" style="min-width:80px">Today Deposit</th>
		<th class="text-center" style="min-width:80px">Settlement</th>
		<th class="text-center" style="min-width:80px">Today Trx</th>
		<th class="text-center" style="min-width:80px">End. Balance</th>
		<th class="text-center" style="min-width:80px">Bailout</th>
		<th class="text-center" style="min-width:80px">Limit</th> 	
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
	</tr>
</tbody>
<tfoot>
	<tr>
		<th class="text-right" colspan="3">Total</th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
	</tr>
</tfoot>
</table>
</div>
</div>

@endsection
@section('plugins')
<script src="{{URL::asset('adminlte/plugins/datatables/jquery.dataTables.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('adminlte/plugins/datatables/dataTables.bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('adminlte/plugins/select2/select2.full.min.js')}}"></script>	
<script src="{{URL::asset('js/dataTables.fixedColumns.min.js')}}"></script> 

@endsection

@section('footer')
	<script>	
	$("#example2").dataTable({
	   	"scrollY": 1000,
	   	"scrollX": true,
	   	"scrollCollapse": true,
	   	"autoWidth": true,
	   	"ordering" : false,
	   	"info" : false,
	   	"paging" : false
	   });


	$('.select2').select2();
	//format desimal
	function formatDesimal(num){
	    var n = num.toString(), p = n.indexOf('.');
	    return n.replace(/\d(?=(?:\d{3})+(?:\.|$))/g, function($0, i){
	        return p<0 || i<p ? ($0+',') : $0;
	    });
	}

	function tampil(tipe){
		var nomor=0;
		$('#example2').dataTable( {
	    "ajax": "{{ url('/transaksi/h1/ajaxH1') }}/"+tipe,
	    "destroy": true,
	    "columns": [
	        { "data": "aksi" },
	        { "data": "loket_code" },
	        { "data": "loket_name" },
	        { "data": "aksi" },
	        { "data": "aksi" },
	        { "data": "aksi" },
	        { "data": "aksi" },
	        { "data": "aksi" },
	        { "data": "limit" },
	        { "data": "pulsa" }
	    	],
		"aoColumnDefs": [ 
		     {
		      "aTargets": [ 0 ],
		      "mRender": function (data, type, full) {
		          return nomor=nomor+1;
			    }
		     },
		     {
		      "aTargets": [ 3 ],
		      "mRender": function (data, type, full) {
		      	  var total=0; 
		      	  if (!full.total){total=0;}else{total=full.total;}
		      	  var topup_money=0
				  if (!full.topup_money){topup_money=0;} else{topup_money=full.topup_money;}
				  var limit=0
				  if (!full.limit){limit=0;} else{limit=full.limit;}

		      	  var open_balance=parseFloat(full.pulsa)-parseFloat(limit)+parseFloat(total)-parseFloat(topup_money);

		          if (open_balance<0){
		            var formmatedvalue = "<td  class='text-right'><font color='red'>"+formatDesimal(open_balance)+"</font></td>";
		          }else {
		            var formmatedvalue = "<td  class='text-right'><font color='green'>"+formatDesimal(open_balance)+"</font></td>";
		          }
		          return formmatedvalue;
			    },
            	"className": "text-right"
		     },
		     {
		      "aTargets": [ 4 ],
		      "mRender": function (data, type, full) {
		      	  var topup_money=0
				  if (!full.topup_money){topup_money=0;} else{topup_money=full.topup_money;}	
		          var formmatedvalue = "<td  class='text-right'><font color='green'>"+formatDesimal(topup_money)+"</font></td>";
		          return formmatedvalue;
			    },
            	"className": "text-right"
		     },
		     {
		      "aTargets": [ 5 ],
		      "mRender": function (data, type, full) {
		      	  var total=0; 
		      	  if (!full.total){total=0;}else{total=full.total;}
		      	  var topup_money=0
				  if (!full.topup_money){topup_money=0;} else{topup_money=full.topup_money;}
				  var limit=0
				  if (!full.limit){limit=0;} else{limit=full.limit;}

		      	  var open_balance=parseFloat(full.pulsa)-parseFloat(limit)+parseFloat(total)-parseFloat(topup_money);
		      	  var settlement=parseFloat(open_balance)+parseFloat(topup_money);

		          if (settlement<0){
		            var formmatedvalue = "<td  class='text-right'><font color='red'>"+formatDesimal(settlement)+"</font></td>";
		          }else {
		            var formmatedvalue = "<td  class='text-right'><font color='green'>"+formatDesimal(settlement)+"</font></td>";
		          }
		          return formmatedvalue;
			    },
            	"className": "text-right"
		     },
		     {
		      "aTargets": [ 6 ],
		      "mRender": function (data, type, full) {
		      	  if (!full.total){var total=0;}else{var total=Math.trunc(full.total);}	
		          var formmatedvalue = "<td  class='text-right'><font color='green'>"+formatDesimal(total)+"</font></td>";
		          return formmatedvalue;
			    },
            	"className": "text-right"
		     },
		     {
		      "aTargets": [ 7 ],
		      "mRender": function (data, type, full) {	
		      	  var limit=0
				  if (!full.limit){limit=0;} else{limit=full.limit;}
		      	  var end_balance=parseFloat(full.pulsa)-parseFloat(limit);

		          if (end_balance<0){
		            var formmatedvalue = "<td  class='text-right'><font color='red'>"+formatDesimal(end_balance)+"</font></td>";
		          }else {
		            var formmatedvalue = "<td  class='text-right'><font color='green'>"+formatDesimal(end_balance)+"</font></td>";
		          }
		          return formmatedvalue;
			    },
            	"className": "text-right"
		     },
		     {
		      "aTargets": [ 8 ],
		      "mRender": function (data, type, full) {
		      	  var limit=0
				  if (!full.limit){limit=0;} else{limit=full.limit;}
		          var formmatedvalue = "<td  class='text-right'><font color='green'>"+formatDesimal(limit)+"</font></td>";
		          return formmatedvalue;
			    },
            	"className": "text-right"
		     },
		     {
		      "aTargets": [ 9 ],
		      "mRender": function (data, type, full) {
		          var formmatedvalue = "<td  class='text-right'><font color='green'>"+formatDesimal(full.pulsa)+"</font></td>";
		          return formmatedvalue;
			    },
            	"className": "text-right"
		     },
		],
		"rowCallback": function( row, data, index ) {		    
		    var total=0; 
	      	if (!data.total){total=0;}else{total=data.total;}
	      	var topup_money=0
			if (!data.topup_money){topup_money=0;} else{topup_money=data.topup_money;}
			var limit=0
			if (!data.limit){limit=0;} else{limit=data.limit;}

	      	var open_balance=parseFloat(data.pulsa)-parseFloat(limit)+parseFloat(total)-parseFloat(topup_money);
	      	var settlement=parseFloat(open_balance)+parseFloat(topup_money);
	      	//hidden jika settlement = 0
	      	if ( settlement == "0") {
		       $(row).hide();
		    }
 		},
	    "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[^\d.-]/g, '') * 1 :
                    typeof i === 'number' ?
                        i : 0;
            };	 
            // Total over all pages
            open_balance = api.column( 3 ).cache('search').reduce( function (a, b) {return intVal(a) + intVal(b);}, 0 );
            topup_money = api.column( 4 ).cache('search').reduce( function (a, b) {return intVal(a) + intVal(b);}, 0 );
            settlement = api.column( 5 ).cache('search').reduce( function (a, b) {return intVal(a) + intVal(b);}, 0 );
            total = api.column( 6 ).cache('search').reduce( function (a, b) {return intVal(a) + intVal(b);}, 0 );
            end_balance = api.column( 7 ).cache('search').reduce( function (a, b) {return intVal(a) + intVal(b);}, 0 );
            limit = api.column( 8 ).data().reduce( function (a, b) {return intVal(a) + intVal(b);}, 0 );
            pulsa = api.column( 9 ).data().reduce( function (a, b) {return intVal(a) + intVal(b);}, 0 );

            // Update footer
            $( api.column( 3 ).footer() ).html('Rp '+formatDesimal(open_balance));
            $( api.column( 4 ).footer() ).html('Rp '+formatDesimal(topup_money));
            $( api.column( 5 ).footer() ).html('Rp '+formatDesimal(settlement));
            $( api.column( 6 ).footer() ).html('Rp '+formatDesimal(total));
            $( api.column( 7 ).footer() ).html('Rp '+formatDesimal(end_balance));
            $( api.column( 8 ).footer() ).html('Rp '+formatDesimal(limit));
            $( api.column( 9 ).footer() ).html('Rp '+formatDesimal(pulsa));
    	},
        "scrollY": 500,
	   	"scrollX": true,
	   	"scrollCollapse": true,
	   	"autoWidth": true,
	   	"ordering" : false,
	   	"info" : false,
   		"lengthChange" :false,
   		"paging" : false,
   		"fixedColumns":   {
            leftColumns: 3
         }		
		});	
	}
	//filter

    $("#filter").on('click',function(){  
      var tipe = $('#tipe').val();
      tampil(tipe); 
	}); 

</script>
@endsection