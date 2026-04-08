<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        $categories = [
            ['name' => 'Trabalho',  'color' => '#3b82f6', 'icon' => '💼'],
            ['name' => 'Pessoal',   'color' => '#22c55e', 'icon' => '🏠'],
            ['name' => 'Estudo',    'color' => '#eab308', 'icon' => '📚'],
            ['name' => 'Saúde',     'color' => '#ef4444', 'icon' => '❤️'],
            ['name' => 'Finanças',  'color' => '#8b5cf6', 'icon' => '💰'],
        ];

        foreach ($categories as $category) {
            $user->categories()->create($category);
        }
    }
}
