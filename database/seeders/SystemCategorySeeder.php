<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class SystemCategorySeeder extends Seeder
{
    public function run(): void
    {
        if (Category::where(Category::SLUG, 'cits')->exists()) {
            return;
        }

        Category::create([
            Category::NAME => 'Cits',
            Category::SLUG => 'cits',
            Category::ICON => 'EllipsisHorizontalCircleIcon',
            Category::PARENT_ID => null,
            Category::IS_SYSTEM => true,
        ]);
    }
}
