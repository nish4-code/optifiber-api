<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiber_colors', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('position')->unique();
            $table->string('name', 50);
            $table->string('tia_name', 50);
            $table->string('hex', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiber_colors');
    }
};
