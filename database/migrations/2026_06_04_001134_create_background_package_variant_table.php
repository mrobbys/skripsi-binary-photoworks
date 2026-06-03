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
        Schema::create('background_package_variant', function (Blueprint $table) {
            $table->foreignId('background_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_variant_id')->constrained()->cascadeOnDelete();
            $table->primary(['background_id', 'package_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('background_package_variant');
    }
};
