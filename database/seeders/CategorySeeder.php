<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create categories
        $categories = [
            ['name' => 'Tech', 'description' => 'tech news and tutorials', 'color' => '#3b82f6'],
            ['name' => 'Businsess', 'description' => 'business insights and strategies', 'color' => '#10b981'],
            ['name' => 'Lifestyle', 'description' => 'lifestyle tips and stories', 'color' => '#f59e0b'],
            ['name' => 'Travel', 'description' => 'travel guides and experiences', 'color' => '#8b5cf6'],
            ['name' => 'Food', 'description' => 'recipes and food reviews', 'color' => '#ef4444'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // create tags
        $tags = [
            'Laravel',
            'PHP',
            'MySQL',
            'Golang',
            'Python',
            'Enterpreneurship',
            'Adventures',
            'Nature',
            'Javascript',
            'React JS',
            'Western Food',
        ];

        foreach ($tags as $tag) {
            Tag::create(['name' => $tag]);
        }

    }
}
