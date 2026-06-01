<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('unit_number');
            $table->enum('type', ['bedsitter', '1br', '2br', '3br', 'studio', 'commercial', 'other'])->default('1br');
            $table->string('floor')->nullable();
            $table->decimal('size_sqft', 8, 2)->unsigned()->nullable();
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
