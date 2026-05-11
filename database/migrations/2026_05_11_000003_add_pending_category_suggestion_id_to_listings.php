<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('pending_category_suggestion_id')
                ->nullable()
                ->nullOnDelete()
                ->constrained('category_suggestions')
                ->after('category_id');
        });

        Schema::table('job_requests', function (Blueprint $table) {
            $table->foreignId('pending_category_suggestion_id')
                ->nullable()
                ->nullOnDelete()
                ->constrained('category_suggestions')
                ->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['pending_category_suggestion_id']);
            $table->dropColumn('pending_category_suggestion_id');
        });

        Schema::table('job_requests', function (Blueprint $table) {
            $table->dropForeign(['pending_category_suggestion_id']);
            $table->dropColumn('pending_category_suggestion_id');
        });
    }
};
