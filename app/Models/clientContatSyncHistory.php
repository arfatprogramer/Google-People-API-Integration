<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class clientContatSyncHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'created',
        'updated',
        'deleted',
        'error',
    ];
}
