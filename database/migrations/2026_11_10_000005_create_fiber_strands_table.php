<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiber_strands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trunk_cable_id')->constrained('trunk_cables')->cascadeOnDelete();
            $table->foreignId('fiber_buffer_id')->constrained('fiber_buffers')->cascadeOnDelete();
            $table->unsignedTinyInteger('strand_number');
            $table->unsignedSmallInteger('global_number');
            $table->foreignId('fiber_color_id')->constrained('fiber_colors');
            $table->string('status', 20)->default('libre');
            $table->timestamps();

            $table->unique(['trunk_cable_id', 'global_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiber_strands');
    }
};
