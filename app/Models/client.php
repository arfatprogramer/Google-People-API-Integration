<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class client extends Model
{
    use HasFactory;
    protected $fillable = [
        'FirstName',
        'lastName',
        'number',
        'familyOrOrgnization',
        'email',
        'panCard',
        'aadharCard',
        'ocupation',
        'kycStatus',
        'anulIncome',
        'reffredBy',
        'totalInvestment',
        'comment',
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
