<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiber_buffers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trunk_cable_id')->constrained('trunk_cables')->cascadeOnDelete();
            $table->unsignedTinyInteger('buffer_number');
            $table->foreignId('fiber_color_id')->constrained('fiber_colors');
            $table->timestamps();

            $table->unique(['trunk_cable_id', 'buffer_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiber_buffers');
    }
};
