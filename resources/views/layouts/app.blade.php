<?php

$pref = (App\Preference::all()->count() > 0) ? App\Preference::all()->first() : null;

if (!empty($pref->company_logo_url)) {
    $company_logo_url = $pref->company_logo_url;
} else {
    $company_logo_url = false;
}

if (!empty($pref->application_name)) {
    $application_name = $pref->application_name;
} else {
    $application_name = 'User Account Verification';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{$application_name}}</title>

    <!-- Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel='stylesheet'
          type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700" rel='stylesheet' type='text/css'>

    <!-- Styles -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
          integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link href="{{ elixir('css/app.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: 'Lato';
        }

        .fa-btn {
            margin-right: 6px;
        }
    </style>
    @yield('head')
</head>
<body id="app-layout">
<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">

            <!-- Collapsed Hamburger -->
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#app-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- Branding Image -->
            <a class="navbar-brand" href="{{ url('/') }}">

                <i class="fa fa-btn fa-unlock-alt"></i>{{$application_name}}

            </a>
        </div>

        <div class="collapse navbar-collapse" id="app-navbar-collapse">
            <!-- Left Side Of Navbar -->
            <ul class="nav navbar-nav">
                @if (!Auth::guest())
                    <li><a href="{{ url('/') }}"><i class="fa fa-btn fa-check-circle"></i>Verify</a></li>
                    @if (Auth::user()->isAdmin)
                        {{--  <li><a href="{{ url('/dashboard') }}"><i class="fa fa-btn fa-dashboard"></i>Dashboard</a></li> --}}
                        <li><a href="{{ url('/timeline') }}"><i class="fa fa-btn fa-expand"></i>Recent Activity</a></li>
                        <li><a href="{{ url('/users') }}"><i class="fa fa-btn fa-group"></i>Local Users</a></li>
                        <li><a href="{{ url('/preferences') }}"><i class="fa fa-btn fa-cogs"></i>App Settings</a>
                        </li>
                    @endif
                @endif
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="nav navbar-nav navbar-right">
                <!-- Authentication Links -->
                @if (Auth::guest())
                    <li><a href="{{ url('/login') }}"><i class="fa fa-btn fa-sign-in"></i>Login</a></li>
                @else
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            <span><img src="{{ Gravatar::src(Auth::user()->email, 64)}}" width="26"> </span>
                            &nbsp; {{ Auth::user()->name }} <span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu" role="menu">
                            <li><a href="{{ url('/profile') }}"><i class="fa fa-btn fa-edit"></i>Profile</a></li>
                            <li><a href="{{ url('/logout') }}"><i class="fa fa-btn fa-sign-out"></i>Logout</a></li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

<div class="flash-message">
    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
        @if(Session::has('alert-' . $msg))
            <p class="alert alert-{{ $msg }} center-div">{{ Session::get('alert-' . $msg) }} <a href="#" class="close"
                                                                                                data-dismiss="alert"
                                                                                                aria-label="close">&times;</a>
            </p>
        @endif
    @endforeach
</div>

@yield('content')

        <!-- JavaScripts -->
<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>-->
<script type="text/javascript" src="/js/app.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
        integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
        crossorigin="anonymous"></script>

@yield('js_foot')

</body>
<footer>
    @yield('foot')
</footer>
</html>
