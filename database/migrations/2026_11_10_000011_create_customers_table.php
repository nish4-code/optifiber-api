<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('full_name', 150);
            $table->string('document', 30)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('plan', 50)->default('100 Mbps');
            $table->foreignId('nap_port_id')->nullable()->unique()->constrained('nap_ports')->nullOnDelete();
            $table->string('smartolt_onu_external_id', 100)->nullable();
            $table->string('status', 20)->default('activo');
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
