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
            $table->integer('createdAtGoogle')->default(0);
            $table->integer('updated')->default(0);
            $table->integer('updatedAtGoogle')->default(0);
            $table->integer('deleted')->default(0);
            $table->integer('deletedAtGoogle')->default(0);
            $table->integer('error')->default(0);
            $table->integer('synced')->default(0);
            $table->integer('pending')->default(0);
            $table->integer('batches')->default(0);
            $table->string('status')->default("Pending");
            $table->integer('startTime')->nullable();
            $table->integer('extimetedTime')->nullable();
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
