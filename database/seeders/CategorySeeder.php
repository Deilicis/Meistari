<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Santehnika' => 'fa-solid fa-faucet',
            'Elektrika' => 'fa-solid fa-bolt',
            'Būvniecība un Remonts' => 'fa-solid fa-hammer',
            'Uzkopšana' => 'fa-solid fa-broom',
            'IT un Datori' => 'fa-solid fa-laptop',
            'Auto Remonts' => 'fa-solid fa-wrench',
        ];

        foreach ($categories as $name => $icon) {
            Category::updateOrCreate(
                [Category::SLUG => Str::slug($name)],
                [
                    Category::NAME => $name,
                    Category::ICON => $icon,
                ]
            );
        }
    }
}