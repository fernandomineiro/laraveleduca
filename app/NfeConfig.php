<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NfeConfig extends Model
{
    protected $fillable = ['authorization_key', 'company_id', 'url_api'];
    protected $primaryKey = 'id';
    protected $table = "nfe_config";
    public $timestamps = false;    
}
