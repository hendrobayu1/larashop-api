<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $city_url = "https://api.rajaongkir.com/starter/city?key=791968c2a8f0e2a3d5d3d68dbe40155b";
        $json_str = file_get_contents($city_url);
        $json_obj = json_decode($json_str);
        $cities = [];
        foreach($json_obj->rajaongkir->results as $city){
            $cities[]=[
                'id' => $city->city_id,
                'province_id' => $city->province_id,
                'city' => $city->city_name,
                'type' => $city->type,
                'postal_code' => $city->postal_code,
            ];
        }

        $collection=new Collection();
        foreach($cities as $city){
            $collection->push((object)$city);
        }

        $cities_collect = $collection->where('id','>',300);
        $json_city_obj = json_decode($cities_collect);
        $cities_limit = [];
        // echo $cities_collect;
        foreach($json_city_obj as $city){
            $cities_limit[]=[
                'id' => $city->id,
                'province_id' => $city->province_id,
                'city' => $city->city,
                'type' => $city->type,
                'postal_code' => $city->postal_code,
            ];
        }
        DB::table('cities')->insert($cities_limit);
    }
}
