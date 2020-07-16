<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [];
        $faker = Faker\Factory::create();
        $image_category = ['abstract','animals','business','cats','city','food',
        'nature','technics','transport'];
        for($i=0;$i<8;$i++){
            $name = $faker->unique()->word();
            $name = str_replace('.','',$name);
            $slug = str_replace(' ','-',strtolower($name));
            $category = $image_category[mt_rand(0,8)];
            $image_path = 'c:/xampp/htdocs/larashop-api/public/images/category';
            $image_pathfull = $faker->image($image_path,500,300,$category,true,true,$category);
            $image = str_replace($image_path.'/','',$image_pathfull);

            $categories[$i] = [
                'name' => $name,
                'slug' => $slug,
                'image' => $image,
                'status' => 'PUBLISH',
                'created_at' => Carbon\Carbon::now(),
            ];
        }
        DB::table('categories')->insert($categories);
    }
}
