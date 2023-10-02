<?php

namespace Database\Seeders;

use App\Models\ThemeCustomizer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThemeCustomizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $theme = new ThemeCustomizer();
        $theme->name = 'Navbar';
        $theme->value = 1;
        $theme->is_active = true;
        $theme->is_inactivable = false;
        $theme->is_static_position = true;
        $theme->ordering = 1;
        $theme->items = 6;
        $theme->save();


        $theme = new ThemeCustomizer();
        $theme->name = 'Banner';
        $theme->value = 1;
        $theme->is_active = true;
        $theme->is_inactivable = false;
        $theme->is_static_position = true;
        $theme->ordering = 2;
        $theme->items = 7;
        $theme->save();


        $theme = new ThemeCustomizer();
        $theme->name = 'Category';
        $theme->value = 1;
        $theme->is_active = true;
        $theme->is_inactivable = true;
        $theme->is_static_position = false;
        $theme->ordering = 3;
        $theme->items = 10;
        $theme->save();


        $theme = new ThemeCustomizer();
        $theme->name = 'Feature products';
        $theme->value = 1;
        $theme->is_active = true;
        $theme->is_inactivable = true;
        $theme->is_static_position = false;
        $theme->ordering = 4;
        $theme->items = 10;
        $theme->save();


        $theme = new ThemeCustomizer();
        $theme->name = 'Flash sale';
        $theme->value = 1;
        $theme->is_active = true;
        $theme->is_inactivable = true;
        $theme->is_static_position = false;
        $theme->ordering = 5;
        $theme->items = 10;
        $theme->save();


        $theme = new ThemeCustomizer();
        $theme->name = 'Feature banner';
        $theme->value = 1;
        $theme->is_active = true;
        $theme->is_inactivable = true;
        $theme->is_static_position = false;
        $theme->ordering = 7;
        $theme->items = 2;
        $theme->save();


        $theme = new ThemeCustomizer();
        $theme->name = 'New arrival';
        $theme->value = 1;
        $theme->is_active = true;
        $theme->is_inactivable = true;
        $theme->is_static_position = false;
        $theme->ordering = 6;
        $theme->items = 10;
        $theme->save();


        $theme = new ThemeCustomizer();
        $theme->name = 'Discount products';
        $theme->value = 1;
        $theme->is_active = true;
        $theme->is_inactivable = true;
        $theme->is_static_position = false;
        $theme->ordering = 9;
        $theme->items = 10;
        $theme->save();


        $theme = new ThemeCustomizer();
        $theme->name = 'Sponsors';
        $theme->value = 1;
        $theme->is_active = true;
        $theme->is_inactivable = true;
        $theme->is_static_position = false;
        $theme->ordering = 10;
        $theme->items = 10;
        $theme->save();



        $theme = new ThemeCustomizer();
        $theme->name = 'Popular products';
        $theme->value = 1;
        $theme->is_active = true;
        $theme->is_inactivable = false;
        $theme->is_static_position = false;
        $theme->ordering = 10;
        $theme->items = 10;
        $theme->save();



        $theme = new ThemeCustomizer();
        $theme->name = 'Subscription';
        $theme->value = 1;
        $theme->is_active = true;
        $theme->is_inactivable = true;
        $theme->is_static_position = false;
        $theme->ordering = 11;
        $theme->items = 5;
        $theme->save();


        $theme = new ThemeCustomizer();
        $theme->name = 'Footer';
        $theme->value = 1;
        $theme->is_active = true;
        $theme->is_inactivable = false;
        $theme->is_static_position = true;
        $theme->ordering = 12;
        $theme->items = 6;
        $theme->save();

    }
}
