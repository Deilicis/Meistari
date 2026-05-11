<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suggested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->foreignId('parent_category_id')->nullable()->nullOnDelete()->constrained('categories');
            $table->text('note')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by_user_id')->nullable()->nullOnDelete()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->foreignId('resulting_category_id')->nullable()->nullOnDelete()->constrained('categories');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index('suggested_by_user_id');
            $table->index('parent_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_suggestions');
    }
};
