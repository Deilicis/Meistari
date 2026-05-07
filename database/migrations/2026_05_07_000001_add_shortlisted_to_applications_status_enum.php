<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM('pending','shortlisted','accepted','rejected','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("UPDATE applications SET status = 'pending' WHERE status = 'shortlisted'");
        DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM('pending','accepted','rejected','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }
};
