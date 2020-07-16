<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [];
        $faker = Faker\Factory::create();
        for($i=0;$i<10;$i++){
            $avatar_path = 'c:/xampp/htdocs/larashop-api/public/images/users';
            $avatar_path_full = $faker->image($avatar_path,200,250,'people',true,true,'people');
            $avatar = str_replace($avatar_path.'/','',$avatar_path_full);
            $users[$i] = [
                'name'      => $faker->name,
                'email'     => $faker->unique()->safeEmail,
                'password'  => bcrypt('123456'),
                'roles'     => json_encode(['CUSTOMER']),
                'avatar'    => $avatar,
                'status'    => 'ACTIVE',
                'created_at'=> Carbon\Carbon::now(),
            ];
        }
        DB::table('users')->insert($users);
    }
}