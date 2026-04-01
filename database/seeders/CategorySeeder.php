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
        $parents = [
            'Santehnika'            => ['icon' => 'WrenchScrewdriverIcon', 'children' => [
                'Cauruļu remonts',
                'Apkures sistēmas',
                'Kanalizācija',
            ]],
            'Elektrika'             => ['icon' => 'BoltIcon', 'children' => [
                'Elektroinstalācija',
                'Apgaismojums',
                'Drošības sistēmas',
            ]],
            'Būvniecība un Remonts' => ['icon' => 'HomeModernIcon', 'children' => [
                'Iekštelpu remonts',
                'Fasādes darbi',
                'Jumta darbi',
                'Flīzēšana',
            ]],
            'Uzkopšana'             => ['icon' => 'SparklesIcon', 'children' => [
                'Dzīvokļu uzkopšana',
                'Biroju uzkopšana',
                'Logu tīrīšana',
            ]],
            'IT un Datori'          => ['icon' => 'ComputerDesktopIcon', 'children' => [
                'Datoru remonts',
                'Tīmekļa izstrāde',
                'Tīklu uzstādīšana',
            ]],
            'Auto Remonts'          => ['icon' => 'TruckIcon', 'children' => [
                'Dzinēja remonts',
                'Virsbūves remonts',
                'Riepu serviss',
            ]],
        ];

        foreach ($parents as $name => $config) {
            $parent = Category::updateOrCreate(
                [Category::SLUG => Str::slug($name)],
                [
                    Category::NAME      => $name,
                    Category::ICON      => $config['icon'],
                    Category::PARENT_ID => null,
                ]
            );

            foreach ($config['children'] as $childName) {
                Category::updateOrCreate(
                    [Category::SLUG => Str::slug($childName)],
                    [
                        Category::NAME      => $childName,
                        Category::ICON      => null,
                        Category::PARENT_ID => $parent->getId(),
                    ]
                );
            }
        }
    }
}