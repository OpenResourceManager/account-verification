<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Preference extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'company_name',
        'application_name',
        'application_email',
        'self_service_url',
        'company_logo_url',
        'reset_session_timeout',
        'ldap_enabled',
        'ldap_servers',
        'ldap_ssl',
        'ldap_bind_user',
        'ldap_bind_password',
        'ldap_search_base',
        'ldap_domain'
    ];
}
