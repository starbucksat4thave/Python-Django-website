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
        Schema::create('application_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique(); // e.g. leave, transcript
            $table->string('title');
            $table->longText('body'); // template with placeholders like %name%
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_templates');
    }
};
