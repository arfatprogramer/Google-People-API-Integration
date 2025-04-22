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
        Schema::create('google_auths', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("email");
            $table->string("google_id");
            $table->string("googleAccessToken");
            $table->integer("accessTokenExpiresIn");
            $table->string("googleRefreshToken");
            $table->integer("refreshTokenExpiresIn");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_auths');
    }
};
