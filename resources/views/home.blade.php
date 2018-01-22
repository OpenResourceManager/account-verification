@extends('layouts.app')

<?php
//dd($errors);
?>

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-btn fa-check-circle"></i> User Verification</div>
                    <div class="panel-body">
                        <form class="form-horizontal" role="form" method="POST" action="{{ url('/verify') }}">
                            {!! csrf_field() !!}

                            <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                                <label class="col-md-4 control-label">Username</label>

                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="username"
                                           value="{{ old('username') }}" placeholder="skywal">

                                    @if ($errors->has('username'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('identifier') ? ' has-error' : '' }}">
                                <label class="col-md-4 control-label">ID Number</label>

                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="identifier"
                                           value="{{ old('identifier') }}" placeholder="0170630">

                                    @if ($errors->has('identifier'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('identifier') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('dob') ? ' has-error' : '' }}">
                                <label class="col-md-4 control-label">Date of Birth</label>

                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="dob"
                                           value="{{ old('dob') }}" placeholder="1992-01-05 (yyyy-mm-dd)">

                                    @if ($errors->has('dob'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('dob') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-btn fa-unlock"></i>Verify
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
