<?php

namespace App\Http\Controllers\Ecommerce;

use App\Models\MerchantPaymentMethods;
use Illuminate\Http\Request;
use App\Http\Services\AssetService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

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
        $validate = Validator::make($request->all(), [
            'country_id' => 'required|exists:location_countries,id'
        ]);

        if($validate->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 400);
        }

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
        $validate = Validator::make($request->all(), [
            'division_id' => 'required|exists:location_divisions,id'
        ]);

        if($validate->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 400);
        }

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
        $validate = Validator::make($request->all(), [
            'district_id' => 'required|exists:location_districts,id'
        ]);

        if($validate->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 400);
        }
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
        $validate = Validator::make($request->all(), [
            'sub_district_id' => 'required|exists:location_upazilas,id'
        ]);

        if($validate->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 400);
        }
        $data = Cache::rememberForever('unionList'.$request->sub_district_id, function () use ($request) {
            return $request->sub_district_id ? $this->service->getUnions($request->sub_district_id) : null;
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], count($data)==0 ? 204 : 200);
    }

    public function bankList()
    {
        $data = $this->service->getBanks();

        return response()->json([
            'status' => true,
            'data'   => $data
        ], $data->isEmpty() ? 204 : 200);
    }

    public function bankBranches($id)
    {
        $data = Cache::rememberForever('bankBranchList'.$id, function () use ($id) {
            return $this->service->getBankBranches($id);
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data) == 0 ? 204 : 200);
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

    public function paymentMethodList()
    {
        $data = Cache::rememberForever('merchantPaymentMethods', function () {
            return MerchantPaymentMethods::get();
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }
}
