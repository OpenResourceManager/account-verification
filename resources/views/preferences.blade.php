@extends('layouts.app')

<?php
$pref = (App\Preference::all()->count() > 0) ? App\Preference::all()->first() : null;
//@todo clean up this bullshit
if (empty(old('company_name'))) {
    $company_name = (empty($pref)) ? '' : $pref->company_name;
} else {
    $company_name = old('company_name');
}

if (empty(old('application_name'))) {
    $app_name = (empty($pref)) ? '' : $pref->application_name;
} else {
    $app_name = old('application_name');
}

if (empty(old('application_email_address'))) {
    $app_email = (empty($pref)) ? '' : $pref->application_email;
} else {
    $app_email = old('application_email_address');
}

if(empty(old('company_logo_url'))) {
    $company_logo_url = (empty($pref)) ? '' : $pref->company_logo_url;
} else {
    $company_logo_url = old('company_logo_url');
}

if (empty(old('self_service_url'))) {
    $self_service_url = (empty($pref)) ? '' : $pref->self_service_url;
} else {
    $self_service_url = old('self_service_url');
}

if (empty(old('password_reset_session_timeout'))) {
    $reset_session_to = (empty($pref)) ? '' : $pref->reset_session_timeout;
} else {
    $reset_session_to = old('password_reset_session_timeout');
}

if (empty(old('ldap_servers'))) {
    $ldap_servers = (empty($pref->ldap_servers)) ? '' : $pref->ldap_servers;
} else {
    $ldap_servers = old('ldap_servers');
}

if (empty(old('ldap_search_base'))) {
    $ldap_search_base = (empty($pref->ldap_search_base)) ? '' : $pref->ldap_search_base;
} else {
    $ldap_search_base = old('ldap_search_base');
}

if (empty(old('ldap_domain'))) {
    $ldap_domain = (empty($pref->ldap_domain)) ? '' : $pref->ldap_domain;
} else {
    $ldap_domain = old('ldap_domain');
}

if (empty(old('ldap_bind_user'))) {
    $ldap_bind_user_dn = (empty($pref->ldap_bind_user)) ? '' : $pref->ldap_bind_user;
} else {
    $ldap_bind_user_dn = old('ldap_bind_user');
}

if (empty(old('ldap_bind_password'))) {
    $ldap_bind_password = (empty($pref->ldap_bind_password)) ? '' : $pref->ldap_bind_password;
} else {
    $ldap_bind_password = old('ldap_bind_password');
}

if (empty(old('ldap_ssl'))) {
    $ldap_ssl = (empty($pref->ldap_ssl)) ? 0 : $pref->ldap_ssl;
} else {
    $ldap_ssl = old('ldap_ssl');
}
if (isset($pref->ldap_ssl)) {
    $checked_ssl = ($pref->ldap_ssl == 1) ? 'checked' : '';
} else {
    $checked_ssl = '';
}
?>

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-btn fa-cogs"></i>Application Settings</div>
                    <div class="panel-body">
                        <form class="form-horizontal" role="form" method="POST" action="{{ url('/preferences') }}">
                            {!! csrf_field() !!}

                            <div class="form-group">
                                <div class="center-div form-group">
                                    <h3><i class="fa fa-cog"></i> General Info</h3>
                                    <p>Basic settings that are required.</p>
                                </div>

                                <div class="form-group{{ $errors->has('company_name') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label">Company Name</label>

                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="company_name"
                                               value="{{ $company_name }}" placeholder="The name of your institution.">

                                        @if ($errors->has('company_name'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('company_name') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('application_name') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label">Application Title</label>

                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="application_name"
                                               value="{{ $app_name }}" placeholder="Custom Application Name">

                                        @if ($errors->has('application_name'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('application_name') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('application_email_address') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label">Application Email Address</label>

                                    <div class="col-md-6">
                                        <input type="email" class="form-control" name="application_email_address"
                                               value="{{ $app_email }}" placeholder="no-reply@domain.tld">

                                        @if ($errors->has('application_email_address'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('application_email_address') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('password_reset_session_timeout') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label">Password Reset Session Timeout</label>

                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="password_reset_session_timeout"
                                               value="{{ $reset_session_to }}" placeholder="15 (min)">

                                        @if ($errors->has('password_reset_session_timeout'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('password_reset_session_timeout') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                            </div>

                            <div class="form-group">
                                <div class="center-div form-group">
                                    <h3><i class="fa fa-book"></i> LDAP Settings</h3>
                                    <p>LDAP settings are optional. They are required for resetting passwords.</p>
                                </div>

                                <div class="form-group{{ $errors->has('ldap_servers') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label">LDAP Servers</label>

                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="ldap_servers"
                                               value="{{ $ldap_servers }}"
                                               placeholder="dc-1.domain.tld, dc-2.domain.tld (Comma Separated)">

                                        @if ($errors->has('ldap_servers'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('ldap_servers') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('ldap_search_base') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label">LDAP Search Base DN</label>

                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="ldap_search_base"
                                               value="{{ $ldap_search_base }}" placeholder="OU=Users,DC=TLD,DC=DOMAIN">

                                        @if ($errors->has('ldap_search_base'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('ldap_search_base') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('ldap_domain') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label">LDAP Domain</label>

                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="ldap_domain"
                                               value="{{ $ldap_domain }}" placeholder="domain.tld">

                                        @if ($errors->has('ldap_domain'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('ldap_domain') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('ldap_bind_user') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label">Bind User</label>

                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="ldap_bind_user"
                                               value="{{ $ldap_bind_user_dn }}"
                                               placeholder="LdapUsername">

                                        @if ($errors->has('ldap_bind_user'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('ldap_bind_user') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('ldap_bind_password') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label">Bind Password</label>

                                    <div class="col-md-6">
                                        <input type="password" class="form-control" name="ldap_bind_password"
                                               value="{{ $ldap_bind_password }}" placeholder="Ldap User Password">

                                        @if ($errors->has('ldap_bind_password'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('ldap_bind_password') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label">Use LDAP SSL</label>
                                    <div class="col-md-6">
                                        <div class="material-switch pull-left">
                                            <input type="checkbox" id="ldap_ssl" name="ldap_ssl"
                                                   value="{{$ldap_ssl}}" {{$checked_ssl}}>
                                            <label for="ldap_ssl" class="label-primary"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="center-div form-group">
                                    <h3><i class="fa fa-cubes"></i> Other Settings</h3>
                                    <p>Other optional settings</p>
                                </div>
                                <div class="form-group{{ $errors->has('company_logo_url') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label">Company Logo URL</label>

                                    <div class="col-md-6">
                                        <input type="url" class="form-control" name="company_logo_url"
                                               value="{{ $company_logo_url }}"
                                               placeholder="https://cdn.domain.tld/logo.png">

                                        @if ($errors->has('company_logo_url'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('company_logo_url') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group{{ $errors->has('self_service_url') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label">Self Service URL</label>

                                    <div class="col-md-6">
                                        <input type="url" class="form-control" name="self_service_url"
                                               value="{{ $self_service_url }}"
                                               placeholder="https://password-reset.domain.tld">

                                        @if ($errors->has('self_service_url'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('self_service_url') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-btn fa-floppy-o"></i>Save Settings
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
