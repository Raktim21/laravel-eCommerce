<?php

namespace Database\Seeders;

use App\Models\Districts;
use App\Models\Union;
use App\Models\Upazila;
use GuzzleHttp\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EcourierLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $client = new Client();

        $district = new Districts();

        $data = $district->clone()->orderBy('id')->get();

        DB::beginTransaction();

        try {
            Union::query()->delete();
            Upazila::query()->delete();

            foreach ($data as $item) {

                $response = $client->post('https://staging.ecourier.com.bd/api/thana-list', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'API-KEY'      => '34PK',
                        'API-SECRET'   => 'PGE5w',
                        'USER-ID'      => 'U6013'
                    ],
                    'json' => [
                        'city'         => $item['name']
                    ]
                ]);

                if ($response->getStatusCode() == 200)
                {
                    $values = json_decode($response->getBody(), true);

                    foreach ($values['message'] as $value)
                    {
                        $item->subDistricts()->create([
                            'name' => $value['name']
                        ]);
                    }
                }
            }
            DB::commit();
            dd('complete - ' . count($data) . ' districts');
        } catch (QueryException $ex)
        {
            DB::rollback();
            dd($ex->getMessage());
        }
    }
}
