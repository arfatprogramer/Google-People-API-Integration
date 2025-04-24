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
        Schema::create('client_contat_sync_histories', function (Blueprint $table) {
            $table->id();
            $table->string('created')->nullable();
            $table->string('updated')->nullable();
            $table->string('deleted')->nullable();
            $table->string('error')->nullable();
            $table->string('synToken')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_contat_sync_histories');
    }
};
