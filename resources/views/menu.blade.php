<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>PEDAMI-MONITORING</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="{{secure_asset('adminlte/dist/css/skins/_all-skins.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{secure_asset('adminlte/plugins/iCheck/all.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{secure_asset('adminlte/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="{{secure_asset('adminlte/dist/css/AdminLTE.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{secure_asset('adminlte/dist/css/skins/_all-skins.min.css')}}" rel="stylesheet" type="text/css" />	
    @yield('header')
  </head>
  <body class="skin-blue sidebar-mini fixed">
   @yield('modal') 
    <!-- Site wrapper -->
    <div class="wrapper">
      
      <header class="main-header">
        <!-- Logo -->
        <a href="" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b>KP</b></span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b>Kopkar&nbsp;</b> PEDAMI</span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
        <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
              <!-- Messages: style can be found in dropdown.less-->			  
  				<li class="dropdown user user-menu">
  				<a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <span class="hidden-xs">{{ session('auth')->name }}</span>
          </a>
          </li>
        </ul>
        </div>
        </nav>
      </header>

      <!-- =============================================== -->

      <!-- Left side column. contains the sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
         <li style="margin:2%;">
			</li>
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">
            <li class="header">MAIN NAVIGATION</li>
      			<li><a href="{{ url('/') }}"><i class="fa fa-home"></i> <span>Beranda</span></a></li>
            @foreach(session('permision') as $p)
              @break($p->parent_id)
              <li class="treeview">
                <a href="#"> <i class="fa {!! $p->class !!}"></i> 
                <span>{!! $p->name !!}</span> <i class="fa fa-angle-left pull-right"></i></a>
                @foreach(session('permision') as $r)
                  @if($loop->first)
                    <ul class="treeview-menu">
                  @endif
                  @if($r->parent_id == $p->id)
                    <li><a href="{{ url($r->url) }}">
                      <i class="fa {!! $r->class !!}"></i><span>{!! $r->name !!}</span></a></li>
                  @endif
                  @if($loop->last)
                    </ul>
                  @endif
                 @endforeach
              </li>
            @endforeach
			      <li><a href="{{ url('/logout') }}"><i class="fa fa-paper-plane"></i> <span>Logout</span></a></li>
          </ul>

        </section>
        <!-- /.sidebar -->
      </aside>

      <!-- =============================================== -->

      <!-- Content Wrapper. Contains page content -->
      <div style="background:url({{secure_asset('image/0.png')}})repeat;" class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section  class="content-header">
          <h1> Monitoring Transaksi
            <small>KOPKAR PEDAMI</small>
          </h1>
        </section>

        <!-- Main content -->
        <section  class="content">
 <!-- diini lah kita kasih artikel nya --> 
<div class="box box-primary">
</div>
	<?php //include "isi.php";?>        		

 @yield('body') 
        </section><!-- /.content -->       
      </div><!-- /.content-wrapper -->

      <footer class="main-footer">
        <div class="pull-right hidden-xs">
          Monitoring Transaksi KOPKAR PEDAMI <b>Version</b> 1.0
        </div>
        <strong>Copyright &copy; 2017 <a href="#"></a>.</strong> All rights reserved.
      </footer>
      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class='control-sidebar-bg'></div>
    </div><!-- ./wrapper -->

    <script src="{{secure_asset('adminlte/plugins/jQuery/jQuery-2.1.4.min.js')}}"></script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="{{secure_asset('adminlte/bootstrap/js/bootstrap.min.js')}}" type="text/javascript"></script>
    <!-- FastClick -->
    <script src="{{secure_asset('adminlte/plugins/fastclick/fastclick.min.js')}}'"></script>
    <!-- AdminLTE App -->
    <script src="{{secure_asset('adminlte/dist/js/app.min.js')}}" type="text/javascript"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="{{secure_asset('adminlte/dist/js/demo.js')}}" type="text/javascript"></script>
    <script src="{{secure_asset('adminlte/plugins/daterangepicker/moment.min.js')}}"></script>
    <!-- Page script -->
    @yield('plugins')
    <script></script>	
    @yield('footer')
  </body>
</html>