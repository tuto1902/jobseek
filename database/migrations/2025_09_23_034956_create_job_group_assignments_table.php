<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_group_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_group_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight_percentage', 5, 2);
            $table->timestamps();

            $table->unique(['job_posting_id', 'job_group_id']);
            $table->index('job_group_id');
            $table->index('weight_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_group_assignments');
    }
};
