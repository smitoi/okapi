<?php

namespace Database\Seeders;

use App\Models\Okapi\Type;
use Illuminate\Database\Seeder;

class OkapiExampleSeeder extends Seeder
{
    protected const TYPE_FOOD = 'Menu Item';
    protected const TYPE_INGREDIENT = 'Ingredient';


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $restaurant = Type::query()->create([
            'name' => 'Restaurant',
            'slug' => 'restaurant',
            'is_collection' => true,
        ]);



        $menuItem = Type::query()->create([
            'name' => 'Menu Item',
            'slug' => 'menu-item',
            'is_collection' => true,
        ]);

        $ingredient = Type::query()->create([
            'name' => 'Ingredient',
            'slug' => 'ingredient',
            'is_collection' => true,
        ]);

        $mainPage = Type::query()->create([
            'name' => 'Main Page',
            'slug' => 'main-page',
            'is_collection' => false,
        ]);
    }
}
