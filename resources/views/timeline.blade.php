@extends('layouts.app')


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
                            @if($total > 0)
                                <ul class="timeline padded_timeline">
                                    @foreach($merged as $request)
                                        <?php
                                        $tl_user = $request->user->withTrashed();
                                        $class = null;
                                        $expired = null;
                                        switch (strval(get_class($request))) {
                                            case 'App\VerificationRequest' :
                                                $class = 'vr';
                                                break;
                                            case 'App\LDAPPasswordReset' :
                                                $class = 'pr';
                                                $created = strtotime($request->created_at);
                                                $time_since_request = round(abs(strtotime(\Carbon\Carbon::now()) - $created) / 60);
                                                if ($time_since_request < $prefs->reset_session_timeout && $request->pending) {
                                                    $expired = false;
                                                } else {
                                                    $expired = true;
                                                }
                                                break;
                                        }
                                        echo ($count % 2 == 0) ? '<li>' : '<li class="timeline-inverted">';
                                        ?>
                                        @if($class == 'vr')
                                            @if($request->verified)
                                                <div class="timeline-badge success"><i class="fa fa-check-circle"></i>
                                                </div>
                                            @else
                                                <div class="timeline-badge danger"><i class="fa fa-times-circle"></i>
                                                </div>
                                            @endif
                                        @else
                                            @if($request->pending)
                                                @if($expired)
                                                    <div class="timeline-badge danger"><i
                                                                class="fa fa-hourglass-end"></i></div>
                                                @else
                                                    <div class="timeline-badge info">
                                                        <i class="fa fa-hourglass-start"></i>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="timeline-badge success"><i class="fa fa-unlock-alt"></i>
                                                </div>
                                            @endif
                                        @endif

                                        <div class="timeline-panel">
                                            <div class="timeline-heading">
                                                @if($class == 'vr')
                                                    <h2 class="timeline-title">User Verification</h2>
                                                @else
                                                    <h2 class="timeline-title">Password Reset</h2>
                                                @endif
                                                <h4 class="timeline-title">Verifier: {{$tl_user->name}}</h4>
                                            </div>
                                            <div class="timeline-body">
                                                @if($tl_user->id == Auth::user()->id)
                                                    <a target="_blank" href="{{url('profile')}}">
                                                        <img src="{{Gravatar::src( $tl_user->email , 256)}}"
                                                             width="96">
                                                    </a>
                                                @else
                                                    <a target="_blank" href="{{url('users', $tl_user->id)}}">
                                                        <img src="{{Gravatar::src( $tl_user->email , 256)}}"
                                                             width="96">
                                                    </a>
                                                @endif

                                                <p>
                                                    A {{ ($class == 'vr') ? 'user verification' : 'password reset'  }}
                                                    request was
                                                    made by, {{$tl_user->name}}.
                                                </p>

                                                <p>
                                                    This request was made on the behalf
                                                    of, {{$request->request_username}}.
                                                </p>

                                                <p>
                                                    @if($class == 'vr')
                                                        The request
                                                        was {{$request->verified ? 'verified' : 'determined to be invalid'}}
                                                        at {{$request->updated_at->format('g:i a')}}
                                                        on {{$request->updated_at->format('l, F j, Y')}}.
                                                    @else
                                                        @if($request->pending)
                                                            @if($expired)
                                                                The request expired
                                                                at {{$request->updated_at->format('g:i a')}}
                                                                on {{$request->updated_at->format('l, F j, Y')}}.
                                                            @else
                                                                <a href="{{url('password', $request->token)}}">The
                                                                    request</a> is still pending as
                                                                of {{$request->updated_at->format('g:i a')}}  -
                                                                {{$request->updated_at->format('l, F j, Y')}}.
                                                            @endif
                                                        @else
                                                            The request was completed
                                                            at {{$request->updated_at->format('g:i a')}}
                                                            on {{$request->updated_at->format('l, F j, Y')}}.
                                                        @endif
                                                    @endif
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

                    <div class="panel-footer center-div">
                        @if($reset_requests->count() > 0)
                            {{$reset_requests->links()}}
                        @else
                            {{$verification_requets->links()}}
                        @endif
                    </div>

                </div>
            </div>
        </div>
@endsection
