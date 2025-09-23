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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->longText('description');
            $table->string('location');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('employment_type');
            $table->string('application_url');
            $table->date('expiration_date');
            $table->string('category')->nullable();
            $table->boolean('remote_work_option')->default(false);
            $table->string('status')->default('draft');
            $table->boolean('featured')->default(false);
            $table->decimal('rpa', 8, 2)->nullable()->comment('Revenue Per Action');
            $table->timestamps();

            $table->index(['status', 'expiration_date']);
            $table->index(['employment_type']);
            $table->index(['featured']);
            $table->index(['publisher_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
