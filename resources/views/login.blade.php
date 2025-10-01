<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>PEDAMI-MONITORING</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.4 -->
    <link href="{{URL::asset('adminlte/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="{{URL::asset('adminlte/dist/css/AdminLTElogin.min.css')}}" rel="stylesheet" type="text/css" />
    <style>
        form, .errors-container {
            max-width: 400px;
            margin: 0 auto;
        }
    </style>
  </head>
  <body class="hold-transition login-page ">
    <div class="login-box gradient">
      <div class="login-box-body ">
	      <div class="login-logo">
          <img src="{{URL::asset('image/logo.png')}}" alt="PEDAMI" style="width: 30%; display:block; margin:auto" />
          <h2 class="title text-center">KOPKAR PEDAMI</h2>
        </div><!-- /.login-logo -->
        <form name="login-form"  class="login-form" method="post">
           {{ csrf_field() }}
          <div class="form-group has-feedback">
            <input type="text" name="login" class="form-control" placeholder="Username" required autofocus/>
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
          </div>
          <div class="form-group has-feedback"> 
            <input name="password" type="password" class="form-control" placeholder="Password" required/>
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
          </div>
          <div class="row">
            <div class="col-xs-8">                                    
            </div><!-- /.col -->
            <div class="col-xs-4">
			       <button type="submit" name="submit" class="btn btn-primary btn-block btn-flat"><i class="glyphicon glyphicon-log-in"></i></i>  Masuk</button>
            </div><!-- /.col -->
            @if (Session::has('alerts'))
              @foreach(Session::get('alerts') as $alert)
              <hr>
              <div class="col-xs-12">
                <div class="alert alert-{{ $alert['type'] }}">{!! $alert['text'] !!}</div>
              </div>  
              @endforeach
            @endif
          </div>
        </form>         

      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->
      
    </script>
  </body>
</html>