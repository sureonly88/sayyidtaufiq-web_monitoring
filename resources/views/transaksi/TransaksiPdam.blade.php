@extends('menu')
@section('header')
    <link href="{{secure_url('adminlte/plugins/datatables/dataTables.bootstrap.css')}}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{secure_url('adminlte/plugins/select2/select2.min.css')}}">
    <link rel="stylesheet" href="{{secure_url('adminlte/plugins/daterangepicker/daterangepicker-bs3.css')}}">
@endsection

@section('body')
<form class="form-horizontal" role="form" method="post" enctype="multipart/form-data"> 
<div class="box box-solid box-primary">
  <div class="box-header">
    <h3 class="btn btn disabled box-title">
    <i class="fa fa-search"></i> Filter </h3>
  	<a class="btn btn-default btn-sm pull-right" data-widget='collapse' data-toggle="tooltip" title="Collapse" style="margin-right: 5px;">
  	<i class="fa fa-minus"></i></a>
  </div>	
	<div class="box-body">
    	<div class="form-group">
      	@if (session('auth')->level!=2&&session('auth')->level!=5&&session('auth')->level!=6)
			<div class="col-sm-6">	
				<label class="control-label" for="input-tipe">Jenis</label>
				<select name="tipe" id="tipe" class="form-control select2" multiple="multiple" data-placeholder="Pilih Jenis Loket">
					<option value="1">Loket Pedami</option>
					<option value="2">Loket Luar</option>
					<option value="3">Loket Petugas Lapangan Pedami</option>
					<option value="4">Switching</option>
					<option value="5">Loket Luar (H+1)</option>
					<option value="6">Switching Deposit</option>
				</select>				
			</div>
			<div class="col-sm-6">	
				<label class="control-label" for="input-status">Loket</label>
				<input  name="loket" id="loket" type="text" class="form-control">				
			</div>
		</div>
		<div class="form-group">	
			<div class="col-sm-6">	
				<label class="control-label" for="input-tipe">Jenis Transaksi</label>
				<select name="jenis" id="jenis" class="form-control select2" multiple="multiple" data-placeholder="Pilih Jenis Transaksi">
					<option value="PDAM_BANDARMASIH">PDAM</option>
					<option value="PLN_POSTPAID">PLN POSTPAID</option>
					<option value="PLN_PREPAID">PLN PREPAID</option>
					<option value="PLN_NONTAGLIS">PLN NONTAGLIS</option>
				</select>				
			</div>
		@endif	
			<div class="col-sm-6">	
				<label class="control-label" for="input-status">Tanggal</label>
				<input  id="tgl"  name="tgl" type="text" class="form-control daterange" >				
			</div>
    	</div>
    	<div class="form-group">
    		<div class="col-sm-5">
		        <button type="button" name="filter" id="filter"  class="btn btn-primary"><i class="glyphicon glyphicon-search"></i> Filter</button>		 
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
		<th class="text-center" style="min-width:30px">No</th>
		<th class="text-center" style="min-width:100px">Status</th>
		<th class="text-center" style="min-width:100px">Tanggal</th>
		<th class="text-center" style="min-width:100px">Nama Loket</th>
		<th class="text-center" style="min-width:50px">User</th>
		<th class="text-center" style="min-width:100px">Jenis Transaksi</th>
		<th class="text-center" style="min-width:100px">Tagihan</th>
		<th class="text-center" style="min-width:100px">Admin</th>
		<th class="text-center" style="min-width:100px">Total</th>
		<th class="text-center" style="min-width:50px">Jumlah</th>
		<th class="text-center" style="min-width:100px">Fee Koperasi</th>
		<th class="text-center" style="min-width:100px">Wajib Setor</th>
		<th class="text-center" style="min-width:100px">Action</th> 	
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
		<td></td>
	</tr>
</tbody>
<tfoot>
	<tr>
		<th class="text-right" colspan="6">Total</th>
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
@if (session('auth')->level!=2&&session('auth')->level!=5&&session('auth')->level!=6)
<button class="btn btn-primary" type="button" onclick="cetakPdf()">
	<span>Cetak</span>
</button>
<button class="btn btn-success" type="button" onclick="cetakRekapExcellDetail()">
	<span>Excell</span>
</button>
<button class="btn btn-success" type="button" onclick="cetakRekapExcell()">
	<span>Rekap</span>
