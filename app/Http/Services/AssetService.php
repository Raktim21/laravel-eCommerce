<?php

namespace App\Http\Services;

use App\Models\Country;
use App\Models\Currency;
use App\Models\DashboardLanguage;
use App\Models\Division;
use App\Models\FAQ;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\UserSex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AssetService
{

    public function getLanguages()
    {
        return DashboardLanguage::get();
    }

    public function getCountries()
    {
        return Country::get();
    }

    public function getDivisions($country_id)
    {
        return Country::findOrFail($country_id)->divisions;
    }

    public function getDistricts($division_id)
    {
        return Division::findOrfail((int) $division_id)->districts;
    }

    public function getSubDistricts($district_id)
    {
        return Upazila::where('district_id', (int) $district_id)->get();
    }

    public function getUnions($sub_district_id)
    {
        return Union::where('upazila_id', $sub_district_id)->get();
    }

    public function getCurrencies()
    {
        return Currency::get();
    }

    public function getGenders()
    {
        return UserSex::get();
    }

    public function getFaqs()
    {
        return FAQ::orderBy('ordering')->get();
    }

    public function storeFAQ(Request $request): void
    {
        FAQ::create([
            'question'      => $request->question,
            'answer'        => $request->answer,
            'ordering'      => FAQ::count() + 1
        ]);
        Cache::forget('faqs');
    }

    public function updateFAQ(Request $request, $id): void
    {
        FAQ::findOrFail($id)->update([
            'question'      => $request->question,
            'answer'        => $request->answer
        ]);
        Cache::forget('faqs');
    }

    public function deleteFAQ($id): void
    {
        FAQ::findOrFail($id)->delete();
        Cache::forget('faqs');
    }

    public function orderFAQ(Request $request): void
    {
        foreach ($request->ids as $key => $id) {
            FAQ::find($id)->update(['ordering' => $key + 1]);
        }
        Cache::forget('faqs');
    }
}
