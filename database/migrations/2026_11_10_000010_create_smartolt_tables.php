<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartolt_olts', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->unique();
            $table->string('name', 150);
            $table->string('ip', 50)->nullable();
            $table->string('hardware_version', 100)->nullable();
            $table->string('status', 30)->default('online');
            $table->jsonb('metrics')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('smartolt_onus', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->unique();
            $table->string('sn', 100)->unique();
            $table->string('name', 150)->nullable();
            $table->foreignId('smartolt_olt_id')->nullable()->constrained('smartolt_olts')->nullOnDelete();
            $table->string('board', 20)->nullable();
            $table->string('port', 20)->nullable();
            $table->unsignedSmallInteger('onu_number')->nullable();
            $table->string('zone', 100)->nullable();
            $table->string('odb', 100)->nullable();
            $table->string('status', 30)->default('Online');
            $table->decimal('signal_rx', 5, 2)->nullable();
            $table->decimal('signal_tx', 5, 2)->nullable();
            $table->unsignedInteger('distance_m')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartolt_onus');
        Schema::dropIfExists('smartolt_olts');
    }
};
