<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('splice_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('type', 30)->default('empalme');
            $table->string('mount_type', 30)->default('poste');
            $table->unsignedSmallInteger('fiber_capacity')->default(144);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('reserve_meters', 8, 2)->default(0);
            $table->string('status', 20)->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('splice_closures');
    }
};
