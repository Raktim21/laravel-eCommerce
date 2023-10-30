<?php

namespace App\Http\Services;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class GeneralSettingService
{
    protected $setting;

    public function __construct(GeneralSetting $setting)
    {
        $this->setting = $setting->with('language','currency')->find(1);
    }

    public function getSetting()
    {
        return $this->setting;
    }

    public function updateSetting(Request $request)
    {
//        $this->setting->dashboard_language_id = $request->dashboard_language_id ?? $this->setting->dashboard_language_id;
        $this->setting->currency_id = $request->currency_id ?? $this->setting->currency_id;
        $this->setting->name = $request->name ?? $this->setting->name;
        $this->setting->email = $request->email ?? $this->setting->email;
        $this->setting->phone = $request->phone ?? $this->setting->phone;
        $this->setting->address = $request->address ?? $this->setting->address;
        $this->setting->facebook = $request->facebook ?? $this->setting->facebook;
        $this->setting->twitter = $request->twitter ?? $this->setting->twitter;
        $this->setting->linkedin = $request->linkedin ?? $this->setting->linkedin;
        $this->setting->instagram = $request->instagram ?? $this->setting->instagram;
        $this->setting->youtube = $request->youtube ?? $this->setting->youtube;
        $this->setting->google = $request->google ?? $this->setting->google;
        $this->setting->pinterest = $request->pinterest ?? $this->setting->pinterest;
        $this->setting->about = $request->about ?? $this->setting->about;
        $this->setting->save();

        if ($request->hasFile('logo'))
        {
            deleteFile($this->setting->logo);
            saveImage($request->file('logo'), '/uploads/images/general-setting/', $this->setting, 'logo');
        }

        if ($request->hasFile('dark_logo'))
        {
            deleteFile($this->setting->dark_logo);
            saveImage($request->file('dark_logo'), '/uploads/images/general-setting/', $this->setting, 'dark_logo');
        }

        if ($request->hasFile('favicon'))
        {
            deleteFile($this->setting->favicon);
            saveImage($request->file('favicon'), '/uploads/images/general-setting/', $this->setting, 'favicon');
        }

    }
}
