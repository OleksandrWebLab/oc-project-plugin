<?php namespace Bronx\Catalog\Updates;

use Bronx\Project\Models\Category;
use Bronx\Project\Models\Project;
use October\Rain\Database\Updates\Seeder;
use Faker\Factory as FakerFactory;

class Plugin_Seeder extends Seeder
{
    public function run()
    {
        $faker = FakerFactory::create('ru_RU');

        for ($i = 0; $i < 5; $i++) {
            Category::create([
                'name'             => $faker->sentence(2, true),
                'name_h1'          => $faker->sentence(4, true),
                'meta_title'       => $faker->sentence(2, true),
                'meta_description' => $faker->sentence(60, true),
            ]);
        }

        for ($i = 0; $i < 10; $i++) {
            Project::create([
                'category_id'       => $faker->numberBetween(1, 5),
                'name'              => $faker->sentence(2, true),
                'name_h1'           => $faker->sentence(4, true),
                'short_description' => $faker->sentence(30, true),
                'description'       => $faker->sentence(100, true),
                'meta_title'        => $faker->sentence(2, true),
                'meta_description'  => $faker->sentence(60, true),
                'latitude'          => $faker->latitude(48.413884, 48.491980),
                'longitude'         => $faker->longitude(34.957194, 35.054265),
            ]);
        }
    }
}