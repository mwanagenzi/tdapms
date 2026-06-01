<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('monthly_rent', 12, 2)->unsigned();
            $table->decimal('deposit_amount', 12, 2)->unsigned();
            $table->enum('status', ['pending_deposit', 'active', 'terminating', 'terminated'])->default('pending_deposit');
            $table->timestamp('terminated_at')->nullable();
            $table->foreignId('terminated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('termination_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};
