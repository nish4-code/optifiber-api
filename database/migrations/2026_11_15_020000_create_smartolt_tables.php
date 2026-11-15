<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartolt_olts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id', 100)->unique();
            $table->string('name', 150);
            $table->string('olt_hardware_version', 100)->nullable();
            $table->string('ip', 50)->nullable();
            $table->unsignedInteger('telnet_port')->nullable();
            $table->unsignedInteger('snmp_port')->nullable();
            $table->string('status', 30)->default('online');
            $table->jsonb('metrics')->nullable();
            $table->timestampTz('last_synced_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('smartolt_zones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id', 100)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->timestampsTz();
        });

        Schema::create('smartolt_odbs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id', 100)->unique();
            $table->string('name', 150);
            $table->foreignUuid('zone_id')->nullable()->constrained('smartolt_zones')->nullOnDelete();
            $table->foreignUuid('smartolt_olt_id')->nullable()->constrained('smartolt_olts')->nullOnDelete();
            $table->string('pon_port', 50)->nullable();
            $table->unsignedSmallInteger('total_ports')->default(16);
            $table->unsignedSmallInteger('used_ports')->default(0);
            $table->timestampsTz();
        });

        Schema::create('smartolt_speed_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id', 100)->unique();
            $table->string('name', 150);
            $table->string('download_speed', 50)->nullable();
            $table->string('upload_speed', 50)->nullable();
            $table->timestampsTz();
        });

        Schema::create('smartolt_onus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('unique_external_id', 100)->unique();
            $table->string('sn', 100)->unique();
            $table->string('name', 150)->nullable()->index();
            $table->foreignUuid('smartolt_olt_id')->nullable()->constrained('smartolt_olts')->nullOnDelete();
            $table->foreignUuid('zone_id')->nullable()->constrained('smartolt_zones')->nullOnDelete();
            $table->foreignUuid('odb_id')->nullable()->constrained('smartolt_odbs')->nullOnDelete();
            $table->string('pon_type', 20)->default('gpon');
            $table->string('board', 20)->nullable();
            $table->string('port', 20)->nullable();
            $table->unsignedSmallInteger('onu_number')->nullable();
            $table->string('onu_type_id', 50)->nullable();
            $table->string('onu_type_name', 100)->nullable();
            $table->string('status', 30)->default('Online')->index();
            $table->string('administrative_status', 30)->default('Enabled');
            $table->string('catv_status', 30)->nullable();
            $table->string('signal_value', 100)->nullable();
            $table->string('signal_1310', 50)->nullable();
            $table->string('signal_1490', 50)->nullable();
            $table->decimal('signal_rx', 5, 2)->nullable();
            $table->decimal('signal_tx', 5, 2)->nullable();
            $table->unsignedInteger('distance_m')->nullable();
            $table->string('mode', 50)->nullable();
            $table->unsignedSmallInteger('vlan')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->index(['latitude', 'longitude']);
            $table->jsonb('extra_data')->nullable();
            $table->timestampTz('last_synced_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('smartolt_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_type', 100)->index();
            $table->string('onu_sn', 100)->nullable()->index();
            $table->string('olt_id', 100)->nullable()->index();
            $table->string('severity', 30)->default('info');
            $table->jsonb('payload');
            $table->timestampTz('occurred_at');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartolt_events');
        Schema::dropIfExists('smartolt_onus');
        Schema::dropIfExists('smartolt_speed_profiles');
        Schema::dropIfExists('smartolt_odbs');
        Schema::dropIfExists('smartolt_zones');
        Schema::dropIfExists('smartolt_olts');
    }
};
