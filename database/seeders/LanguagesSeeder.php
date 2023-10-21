<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'name' => 'English',
                'code' => 'en',
                'dir' => 'ltr',
                'icon' => 'us',
                'status' => 'Active',
            ],
        ];

        foreach ($languages as $language) {
            Language::create($language);
        }
    }
}
