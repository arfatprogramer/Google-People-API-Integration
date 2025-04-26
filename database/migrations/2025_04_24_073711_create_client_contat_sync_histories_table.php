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
            $table->integer('created')->default(0);
            $table->integer('updated')->default(0);
            $table->integer('deleted')->default(0);
            $table->integer('error')->default(0);
            $table->integer('batches')->default(0);
            $table->integer('status')->default(0);
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
