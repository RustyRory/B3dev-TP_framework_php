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
        Schema::create('films', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('producer');
            $table->unsignedSmallInteger('release_year');
            $table->unsignedSmallInteger('time');
            $table->string('genres');
            $table->text('synopsis');
            $table->string('poster_url');
            $table->string('trailer_url');
            $table->string('actors');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('films');
    }
};
