<?php

namespace App\Http\Services;

use App\Models\Bank;
use App\Models\BankBranch;
use App\Models\Country;
use App\Models\Currency;
use App\Models\DashboardLanguage;
use App\Models\Division;
use App\Models\FAQ;
use App\Models\OrderDeliverySystem;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\UserSex;
use Illuminate\Http\Request;

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
    }

    public function updateFAQ(Request $request, $id): void
    {
        FAQ::findOrFail($id)->update([
            'question'      => $request->question,
            'answer'        => $request->answer
        ]);

    }

    public function deleteFAQ($id): void
    {
        FAQ::findOrFail($id)->delete();
    }

    public function orderFAQ(Request $request): void
    {
        foreach ($request->ids as $key => $id) {
            FAQ::find($id)->update(['ordering' => $key + 1]);
        }
    }

    public function activeDeliverySystem()
    {
        return OrderDeliverySystem::where('active_status', 1)->first()->id;
    }

    public function getBanks()
    {
        return Bank::when(!\request()->has('eftn'), function ($q) {
                return $q->whereNotIn('name', ['BRAC BANK LTD.', 'DUTCH-BANGLA BANK LTD'])
                    ->when(\request()->input('name'), function ($q) {
                        return $q->where('name','like','%'.\request()->input('name').'%');
                    });
            })
            ->when(\request()->has('eftn'), function ($q) {
                return $q->whereIn('name', ['BRAC BANK LTD.', 'DUTCH-BANGLA BANK LTD'])
                    ->when(\request()->input('name'), function ($q) {
                        return $q->where('name','like','%'.\request()->input('name').'%');
                    });
            })
            ->paginate(10);
    }

    public function getBankBranches($id)
    {
        return BankBranch::where('bank_id', $id)->paginate(10);
    }
}
