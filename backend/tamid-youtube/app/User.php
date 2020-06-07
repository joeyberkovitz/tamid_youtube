<?php

namespace App;

use BaoPham\DynamoDb\DynamoDbModel;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

// User model, extends DynamoDbModel to support DynamoDB
// To use MySQL or other DB, switch to extend Model
class User extends DynamoDbModel
{
    use \Illuminate\Auth\Authenticatable, Authorizable, CanResetPassword, \Illuminate\Auth\MustVerifyEmail;
    use HasApiTokens, Notifiable, UsesUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
