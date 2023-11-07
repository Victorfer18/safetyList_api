<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Myth\Faker\Faker;

class UserSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();

        for ($i = 1; $i <= 10; $i++) {
            $data = [
                'user_name' => $faker->name,
                'user_email' => $faker->email,
                'user_created' => $faker->date('Y-m-d H:i:s'),
                'group_id' => 4,
                'situation_id' => $faker->numberBetween(1, 3),
                'client_id' => $i,
                'user_doc' => $faker->numerify('###########'),
                'user_password' => sha1('123'),
            ];

            $this->db->table('user')->insert($data);
        }
    }
}