</button>
@endif
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
		<th class="text-center">No</th>
		<th class="text-center">Tanggal</th>
		<th class="text-center">ID Pelanggan</th>
		<th class="text-center">Nama</th>
		<th class="text-center">Periode</th>
		<th class="text-center">Tagihan</th>
		<th class="text-center">Admin</th>
		<th class="text-center">Total</th>
		<th class="text-center">Nama Loket</th>
		<th class="text-center">User</th>
		<th class="text-center">Jenis Transaksi</th> 	
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
<script src="{{secure_url('adminlte/plugins/slimScroll/jquery.slimscroll.min.js')}}" type="text/javascript"></script>
<script src="{{secure_url('adminlte/plugins/daterangepicker/daterangepicker.js')}}"></script>

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
	    $('.daterange').daterangepicker();
	 });	
	

	var data = <?= json_encode($lokets) ?>;
	var loket = $('#loket').select2({
		data: data,
		placeholder: 'Pilih Loket',
		allowClear: true,
		minimumInputLength: 0,
		multiple:true,
		formatSelection: function(data) { return data.text },
		formatResult: function(data) {
			return  '<span class="label label-info">'+data.id+'</span>'+
						'<strong style="margin-left:5px">'+data.text+'</strong>';

		}
	});

	/**$('#tipe').change(function(){
		//loket.select2("destroy");
		$.getJSON("{{ url('ajaxLoketsTipe')}}/"+$(this).val(), function(data) {
			loket.select2({
				data: data,
				placeholder: 'Pilih Loket',
				allowClear: true,
				minimumInputLength: 0,
				multiple:true,
				formatSelection: function(data) { return data.text },
				formatResult: function(data) {
					return  '<span class="label label-info">'+data.id+'</span>'+
								'<strong style="margin-left:5px">'+data.text+'</strong>';
				}
			});
		});
	});**/

	//format desimal
	function formatDesimal(num){
	    var n = num.toString(), p = n.indexOf('.');
	    return n.replace(/\d(?=(?:\d{3})+(?:\.|$))/g, function($0, i){
	        return p<0 || i<p ? ($0+',') : $0;
	    });
	}
	//filter
    $("#filter").on('click',function(){
    if ($("#tgl").val()==''){
		alert("Pilih Tanggal");
	}else{	
      var tipe = $('#tipe').val();
       if (!tipe){tipe="-";}
      var jenis = $('#jenis').val();
      	if (!jenis){jenis="-";}
      var loket = $('#loket').val();
      	if (!loket){loket="-";}
      var tgl = $('#tgl').val();
      var a =tgl.split(" - ");
		var b =a[0].split("/");
		var c =a[1].split("/");

		var d=[b[2],b[0],b[1]];
		var dari=d.join("-");

		var e=[c[2],c[0],c[1]];
		var sampai=e.join("-");

		var level="<?php echo session('auth')->level;?>"
		var nomor=0;
		$('#example2').dataTable( {
	    "ajax": "{{ url('/transaksi/pdam') }}/"+tipe+"/"+jenis+"/"+loket+"/"+dari+"/"+sampai,
	    "destroy": true,
	    "columns": [
	        { "data": "nomor" },
	        { "data": "status" },
	        { "data": "tanggal" },
	        { "data": "loket_name" },
	        { "data": "user_" },
	        { "data": "jenis_transaksi" },
	        { "data": "tagihan",render: $.fn.dataTable.render.number(',', '.', 0, '') },
	        { "data": "admin",render: $.fn.dataTable.render.number(',', '.', 0, '') },
	        { "data": "total",render: $.fn.dataTable.render.number(',', '.', 0, '') },
	        { "data": "jumlah",render: $.fn.dataTable.render.number(',', '.', 0, '') },
	        { "data": "aksi" },
	        { "data": "aksi" },
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
		            var formmatedvalue = "<span class='btn btn-xs btn-warning'>Belum Diperiksa</span>";
		          }else if (full.status=="1"){
		            var formmatedvalue = "<span class='btn btn-xs btn-danger'>Belum Fix</span>";
		          }else{
		            var formmatedvalue = "<span class='btn btn-xs btn-success'>Fix</span>";
		          }
		          return formmatedvalue;
			    }
		      },
		      {
		      "aTargets": [ 10 ],
		      "mRender": function (data, type, full, level) {
		          if (level!="2"||full.jenis_loket=="SWITCHING"){
		            var fee = full.pdam*full.jumlah;
		          }else if (level!="2"){
		            var fee = 0;
		          }
		          return formatDesimal(fee);
			    }
		      },
		      {
		      "aTargets": [ 11 ],
		      "mRender": function (data, type, full, level) {
		          if (full.jenis_loket=="SWITCHING"){
		            var setor = parseFloat(full.pdam*full.jumlah)+parseFloat(full.tagihan);
		          }else{
		            var setor = 0;
		          }
		          return formatDesimal(setor);
			    }
		      },
		      {
		      "aTargets": [ 12 ],
		      "mRender": function (data, type, full, level) {
		          var formmatedvalue="<button value='"+full.loket_code+"/ "+full.tanggal+"/"+full.user_+"'";
		          formmatedvalue=formmatedvalue+" type='button' class='btn btn-primary btn-xs bdetail'>Detail</button>";
			      formmatedvalue=formmatedvalue+" <a href='{{ url('transaksi/pdam/cetakDetail')}}/"+full.loket_code+"/"+full.tanggal+"/"+full.user_+"'" ;
			      formmatedvalue=formmatedvalue+" class='btn btn-success btn-xs'>Excell</a>";
		          return formmatedvalue;
			    }
		      },
		    ],
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
	            tagihan = api.column( 6 ).data().reduce( function (a, b) {return intVal(a) + intVal(b);}, 0 );
	            admin = api.column( 7 ).data().reduce( function (a, b) {return intVal(a) + intVal(b);}, 0 );
	            total = api.column( 8 ).data().reduce( function (a, b) {return intVal(a) + intVal(b);}, 0 );
	            jumlah = api.column( 9 ).data().reduce( function (a, b) {return intVal(a) + intVal(b);}, 0 );
	            fee = api.column( 10 ).cache('search').reduce( function (a, b) {return intVal(a) + intVal(b);}, 0 );
	            setor = api.column( 11 ).cache('search').reduce( function (a, b) {return intVal(a) + intVal(b);}, 0 );

	            // Update footer

	            $( api.column( 6 ).footer() ).html('Rp '+formatDesimal(tagihan));
	            $( api.column( 7 ).footer() ).html('Rp '+formatDesimal(admin));
	            $( api.column( 8 ).footer() ).html('Rp '+formatDesimal(total));
	            $( api.column( 9 ).footer() ).html(formatDesimal(jumlah));
	            $( api.column( 10 ).footer() ).html('Rp '+formatDesimal(fee));
	            $( api.column( 11 ).footer() ).html('Rp '+formatDesimal(setor));

	            if (level=="2"){ api.column([10]).visible(false);}
        	},
	        "scrollY": 1000,
		   	"scrollX": true,
		   	"scrollCollapse": true,
		   	"autoWidth": true,
		   	"ordering" : false,
		   	"info" : false,
	   		"lengthChange" :true,		
		});	
	}  
	}); 

	function cetakPdf(){
		//var tipe = $("#tipe").val();
		//var jenis = $("#jenis").val();
		//var loket = $("#loket").val();

	    var tipe = $('#tipe').val();
	      if (!tipe){tipe="-";}
	    var jenis = $('#jenis').val();
	      if (!jenis){jenis="-";}
	    var loket = $('#loket').val();
	      if (!loket){loket="-";}


		var tanggal = $("#tgl").val();
		var a =tanggal.split(" - ");
		var b =a[0].split("/");
		var c =a[1].split("/");

		var d=[b[2],b[0],b[1]];
		var dari=d.join("-");

		var e=[c[2],c[0],c[1]];
		var sampai=e.join("-");

		if(!loket){loket = "-";}
	   	window.open("{{ url('transaksi/pdam/cetakPdf') }}/"+tipe+"/"+jenis+"/"+loket+"/"+dari+"/"+sampai,'_blank');
	}

	function cetakRekapExcell(){

	    var tipe = $('#tipe').val();
	      if (!tipe){tipe="-";}
	    var jenis = $('#jenis').val();
	      if (!jenis){jenis="-";}
	    var loket = $('#loket').val();
	      if (!loket){loket="-";}

		var tanggal = $("#tgl").val();
		var a =tanggal.split(" - ");
		var b =a[0].split("/");
		var c =a[1].split("/");

		var d=[b[2],b[0],b[1]];
		var dari=d.join("-");

		var e=[c[2],c[0],c[1]];
		var sampai=e.join("-");

		if(!loket){loket = "-";}
	   	window.open("{{ url('transaksi/pdam/cetakRekapExcell') }}/"+tipe+"/"+jenis+"/"+loket+"/"+dari+"/"+sampai,'_blank');
	}

	function cetakRekapExcellDetail(){

	    var tipe = $('#tipe').val();
	      if (!tipe){tipe="-";}
	    var jenis = $('#jenis').val();
	      if (!jenis){jenis="-";}
	    var loket = $('#loket').val();
	      if (!loket){loket="-";}

		var tanggal = $("#tgl").val();
		var a =tanggal.split(" - ");
		var b =a[0].split("/");
		var c =a[1].split("/");

		var d=[b[2],b[0],b[1]];
		var dari=d.join("-");

		var e=[c[2],c[0],c[1]];
		var sampai=e.join("-");

		if(!loket){loket = "-";}
	   	window.open("{{ url('transaksi/pdam/cetakRekapExcellDetail') }}/"+tipe+"/"+jenis+"/"+loket+"/"+dari+"/"+sampai,'_blank');
	}

	

	$("#example2").on('click','.bdetail',function(){
		var nomor=0;
		$('#example3').dataTable( {
	    "ajax": "{{ url('detailTransaksi')}}/"+$(this).val(),
	    "destroy": true,
	    "columns": [
	        { "data": "tanggal" },
	        { "data": "tanggal" },
	        { "data": "idpel" },
	        { "data": "nama" },
	        { "data": "periode" },
	        { "data": "tagihan",render: $.fn.dataTable.render.number(',', '.', 0, '') },
	        { "data": "admin",render: $.fn.dataTable.render.number(',', '.', 0, '') },
	        { "data": "total",render: $.fn.dataTable.render.number(',', '.', 0, '') },
	        { "data": "loket_name" },
	        { "data": "user_" },
	        { "data": "jenis_transaksi" }
	    	],"aoColumnDefs": [ 
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
   		"lengthChange" :true,		
		});	 
	});
</script>
@endsection