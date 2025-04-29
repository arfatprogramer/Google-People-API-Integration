<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class client extends Model
{
    use HasFactory;
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'number',
        'familyOrOrgnization',
        'panCardNumber',
        'aadharCardNumber',
        'occupation',
        'kycStatus',
        'anulIncome',
        'referredBy',
        'totalInvestment',
        'comments',
        'relationshipManager',
        'serviceRM',
        'totalSIP',
        'primeryContactPerson',
        'meetinSchedule',
        'firstMeetingDate',
        'typeOfRelation',
        'maritalStatus',
    ];
}
