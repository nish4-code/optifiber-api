<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nap_boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('splitter_ratio', 20)->default('1:8');
            $table->unsignedSmallInteger('port_capacity')->default(8);
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->foreignId('feeder_strand_id')->nullable()->constrained('fiber_strands')->nullOnDelete();
            $table->string('map_color', 20)->default('#10B981');
            $table->string('map_icon', 30)->default('nap');
            $table->string('status', 20)->default('activo');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nap_boxes');
    }
};
