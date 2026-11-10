<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('severity', 20)->default('advertencia');
            $table->string('title', 150);
            $table->text('message');
            $table->nullableMorphs('alertable');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_alerts');
    }
};
