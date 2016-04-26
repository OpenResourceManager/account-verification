@extends('layouts.error')

@section('content')
    <div class="container">
        <div class="row">
            <div class="center-div">
                <h2>We're undergoing routine maintenance.</h2>
                <h4>(The hamsters that power this site are taking a water break.)</h4>
                <img src="img/maintenance.svg" width="256" alt="System Maintenance">
                <h2>We'll be right back. Thank you for your patience!</h2>
            </div>
        </div>
    </div>
@endsection

@section('foot')
    <div class="navbar navbar-fixed-bottom">
        <div class="footer center-div">
            <p><i class="fa fa-creative-commons"></i> "<a about="_blank"
                                                          href="https://thenounproject.com/term/hamster-wheel/4739">Hamster
                    Wheel</a>" by <a
                        about="_blank" href="https://thenounproject.com/olivierguin">Olivier Guin</a> is licensed under
                <a
                        about="_blank" href="https://creativecommons.org/licenses/by/3.0/us/legalcode">CC BY 3.0</a></p>
        </div>
    </div>
@endsection