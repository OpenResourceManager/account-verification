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
        'reset_session_timeout',
        'uud_api_url',
        'uud_api_key',
        'ldap_enabled',
        'ldap_servers',
        'ldap_ssl',
        'ldap_bind_user',
        'ldap_bind_password',
        'ldap_search_base',
        'ldap_domain'
    ];
}
