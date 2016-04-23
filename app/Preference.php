<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Preference extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'application_name',
        'application_email',
        'ldap_servers',
        'ldap_port',
        'ldap_ssl',
        'ldap_bind_user_dn',
        'ldap_bind_password',
        'ldap_search_base',
        'ldap_domain',
    ];
}
