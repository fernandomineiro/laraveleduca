<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WirecardAccount extends Model
{
    protected $table = 'wirecard_account';
    protected $fillable = ['account_id', 'access_token', 'channel_id'];
    public $timestamps = false;
    
    public $rules = [
        'account_id'   => 'required',
        'access_token' => 'required',
        'channel_id'   => 'required'
    ];
}
