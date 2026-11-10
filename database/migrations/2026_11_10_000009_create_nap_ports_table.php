<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nap_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nap_box_id')->constrained('nap_boxes')->cascadeOnDelete();
            $table->unsignedSmallInteger('port_number');
            $table->string('status', 20)->default('libre');
            $table->timestamps();

            $table->unique(['nap_box_id', 'port_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nap_ports');
    }
};
