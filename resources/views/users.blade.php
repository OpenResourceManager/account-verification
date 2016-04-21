@extends('layouts.app')

@section('content')
    <?php

    function makeTile($fuser, $me, $trashed = false)
    {
        if ($fuser->id == $me->id) {
            echo '<a href="/profile">';
        } elseif ($trashed) {
            echo '<a href="/users/' . $fuser->id . '/trash">';
        } else {
            echo '<a href="/users/' . $fuser->id . '">';
        }
        echo '
        <div class="center-div row-style col-md-2">
        <h4>' . $fuser->name . '</h4>
        <img src=" ' . Gravatar::src($fuser->email, 512) . ' " width="96">
        </div>
        </a> ';
    }

    function newUserTile()
    {

        echo '<a href="' . URL::to('users/new') . '">
        <div class="center-div row-style col-md-2">
        <h4>Create User</h4>
        <span class="fa fa-user-plus new-user-icon"></span>
        </div>
        </a>
        ';
    }

    $users = \App\User::all();
    $count = 0;
    $trashed = \App\User::onlyTrashed()->get();
    $trashCount = 0;
    ?>
    <div class="container">
        <div class="row">
            <div class="col-lg-10 col-md-12 col-lg-offset-1 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-btn fa-users"></i> Active Users</div>
                    <div class="panel-body">
                        <div class="row center-div">
                            @if(Auth::user()->isAdmin)
                                <?php
                                newUserTile();
                                $count++;
                                ?>
                            @endif
                            @foreach($users as $user)
                                @if($count >= 5)
                        </div>
                        <div class="row center-div">
                            <?php makeTile($user, Auth::user());
                            $count = 0;
                            ?>
                            @else
                                <?php makeTile($user, Auth::user()); ?>
                            @endif
                            <?php $count++;?>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($trashed->count() > 0)
        <div class="container">
            <div class="row">
                <div class="col-lg-10 col-md-12 col-lg-offset-1 col-md-offset-1">
                    <div class="panel panel-default">
                        <div class="panel-heading"><i class="fa fa-btn fa-trash"></i> Trashed Users</div>
                        <div class="panel-body">
                            <div class="row center-div">
                                @foreach($trashed as $tuser)
                                    @if($trashCount >= 5)
                            </div>
                            <div class="row center-div">
                                <?php makeTile($tuser, Auth::user(), true);
                                $trashCount = 0;
                                ?>
                                @else
                                    <?php makeTile($tuser, Auth::user(), true); ?>
                                @endif
                                <?php $trashCount++;?>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
