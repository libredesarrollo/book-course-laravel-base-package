<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = [
            ['title' => 'Napa Valley Wineries', 'content' => 'Napa Valley is famous for its world-class wineries and premium wine production. The region offers tasting rooms, vineyard tours, and stunning views.'],
            ['title' => 'Laravel Tutorial', 'content' => 'Laravel is a PHP framework known for its elegant syntax and developer-friendly features. It provides routing, authentication, and caching out of the box.'],
            ['title' => 'React.js Guide', 'content' => 'React is a JavaScript library for building user interfaces. It uses a component-based architecture and virtual DOM for efficient rendering.'],
            ['title' => 'Wine Tasting Tips', 'content' => 'When tasting wine, look at the color, smell the aroma, and savor the flavor. Start with white wines and move to reds for the best experience.'],
            ['title' => 'PHP Best Practices', 'content' => 'PHP 8 introduced many new features like named arguments, attributes, and match expressions. Follow PSR standards for clean code.'],
        ];

        foreach ($documents as $doc) {
            DB::table('documents')->insert([
                'title' => $doc['title'],
                'content' => $doc['content'],
                'embedding' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
