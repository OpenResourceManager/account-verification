@extends('layouts.app')

<?php

$verification_requets = \App\VerificationRequest::orderBy('created_at', 'desc')->take(10)->get();

?>

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading center-div"><h1><i class="fa fa-dashboard"></i> Dashboard</h1></div>
                    <div class="panel-body">
                        <div class="container center-div">
                            <h3>Recent Requests</h3>
                            <ul class="timeline">
                                @foreach($verification_requets as $vr)
                                    <?php
                                    $tl_user = $vr->user;
                                    ?>
                                    <li>
                                        @if($vr->verified)
                                            <div class="timeline-badge success"><i class="fa fa-check-circle"></i></div>
                                        @else
                                            <div class="timeline-badge danger"><i class="fa fa-times-circle"></i></div>
                                        @endif
                                        <div class="timeline-panel">
                                            <div class="timeline-heading">
                                                <h4 class="timeline-title">Verifier: {{$tl_user->name}}</h4>
                                            </div>
                                            <div class="timeline-body">
                                                @if($tl_user->id == Auth::user()->id)
                                                    <a href="{{url('/profile')}}">
                                                        <img src="{{Gravatar::src( $tl_user->email , 256)}}" width="96">
                                                    </a>
                                                @else
                                                    <a href="{{url('/users/'.$tl_user->id)}}">
                                                        <img src="{{Gravatar::src( $tl_user->email , 256)}}" width="96">
                                                    </a>
                                                @endif
                                                <p>
                                                    A verification request was made by, {{$tl_user->name}}.
                                                </p>
                                                <p>
                                                    This request was made on the behalf of, {{$vr->request_username}}.
                                                </p>
                                                <p>
                                                    The request was {{$vr->verified ? 'verified' : 'determined to be invalid'}}
                                                    at {{$vr->updated_at->format('g:i a')}} on {{$vr->updated_at->format('l, F j, Y')}}.
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                        </div>
                        <br/>
                        <br/>
                    </div>

                </div>
            </div>
        </div>
@endsection
