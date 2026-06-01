<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_report_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('item_name');
            $table->enum('condition', ['good', 'fair', 'damaged', 'missing'])->default('good');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_report_items');
    }
};
