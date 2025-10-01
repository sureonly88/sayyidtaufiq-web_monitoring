<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="description" content="Xenon Boostrap Admin Panel" />
	<meta name="author" content="" />
	
	<title>@yield('html-title')</title>

	{{--<link rel="stylesheet" href="stylesheet" href="http://fonts.googleapis.com/css?family=Arimo:400,700,400italic">--}}
	<link rel="stylesheet" href="{{secure_asset('xenon/css/fonts/linecons/css/linecons.css')}}" />
	<link rel="stylesheet" href="{{secure_asset('xenon/css/fonts/fontawesome/css/font-awesome.min.css')}}" />
	<link rel="stylesheet" href="{{secure_asset('xenon/css/bootstrap.css')}}" />
	<link rel="stylesheet" href="{{secure_asset('xenon/css/xenon-core.css')}}" />
	<link rel="stylesheet" href="{{secure_asset('xenon/css/xenon-forms.css')}}" />
	<link rel="stylesheet" href="{{secure_asset('xenon/css/xenon-components.css')}}" />
	<link rel="stylesheet" href="{{secure_asset('xenon/css/xenon-skins.css')}}" />
	<link rel="stylesheet" href="{{secure_asset('xenon/css/custom.css')}}">

  <script src="{{secure_asset('xenon/js/jquery-1.11.1.min.js')}}"></script>



	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	
	<link rel="stylesheet" href="{{secure_asset('css/app.css')}}" />

	@yield('header')
</head>
<body class="page-body">
	@yield('modal')
	<div class="page-container"><!-- add class "sidebar-collapsed" to close sidebar by default, "chat-visible" to make chat appear always -->

		<!-- Add "fixed" class to make the sidebar fixed always to the browser viewport. -->
		<!-- Adding class "toggle-others" will keep only one menu item open at a time. -->
		<!-- Adding class "collapsed" collapse sidebar root elements and show only icons. -->
		<div class="sidebar-menu toggle-others collapsed">
			<div class="sidebar-menu-inner">
				<header class="logo-env">
					<!-- logo -->
					<div class="logo">
						<a href="{{ url('/')}}" class="logo-expanded">
							<img src="{{ asset('image/logo.png') }}" width="50" alt="PDAM" />
							<strong> KOPKAR PEDAMI</strong>
						</a>
						
						<a href="{{ url('/')}}" class="logo-collapsed">
							<img src="{{ asset('image/logo.png') }}" width="40" alt="PDAM" />
						</a>
					</div>
					
					<!-- This will toggle the mobile menu and will be visible only on mobile devices -->
					<div class="mobile-menu-toggle visible-xs">
						<a href="#" data-toggle="mobile-menu">
							<i class="fa-bars"></i>
						</a>
					</div>
				</header>

				<ul id="main-menu" class="main-menu">
					<!-- add class "multiple-expanded" to allow multiple submenus to open -->
					<!-- class "auto-inherit-active-class" will automatically add "active" class for parent elements who are marked already with class "active" -->
					<!-- <li>
						<a href="/">
							<i class="linecons-doc"></i>
							<span class="title">Dashboard</span>
						</a>
					</li> -->
			<li>
				<a href="{{ url('/')}}">
					<i class="fa-home"></i>
					<span class="title">HOME</span>
				</a>		
			</li>		
            @foreach(session('permision') as $p)
		          @break($p->parent_id)
              <li>
					<a href="{{ url($p->url)}}">
						<i class="{!! $p->class !!}"></i>
						<span class="title">{!! $p->name !!}</span>
					</a>
	              @foreach(session('permision') as $r)
	                @if($loop->first)
					<ul>
	                @endif
	                @if($r->parent_id == $p->id)
	                	<li>
							<a href="{{ url($r->url)}}">
								<i class="{!! $r->class !!}"></i>
								<span class="title">{!! $r->name !!}</span>
							</a>
						</li>
					@endif
					@if($loop->last)
					</ul>
	                @endif
	               @endforeach
	            </li>
		        @endforeach
				<li>
					<a href="{{ url('/logout')}}">
						<i class="linecons-paper-plane"></i>
						<span class="title">LOGOUT</span>
					</a>		
				</li>
					
					
				</ul>
						
			</div>
			
		</div>
		
		<div class="main-content">
					
			<!-- User Info, Notifications and Menu Bar -->
			<nav class="navbar user-info-navbar mobile-is-visible" role="navigation">
				
				<!-- Left links for user info navbar -->
				<ul class="user-info-menu left-links list-inline list-unstyled">
					<li class="hidden-sm hidden-xs">
						<a href="#" data-toggle="sidebar">
							<i class="fa-bars"></i>
						</a>
					</li>
				</ul>
				<!-- Right links for user info navbar -->
				<ul class="user-info-menu right-links list-inline list-unstyled">
					<li class="dropdown user-profile">
						<a href="#" data-toggle="dropdown">		
						    <!-- <img src="" alt="user-image" class="img-circle img-inline userpic-16d" width="28" />-->
							<span>
								@if (session('auth')->level!=2)
									{{ session('auth')->name }}
								@else
									{{ session('auth')->name }}
								@endif		
							</span>
						</a>
					</li>
				</ul>	
			</nav>
			<div class="clearfix"></div>
			<div class="page-title">
				<div class="title-env">
					<h1 class="title">KOPKAR PEDAMI</h1>
					<p class="description">Monitoring Transaksi</p>
				</div>
					<div class="breadcrumb-env">
						<ol class="breadcrumb bc-1" >
							<li class="mod-title">
								<div class="hidden-xs">@yield('page-title')</div>
								<div class="visible-xs-block">@yield('xs-title')</div>
							</li>	
						</ol>
						
				</div>
				
			</div>

			

			@yield('body')

		</div>

	</div>

	<!-- Bottom Scripts -->
	<script src="{{secure_asset('xenon/js/bootstrap.min.js')}}"></script>
	<script src="{{secure_asset('xenon/js/TweenMax.min.js')}}"></script>
	<script src="{{secure_asset('xenon/js/resizeable.js')}}"></script>
	<script src="{{secure_asset('xenon/js/joinable.js')}}"></script>
	<script src="{{secure_asset('xenon/js/xenon-api.js')}}"></script>
	<script src="{{secure_asset('xenon/js/xenon-toggles.js')}}"></script>
	<script src="{{secure_asset('xenon/js/moment.min.js')}}"></script>
	
	@yield('plugins')

	<!-- JavaScripts initializations and stuff -->
	<script src="{{secure_asset('/xenon/js/xenon-custom.js')}}"></script>

    <script>
        (function() {

            if (is('sdevicescreen'))
                $('.sidebar-menu.collapsed').removeClass('collapsed');
        })()
    </script>

	@yield('footer')
	
</body>
</html>
