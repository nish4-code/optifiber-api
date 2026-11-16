<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smartolt_olts', function (Blueprint $table) {
            if (! Schema::hasColumn('smartolt_olts', 'telnet_port')) {
                $table->unsignedInteger('telnet_port')->nullable()->after('ip');
            }
            if (! Schema::hasColumn('smartolt_olts', 'snmp_port')) {
                $table->unsignedInteger('snmp_port')->nullable()->after('telnet_port');
            }
            if (! Schema::hasColumn('smartolt_olts', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('status');
            }
            if (! Schema::hasColumn('smartolt_olts', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });

        Schema::create('smartolt_zones', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('smartolt_odbs', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->unique();
            $table->string('name', 150);
            $table->foreignId('zone_id')->nullable()->constrained('smartolt_zones')->nullOnDelete();
            $table->foreignId('smartolt_olt_id')->nullable()->constrained('smartolt_olts')->nullOnDelete();
            $table->string('pon_port', 50)->nullable();
            $table->unsignedSmallInteger('total_ports')->default(16);
            $table->unsignedSmallInteger('used_ports')->default(0);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        Schema::create('smartolt_speed_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->unique();
            $table->string('name', 150);
            $table->string('download_speed', 50)->nullable();
            $table->string('upload_speed', 50)->nullable();
            $table->timestamps();
        });

        Schema::table('smartolt_onus', function (Blueprint $table) {
            if (! Schema::hasColumn('smartolt_onus', 'zone_id')) {
                $table->foreignId('zone_id')->nullable()->after('smartolt_olt_id')->constrained('smartolt_zones')->nullOnDelete();
            }
            if (! Schema::hasColumn('smartolt_onus', 'odb_id')) {
                $table->foreignId('odb_id')->nullable()->after('zone_id')->constrained('smartolt_odbs')->nullOnDelete();
            }
            if (! Schema::hasColumn('smartolt_onus', 'pon_type')) {
                $table->string('pon_type', 20)->default('gpon')->after('odb_id');
            }
            if (! Schema::hasColumn('smartolt_onus', 'onu_type_id')) {
                $table->string('onu_type_id', 50)->nullable()->after('onu_number');
            }
            if (! Schema::hasColumn('smartolt_onus', 'onu_type_name')) {
                $table->string('onu_type_name', 100)->nullable()->after('onu_type_id');
            }
            if (! Schema::hasColumn('smartolt_onus', 'administrative_status')) {
                $table->string('administrative_status', 30)->default('Enabled')->after('status');
            }
            if (! Schema::hasColumn('smartolt_onus', 'catv_status')) {
                $table->string('catv_status', 30)->nullable()->after('administrative_status');
            }
            if (! Schema::hasColumn('smartolt_onus', 'signal_value')) {
                $table->string('signal_value', 100)->nullable()->after('catv_status');
            }
            if (! Schema::hasColumn('smartolt_onus', 'signal_1310')) {
                $table->string('signal_1310', 50)->nullable()->after('signal_value');
            }
            if (! Schema::hasColumn('smartolt_onus', 'signal_1490')) {
                $table->string('signal_1490', 50)->nullable()->after('signal_1310');
            }
            if (! Schema::hasColumn('smartolt_onus', 'mode')) {
                $table->string('mode', 50)->nullable()->after('distance_m');
            }
            if (! Schema::hasColumn('smartolt_onus', 'vlan')) {
                $table->unsignedSmallInteger('vlan')->nullable()->after('mode');
            }
            if (! Schema::hasColumn('smartolt_onus', 'address')) {
                $table->text('address')->nullable()->after('vlan');
            }
            if (! Schema::hasColumn('smartolt_onus', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('address');
            }
            if (! Schema::hasColumn('smartolt_onus', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (! Schema::hasColumn('smartolt_onus', 'extra_data')) {
                $table->jsonb('extra_data')->nullable()->after('longitude');
            }
        });

        Schema::create('smartolt_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100)->index();
            $table->string('onu_sn', 100)->nullable()->index();
            $table->string('olt_id', 100)->nullable()->index();
            $table->string('severity', 30)->default('info');
            $table->jsonb('payload');
            $table->timestampTz('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartolt_events');

        Schema::table('smartolt_onus', function (Blueprint $table) {
            $table->dropConstrainedForeignId('odb_id');
            $table->dropConstrainedForeignId('zone_id');
            $table->dropColumn([
                'pon_type',
                'onu_type_id',
                'onu_type_name',
                'administrative_status',
                'catv_status',
                'signal_value',
                'signal_1310',
                'signal_1490',
                'mode',
                'vlan',
                'address',
                'latitude',
                'longitude',
                'extra_data',
            ]);
        });

        Schema::dropIfExists('smartolt_speed_profiles');
        Schema::dropIfExists('smartolt_odbs');
        Schema::dropIfExists('smartolt_zones');

        Schema::table('smartolt_olts', function (Blueprint $table) {
            $table->dropColumn(['telnet_port', 'snmp_port', 'latitude', 'longitude']);
        });
    }
};
