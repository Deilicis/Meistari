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
        $structure = [
            'Apkure un siltumtehnika' => ['icon' => 'FireIcon', 'children' => [
                'Krāsnis', 'Radiatori', 'Siltumsūkņi',
            ]],
            'Elektroinstalācija' => ['icon' => 'BoltIcon', 'children' => [
                'Iekšējie darbi', 'Ārējie darbi', 'Apgaismojums',
            ]],
            'Santehnika' => ['icon' => 'WrenchScrewdriverIcon', 'children' => [
                'Cauruļvadi', 'Sanitārtehnika', 'Ūdens sildītāji',
            ]],
            'Būvniecība un remonts' => ['icon' => 'HomeModernIcon', 'children' => [
                'Iekšējais remonts', 'Ārējais remonts', 'Jaunbūves',
            ]],
            'Logu tīrīšana' => ['icon' => 'SparklesIcon', 'children' => [
                'Stiklu pakalpojumi', 'Karkasu mazgāšana',
            ]],
            'Krāsošana' => ['icon' => 'PaintBrushIcon', 'children' => [
                'Iekšējā krāsošana', 'Fasāžu krāsošana',
            ]],
            'Dārza darbi' => ['icon' => 'SunIcon', 'children' => [
                'Zāles pļaušana', 'Stādīšana', 'Sniega tīrīšana',
            ]],
            'Tīrīšana' => ['icon' => 'SparklesIcon', 'children' => [
                'Dzīvokļu uzkopšana', 'Biroju tīrīšana', 'Pēcremonta tīrīšana',
            ]],
        ];

        foreach ($structure as $name => $config) {
            $parent = Category::updateOrCreate(
                [Category::SLUG => Str::slug($name)],
                [
                    Category::NAME => $name,
                    Category::ICON => $config['icon'],
                    Category::PARENT_ID => null,
                    Category::IS_SYSTEM => false,
                ]
            );

            foreach ($config['children'] as $childName) {
                Category::updateOrCreate(
                    [Category::SLUG => Str::slug($childName)],
                    [
                        Category::NAME => $childName,
                        Category::ICON => null,
                        Category::PARENT_ID => $parent->getId(),
                        Category::IS_SYSTEM => false,
                    ]
                );
            }
        }
    }
}
