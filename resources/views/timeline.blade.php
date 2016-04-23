@extends('layouts.app')

<?php

$verification_requets = \App\VerificationRequest::orderBy('created_at', 'desc')->take(10)->get();
$count = 0;
?>

@section('content')
    <div class="container">
        <div class="row">
            <div>
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-expand"></i> Recent Activity</div>
                    <div class="panel-body">
                        <div class="container center-div inner_padding_right">
                            <div class="page-header">
                                <h1 id="timeline">Activity Timeline</h1>
                            </div>
                            @if($verification_requets->count() > 0)
                                <ul class="timeline padded_timeline">
                                    @foreach($verification_requets as $vr)
                                        <?php
                                        $tl_user = $vr->user;
                                        echo ($count % 2 == 0) ? '<li>' : '<li class="timeline-inverted">';
                                        ?>
                                        @if($vr->verified)
                                            <div class="timeline-badge success"><i class="fa fa-check-circle"></i>
                                            </div>
                                        @else
                                            <div class="timeline-badge danger"><i class="fa fa-times-circle"></i>
                                            </div>
                                        @endif
                                        <div class="timeline-panel">
                                            <div class="timeline-heading">
                                                <h4 class="timeline-title">Verifier: {{$tl_user->name}}</h4>
                                            </div>
                                            <div class="timeline-body">
                                                @if($tl_user->id == Auth::user()->id)
                                                    <a href="{{url('/profile')}}">
                                                        <img src="{{Gravatar::src( $tl_user->email , 256)}}"
                                                             width="96">
                                                    </a>
                                                @else
                                                    <a href="{{url('/users/'.$tl_user->id)}}">
                                                        <img src="{{Gravatar::src( $tl_user->email , 256)}}"
                                                             width="96">
                                                    </a>
                                                @endif
                                                <p>
                                                    A verification request was made by, {{$tl_user->name}}.
                                                </p>
                                                <p>
                                                    This request was made on the behalf
                                                    of, {{$vr->request_username}}.
                                                </p>
                                                <p>
                                                    The request
                                                    was {{$vr->verified ? 'verified' : 'determined to be invalid'}}
                                                    at {{$vr->updated_at->format('g:i a')}}
                                                    on {{$vr->updated_at->format('l, F j, Y')}}.
                                                </p>
                                            </div>
                                        </div>
                                        </li>
                                        <?php $count++; ?>
                                    @endforeach
                                </ul>
                            @else
                                <h4>Nothing here, come back later.</h4>
                            @endif

                        </div>
                        <br/>
                        <br/>
                    </div>

                </div>
            </div>
        </div>
@endsection
