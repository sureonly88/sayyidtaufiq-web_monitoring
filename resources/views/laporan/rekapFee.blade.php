@extends('menu')
@section('header')
    <link href="{{URL::asset('adminlte/plugins/datepicker/datepicker3.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('body')
<h3 class="box-title">Rekap Fee</h3>
<form class="form-horizontal" role="form" method="post" enctype="multipart/form-data"> 
<div class="box box-solid box-primary">
  <div class="box-header">
    <h3 class="btn btn disabled box-title">
    <i class="fa fa-search"></i> Rekap Fee </h3>
  </div>	
	<div class="box-body">
    	<div class="form-group">	
			<div class="col-sm-3">	
				<label class="control-label" for="input-status">Bulan</label>
				<input  id="tgl"  name="tgl" type="text" class="form-control datepicker" >				
			</div>
    	</div>
    	<div class="form-group">
    		<div class="col-sm-12">
		        <button class="btn btn-success" type="button" onclick="cetakRekapKasirBulanan()">
		        	<i class="glyphicon glyphicon-tags"></i>
					<span>&nbsp;&nbsp;Rekap Fee Kasir</span>
				</button>	
				<button class="btn btn-success" type="button" onclick="cetakRekapFeeBulanan()">
		        	<i class="glyphicon glyphicon-tasks"></i>
					<span>&nbsp;&nbsp;Rekap Fee All</span>
				</button> 
				<button class="btn btn-success" type="button" onclick="cetakLaporanBulanan()" data-toggle='tooltip' data-placement='top' data-original-title='Belum Tuntung'>
		        	<i class="glyphicon glyphicon-stats"></i>
					<span>&nbsp;&nbsp;Laporan Pendapatan</span>
				</button>
		    </div>
		</div>    
  </div>
</div>
</form>

@endsection
@section('plugins')
<script src="{{URL::asset('adminlte/plugins/datepicker/bootstrap-datepicker.js')}}" type="text/javascript"></script>

@endsection

@section('footer')
	<script>	
	$(document).ready(function(){
	    $(".datepicker").datepicker( {
		    format: "yyyy-mm",
		    startView: "months", 
		    minViewMode: "months",  
	    	autoclose: true
		});
	 });	
	
	function cetakRekapKasirBulanan()
	{
		if ($("#tgl").val()=='')
		{
			alert("Pilih Bulan");
		}else{
			var bulan = $("#tgl").val();
		   	window.open("{{ url('laporan/bulanan/rekapKasir') }}/"+bulan,'_blank');
		}
	}

	function cetakRekapFeeBulanan()
	{
		if ($("#tgl").val()=='')
		{
			alert("Pilih Bulan");
		}else{
			var bulan = $("#tgl").val();
		   	window.open("{{ url('laporan/bulanan/rekapFee') }}/"+bulan,'_blank');
		}
	}


	function cetakLaporanBulanan()
	{
		if ($("#tgl").val()=='')
		{
			alert("Pilih Bulan");
		}else{
			var bulan = $("#tgl").val();
		   	window.open("{{ url('laporan/bulanan/laporanPendapatan') }}/"+bulan,'_blank');
		}
	}

</script>
@endsection