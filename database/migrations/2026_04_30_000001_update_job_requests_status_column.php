<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE job_requests MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'open'");

        DB::table('job_requests')->where('status', 'active')->update(['status' => 'open']);
        DB::table('job_requests')->where('status', 'assigned')->update(['status' => 'in_progress']);

        Schema::table('job_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('job_requests', 'accepted_application_id')) {
                $table->foreignId('accepted_application_id')->nullable()->constrained('applications')->nullOnDelete();
            }
            if (!Schema::hasColumn('job_requests', 'master_id')) {
                $table->foreignId('master_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('job_requests', 'agreed_price')) {
                $table->decimal('agreed_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('job_requests', 'price_type')) {
                $table->string('price_type')->nullable();
            }
            if (!Schema::hasColumn('job_requests', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
            if (!Schema::hasColumn('job_requests', 'master_completed_at')) {
                $table->timestamp('master_completed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        DB::table('job_requests')->where('status', 'open')->update(['status' => 'active']);
        DB::table('job_requests')->where('status', 'accepted')->update(['status' => 'active']);
        DB::table('job_requests')->where('status', 'in_progress')->update(['status' => 'assigned']);
        DB::table('job_requests')->where('status', 'awaiting_confirmation')->update(['status' => 'assigned']);
        DB::table('job_requests')->where('status', 'disputed')->update(['status' => 'assigned']);

        DB::statement("ALTER TABLE job_requests MODIFY COLUMN status ENUM('active','assigned','completed','cancelled') NOT NULL DEFAULT 'active'");

        Schema::table('job_requests', function (Blueprint $table) {
            $table->dropForeign(['accepted_application_id']);
            $table->dropForeign(['master_id']);
            $table->dropColumn(['accepted_application_id', 'master_id', 'agreed_price', 'price_type', 'completed_at', 'master_completed_at']);
        });
    }
};
