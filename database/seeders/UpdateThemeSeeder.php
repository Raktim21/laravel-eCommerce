<?php

namespace Database\Seeders;

use App\Models\ThemeCustomizer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ThemeCustomizer::find(1)->update([
            'name' => 'Navbar',
            'is_inactivable' => 0,
            'items' => 6
        ]);

        ThemeCustomizer::find(2)->update([
            'name' => 'Banner',
            'is_inactivable' => 0,
            'items' => 7
        ]);

        ThemeCustomizer::find(3)->update([
            'name' => 'Category',
            'is_inactivable' => 1,
            'items' => 10
        ]);

        ThemeCustomizer::find(4)->update([
            'name' => 'Featured Products',
            'is_inactivable' => 1,
            'items' => 10
        ]);

        ThemeCustomizer::find(5)->update([
            'name' => 'Flash Sale',
            'is_inactivable' => 1,
            'items' => 10
        ]);

        ThemeCustomizer::find(6)->update([
            'name' => 'Featured Banner',
            'is_inactivable' => 1,
            'items' => 2
        ]);

        ThemeCustomizer::find(7)->update([
            'name' => 'New Arrival',
            'is_inactivable' => 1,
            'items' => 10
        ]);

        ThemeCustomizer::find(8)->update([
            'name' => 'Discount Products',
            'is_inactivable' => 1,
            'items' => 10
        ]);

        ThemeCustomizer::find(9)->update([
            'name' => 'Sponsors',
            'is_inactivable' => 1,
            'items' => 10
        ]);

        ThemeCustomizer::find(10)->update([
            'name' => 'Popular Products',
            'is_inactivable' => 1,
            'items' => 10
        ]);

        ThemeCustomizer::find(11)->update([
            'name' => 'Subscription',
            'is_inactivable' => 1,
            'items' => 5
        ]);

        ThemeCustomizer::find(12)->update([
            'name' => 'Footer',
            'is_inactivable' => 0,
            'items' => 6
        ]);
    }
}
