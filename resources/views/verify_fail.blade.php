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
                    <div class="panel-heading"><i class="fa fa-btn fa-times-circle"></i> Verification Failure</div>
                    <div class="panel-body">
                        <div class="center-div">
                            <h2>{{$target['username']}} failed to pass verification!</h2>
                            <span class="big-icon"><i class="fa fa-times-circle text-danger"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection