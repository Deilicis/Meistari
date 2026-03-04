<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'profiles';
    private const CITY = 'city';
    private const FIRST_NAME = 'first_name';
    private const LAST_NAME = 'last_name';
    private const COMPANY_NAME = 'company_name';
    private const REG_NUMBER = 'reg_number';
    private const VAT_NUMBER = 'vat_number';

    public function up(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->string(self::CITY)->nullable()->change();
            $table->string(self::FIRST_NAME)->nullable()->change();
            $table->string(self::LAST_NAME)->nullable()->change();
            $table->string(self::COMPANY_NAME)->nullable()->change();
            $table->string(self::REG_NUMBER)->nullable()->change();
            $table->string(self::VAT_NUMBER)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->string(self::CITY)->nullable(false)->change();
            $table->string(self::FIRST_NAME)->nullable(false)->change();
            $table->string(self::LAST_NAME)->nullable(false)->change();
            $table->string(self::COMPANY_NAME)->nullable(false)->change();
            $table->string(self::REG_NUMBER)->nullable(false)->change();
            $table->string(self::VAT_NUMBER)->nullable(false)->change();
        });
    }
};