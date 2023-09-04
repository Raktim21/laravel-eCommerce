<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Services\AssetService;
use App\Models\Country;
use App\Models\Division;
use App\Models\Union;
use App\Models\Upazila;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StaticAssetController extends Controller
{

    protected $service;

    public function __construct(AssetService $service)
    {
        $this->service = $service;
    }


    public function countryList()
    {
        $data = Cache::rememberForever('countryList', function () {
            return $this->service->getCountries();
        });
        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }



    public function divisionList(Request $request)
    {
        $data = Cache::rememberForever('divisionList'.$request->country_id, function () use ($request) {
            return $request->country_id ? $this->service->getDivisions($request->country_id) : null;
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }



    public function districtList(Request $request)
    {
        $data = Cache::rememberForever('districtList'.$request->division_id, function () use ($request) {
            return $request->division_id ? $this->service->getDistricts($request->division_id) : null;
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }



    public function subDistrictList(Request $request)
    {
        $data = Cache::rememberForever('subDistrictList'.$request->district_id, function () use ($request) {
            return $request->district_id ? $this->service->getSubDistricts($request->district_id) : null;
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }



    public function unionList(Request $request)
    {
        $data = Cache::rememberForever('unionList'.$request->sub_district_id, function () use ($request) {
            return $request->sub_district_id ? $this->service->getUnions($request->sub_district_id) : null;
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }

    public function languageList()
    {
        $data = Cache::rememberForever('languageList', function () {
            return $this->service->getLanguages();
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function currencyList()
    {
        $data = Cache::rememberForever('currencyList', function () {
            return $this->service->getCurrencies();
        });

        return response()->json([
            'status'        => true,
            'data'          => $data
        ]);
    }

    public function genderList()
    {
        $data = Cache::rememberForever('genderList', function () {
            return $this->service->getGenders();
        });

        return response()->json([
            'status'        => true,
            'data'          => $data
        ]);
    }
}
