<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerificationRequest extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'verified',
        'request_username',
        'request_identifier',
        'request_ssn',
        'request_dob',
        'returned_username',
        'returned_identifier',
        'returned_ssn',
        'returned_dob',
        'returned_user_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('\App\User')->withTrashed();
    }

}
