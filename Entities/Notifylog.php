<?php

namespace Modules\PaiementGateways\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notifylog extends Model
{
    use HasFactory;

    
    public $table = 'notifylogs';

    protected $fillable = ['mode','invoice','status','error','response'];
}
