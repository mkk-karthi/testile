<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $country_list = [
            [
                "name" => "India",
                "code" => "IN",
                "states" => [
                    "Assam",
                    "Andhra Pradesh",
                    "Tamil Nadu",
                    "Kerala",
                    "Karnataka"
                ]
            ],
            [
                "name" => "United States",
                "code" => "US",
                "states" => ["California", "Texas"]
            ]
        ];

        foreach ($country_list as $row) {
            $country = Country::create([
                "country_name" => $row["name"],
                "country_code" => $row["code"]
            ]);

            if ($country) {
                $country_id = $country->id;

                foreach ($row["states"] as $row1) {
                    State::create([
                        "state_name" => $row1,
                        "country_id" => $country_id
                    ]);
                }
            }
        }
    }
}
