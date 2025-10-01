@extends('menu')
@section('header')
    <link href="{{URL::asset('adminlte/plugins/datatables/dataTables.bootstrap.css')}}" rel="stylesheet" type="text/css" />

@endsection

@section('body')
	@if (session('auth')->level!=5&&session('auth')->tipe==6)
	<div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-bank"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">LIMIT</span>
              <span class="info-box-number">{{number_format($limit)}}</span>

              <div class="progress">
                <div class="progress-bar" style="width: 100%"></div>
              </div>
                  <span class="progress-description">
                  </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-pie-chart"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">SISA DANA PLAFON</span>
              <span class="info-box-number">{{number_format($saldo)}}</span>

              <div class="progress">
                <div class="progress-bar" style="width: 100%"></div>
              </div>
                  <span class="progress-description">
                  </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <?php 
			$transaksi=$saldo-$limit;
			$wajibsetor=$transaksi+$hariini;
			
			if($wajibsetor==0){$wajibsetor=$transaksi;}
		?>
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-bar-chart"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">TRANSAKSI</span>
              <span class="info-box-number">{{number_format($transaksi)}}</span>

              <div class="progress">
                <div class="progress-bar" style="width: 100%"></div>
              </div>
                  <span class="progress-description">
                  </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-red">
            <span class="info-box-icon"><i class="fa fa-money"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">WAJIB SETOR</span>
              <span class="info-box-number">{{number_format($wajibsetor)}}</span>

              <div class="progress">
                <div class="progress-bar" style="width: 100%"></div>
              </div>
                  <span class="progress-description">
                  </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
      </div>
	@elseif (session('auth')->level!=5&&session('auth')->tipe==4)
  <div class="row">
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-pie-chart"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">DEPOSIT</span>
              <span class="info-box-number">{{number_format($saldo)}}</span>

              <div class="progress">
                <div class="progress-bar" style="width: 100%"></div>
              </div>
                  <span class="progress-description">
                  </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-bar-chart"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">TRANSAKSI HARI INI</span>
              <span class="info-box-number">{{number_format($hariini)}}</span>

              <div class="progress">
                <div class="progress-bar" style="width: 100%"></div>
              </div>
                  <span class="progress-description">
                  </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
       
  @endif	

@endsection
@section('plugins')
<script src="{{URL::asset('adminlte/plugins/datatables/jquery.dataTables.min.js')}}" type="text/javascript"></script>


@endsection

@section('footer')
<script>	
	
</script>
@endsection