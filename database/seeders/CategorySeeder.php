<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Elektronik',
                'icon' => 'category/Elektronik.webp',
                'childs' => ['Microwave', 'TV']
            ],
            [
                'name' => 'Fashion Pria',
                'icon' => 'category/Fashion-pria.webp',
                'childs' => ['Kemeja', 'Jas']
            ],
            [
                'name' => 'Fashion Wanita',
                'icon' => 'category/Fashion-wanita.webp',
                'childs' => ['Kemeja', 'Jas']
            ],
            [
                'name' => 'Handphone',
                'icon' => 'category/Handphone.webp',
                'childs' => ['Kemeja', 'Jas']
            ],
            [
                'name' => 'Komputer & Laptop',
                'icon' => 'category/Komputer-Laptop.webp',
                'childs' => ['Keyboard', 'Laptop']
            ],
            [
                'name' => 'Makanan & Minuman',
                'icon' => 'category/Makanan-Minuman.webp',
                'childs' => ['Makanan', 'Minuman']
            ]
        ];

        foreach ($categories as $categoryPayLoad) {
            $category = Category::create([
                'slug' => Str::slug($categoryPayLoad['name']),
                'name' => $categoryPayLoad['name'],
                'icon' => $categoryPayLoad['icon'],
            ]);

            foreach ($categoryPayLoad['childs'] as $child) {
                $category->childs()->create([
                    'slug' => Str::slug($child),
                    'name' => $child,
                ]);
            }
        }
    }
}
