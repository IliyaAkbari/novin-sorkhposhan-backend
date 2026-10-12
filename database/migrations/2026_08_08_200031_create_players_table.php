<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');

            $table->string('username')->unique();

            $table->string('player_code')->unique();

            $table->string('age_group')->nullable();

            $table->string('position')->nullable();

            $table->string('phone')->nullable();

            $table->boolean('is_active')->default(true);
            
            $table->unsignedInteger('present_count')->default(0);

            $table->unsignedInteger('absent_count')->default(0);

            $table->date('subscription_start')->nullable();

            $table->date('subscription_end')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};