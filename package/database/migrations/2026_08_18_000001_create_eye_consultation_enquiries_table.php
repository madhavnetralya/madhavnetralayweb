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
        Schema::create('eye_consultation_enquiries', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('mobile');
            $table->text('concern');
            $table->string('status')->default('new'); // new | contacted | closed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eye_consultation_enquiries');
    }
};
