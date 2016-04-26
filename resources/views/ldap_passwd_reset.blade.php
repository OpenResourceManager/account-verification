<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 4/20/16
 * Time: 10:24 PM
 */

?>

@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-btn fa-unlock-alt"></i> User Password Reset</div>
                    <div class="panel-body">
                        <form class="form-horizontal" role="form" method="POST"
                              action="{{ url('password', [$token]) }}">
                            {!! csrf_field() !!}

                            <div class="center-div form-group">
                                <p>Ask the user for an external email.</p>
                            </div>

                            @if(!empty($emails))
                                <div class="center-div form-group">
                                    <h3>Option 1:</h3>
                                    <p>The user's external email address may be in this list.</p>
                                </div>

                                <div class="form-group{{ $errors->has('known_email_address') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label" for="known_email_select">Known E-Mail
                                        Addresses</label>
                                    <div class="col-md-6">
                                        <select class="form-control" id="known_email_address"
                                                name="known_email_address">
                                            <option value="">-- select or enter a new address --
                                            </option>
                                            @foreach($emails as $email_rec)
                                                <option value="{{$email_rec['email']}}">{{$email_rec['email']}}</option>
                                            @endforeach
                                        </select>

                                        @if ($errors->has('known_email_address'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('known_email_address') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(!empty($emails))
                                <div class="center-div form-group">
                                    <h3>Option 2:</h3>
                                    <p>If the user's external email is not in the known email list. Provide a new external email
                                        here.</p>
                                </div>
                            @endif

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label class="col-md-4 control-label">New External E-Mail Address</label>

                                <div class="col-md-6">
                                    <input type="email" class="form-control" name="email"
                                           value="{{ old('email') }}" placeholder="some-email@gmail.com">

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('email_confirmation') ? ' has-error' : '' }}">
                                <label class="col-md-4 control-label">Confirm New E-Mail Address</label>

                                <div class="col-md-6">
                                    <input type="email" class="form-control" name="email_confirmation"
                                           value="{{ old('email_confirmation') }}" placeholder="some-email@gmail.com">

                                    @if ($errors->has('email_confirmation'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('email_confirmation') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-btn fa-paper-plane"></i>Send Reset
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
