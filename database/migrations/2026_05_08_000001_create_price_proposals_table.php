<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('proposed_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('price_type')->default('fixed');
            $table->text('note')->nullable();
            $table->string('status');
            $table->foreignId('responded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();

            $table->index(['job_application_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_proposals');
    }
};
