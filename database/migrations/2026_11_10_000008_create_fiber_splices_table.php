<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiber_splices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('splice_closure_id')->constrained('splice_closures')->cascadeOnDelete();
            $table->unsignedSmallInteger('tray_number')->default(1);
            $table->foreignId('strand_in_id')->constrained('fiber_strands')->cascadeOnDelete();
            $table->foreignId('strand_out_id')->nullable()->constrained('fiber_strands')->nullOnDelete();
            $table->foreignId('nap_box_id')->nullable()->constrained('nap_boxes')->nullOnDelete();
            $table->decimal('loss_db', 5, 2)->default(0.05);
            $table->string('status', 20)->default('activo');
            $table->timestamps();

            $table->unique(['splice_closure_id', 'strand_in_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiber_splices');
    }
};
