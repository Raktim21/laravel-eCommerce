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
        $data = Cache::remember('countryList', PHP_INT_MAX, function () {
            return $this->service->getCountries();
        });
        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }



    public function divisionList(Request $request)
    {
        $data = Cache::remember('divisionList', PHP_INT_MAX, function () use ($request) {
            return $request->country_id ? $this->service->getDivisions($request->country_id) : null;
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }



    public function districtList(Request $request)
    {
        $data = Cache::remember('districtList', PHP_INT_MAX, function () use ($request) {
            return $request->division_id ? $this->service->getDistricts($request->division_id) : null;
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }



    public function subDistrictList(Request $request)
    {
        $data = Cache::remember('subDistrictList', PHP_INT_MAX, function () use ($request) {
            return $request->district_id ? $this->service->getSubDistricts($request->district_id) : null;
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }



    public function unionList(Request $request)
    {
        $data = Cache::remember('unionList', PHP_INT_MAX, function () use ($request) {
            return $request->sub_district_id ? $this->service->getUnions($request->sub_district_id) : null;
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }

    public function languageList()
    {
        $data = Cache::remember('languageList', PHP_INT_MAX, function () {
            return $this->service->getLanguages();
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }

    public function currencyList()
    {
        $data = Cache::remember('currencyList', PHP_INT_MAX, function () {
            return $this->service->getCurrencies();
        });

        return response()->json([
            'status'        => true,
            'data'          => $data
        ], count($data)==0 ? 204 : 200);
    }

    public function genderList()
    {
        $data = Cache::remember('genderList', PHP_INT_MAX, function () {
            return $this->service->getGenders();
        });

        return response()->json([
            'status'        => true,
            'data'          => $data
        ], count($data)==0 ? 204 : 200);
    }
}
