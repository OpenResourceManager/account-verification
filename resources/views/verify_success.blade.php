<?php

$target = session('target');

if (!isset($target) || !$target || empty($target)) {
    header("Location: /");
    exit();
}
?>

@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-btn fa-check-circle"></i> Verification Success</div>
                    <div class="panel-body">
                        <div class="center-div">
                            <h2>{{$target['name_first'] . ' ' . $target['name_last']}} is verified!</h2>
                            @if($can_reset_password)
                                <a href="{{url('password', [$token])}}">
                                    <span class="big-icon"><i class="fa fa-check-circle text-success"></i></span>
                                    <h3>Click here to perform a password reset.</h3>
                                </a>
                            @else
                                <span class="big-icon"><i class="fa fa-check-circle text-success"></i></span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection