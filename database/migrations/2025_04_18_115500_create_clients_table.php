<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('firstName')->nullable();              //"firstName" => null
            $table->string('lastName')->nullable();               //"lastName" => null
            $table->String('number')->nullable();                 //"number" => null
            $table->string('familyOrOrgnization')->nullable();    //"familyOrOrgnization" => null
            $table->string('email')->nullable();                  //"email" => null
            $table->string('panCardNumber')->nullable();          //"panCardNumber" => null
            $table->string('aadharCardNumber')->nullable();       //"aadharCardNumber" => null
            $table->string('occupation')->nullable();              //"occupation" => "Select"
            $table->string('kycStatus')->nullable();              //"kycStatus" => "Select"
            $table->decimal('anulIncome')->nullable();            //"anulIncome" => null
            $table->string('referredBy')->nullable();              //"referredBy" => null
            $table->string('totalInvestment')->nullable();        //"totalInvestment" => null
            $table->string('comments')->nullable();                //"comments" => null
            $table->string('relationshipManager')->nullable();    //"relationshipManager" => "Mo Arfat Ansari"
            $table->string('serviceRM')->nullable();              //"serviceRM" => null
            $table->decimal('totalSIP')->nullable();              //"totalSIP" => null
            $table->string('primeryContactPerson')->nullable();   //"primeryContactPerson" => null
            $table->string('meetinSchedule')->nullable();         //"meetinSchedule" => "Select"
            $table->string('firstMeetingDate')->nullable();       //"firstMeetingDate" => null
            $table->string('typeOfRelation')->nullable();         //"typeOfRelation" => "Select"
            $table->string('maritalStatus')->nullable();          //"maritalStatus" => "Select"
            $table->string('etag')->nullable();                   //"maritalStatus" => "Select"
            $table->string('resourceName')->nullable();           //"maritalStatus" => "Select"
            $table->string('syncStatus')->default('Not Synced');
            $table->string('lastSync')->nullable();             //"maritalStatus" => "Select"
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
