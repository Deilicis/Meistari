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
            'Santehnika'           => 'WrenchScrewdriverIcon',
            'Elektrika'            => 'BoltIcon',
            'Būvniecība un Remonts' => 'HomeModernIcon',
            'Uzkopšana'            => 'SparklesIcon',
            'IT un Datori'         => 'ComputerDesktopIcon',
            'Auto Remonts'         => 'TruckIcon',
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