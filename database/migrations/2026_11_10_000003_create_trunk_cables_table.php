<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trunk_cables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->unsignedSmallInteger('fiber_count')->default(144);
            $table->unsignedSmallInteger('buffer_count')->default(12);
            $table->unsignedSmallInteger('strands_per_buffer')->default(12);
            $table->jsonb('path')->nullable();
            $table->decimal('length_km', 8, 3)->default(0);
            $table->string('map_color', 20)->default('#3B82F6');
            $table->string('status', 20)->default('activo');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trunk_cables');
    }
};
