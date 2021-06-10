<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserInvitationsToken extends Model
{
    protected $fillable = [
         'email', 'invitation_token'
    ];
}
