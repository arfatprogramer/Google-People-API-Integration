<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientAddress extends Model
{
    protected $table = "client_address";
    protected $fillable = [
        'client_id',
        'address_type',
        'street',
        'area',
        'city',
        'state',
        'postal_code',
        'country',
    ];
}
